<?php
/*
* LanguageEngine.php - Main component file
*
* This file is part of the Language component.
*-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\Language;

use App\Yantrana\Base\BaseEngine;

use App\Yantrana\Components\Language\Repositories\LanguageRepository;
use App\Yantrana\Components\Article\Repositories\ArticleRepository;
use App\Yantrana\Components\Project\Repositories\ProjectRepository;
use App\Yantrana\Components\Language\Interfaces\LanguageEngineInterface;

class LanguageEngine extends BaseEngine implements LanguageEngineInterface
{
    /**
     * @var  LanguageRepository $languageRepository - Language Repository
     */
    protected $languageRepository;

    /**
     * @var  ArticleRepository $articleRepository - Article Repository
     */
    protected $articleRepository;

    /**
     * @var  ProjectRepository $projectRepository - Project Repository
     */
    protected $projectRepository;

    /**
      * Constructor
      *
      * @param  LanguageRepository $languageRepository - Language Repository
      * @param  ArticleRepository $articleRepository - Article Repository
      * @param  ProjectRepository $projectRepository - Project Repository
      *
      * @return  void
      *-----------------------------------------------------------------------*/

    function __construct(LanguageRepository $languageRepository, ArticleRepository $articleRepository, ProjectRepository $projectRepository)
    {
        $this->languageRepository = $languageRepository;
        $this->articleRepository = $articleRepository;
        $this->projectRepository 	= $projectRepository;
    }

  /**
  	* Language datatable source
  	*
  	* @return  array
  	*---------------------------------------------------------------- */

	public function prepareLanguageDataTableSource()
	{
    	$languageCollection = $this->languageRepository->fetchLanguageDataTableSource();
    	$requireColumns = [
        	'_id',
            'is_rtl' => function($key) {
            	if($key['is_rtl'] == 1) {
            		return 'Yes';
            	} else {
            		return 'No';
            	}
            },
            'name',
            'status',
			'canEditLanguage' => function() {
				return canAccess('manage.language.write.update');
			},
			'canDeleteLanguage' => function() {
				return canAccess('manage.language.write.delete');
			},
        ];

    	return $this->dataTableResponse($languageCollection, $requireColumns);
	}

	/**
	  * Language delete process
	  *
	  * @param  mix $languageIdOrUid
	  *
	  * @return  array
	  *---------------------------------------------------------------- */

	public function processLanguageDelete($languageIdOrUid)
	{
	    $language = $this->languageRepository->fetch($languageIdOrUid);

	    if (__isEmpty($language)) {
	         return $this->engineReaction(18, null, __tr('Language not found.'));
	    }

	    $articlesContents = $this->articleRepository->fetchArticleContentsbyLanguage($language->_id);

	    $projects = $this->projectRepository->fetchProjectsbyLanguage($language->_id);

	    if (!__isEmpty($projects) || !__isEmpty($articlesContents)) {
	    	return $this->engineReaction(2, null, __tr('Language cannot be deleted, as Projects exists with this language.'));
	    }

	    if ($this->languageRepository->deleteLanguage($language)) {

	        return $this->engineReaction(1, null, __tr('Language deleted.'));
	    }

	    return $this->engineReaction(2, null, __tr('Language not deleted.'));
	}

    /**
      * Language Add Support Data
      *
      * @return  array
      *---------------------------------------------------------------- */

    public function prepareLanguageSupportData()
    {
        return $this->engineReaction(1, []);
    }
	/**
	  * Language create
	  *
	  * @param  array $inputData
	  *
	  * @return  array
	  *---------------------------------------------------------------- */

	public function processLanguageCreate($inputData)
	{
		$storeData = [
			'name'		=> $inputData['name'],
            '_id'		=> $inputData['code'],
            'status'	=> $inputData['status'],
            'is_rtl'    => $inputData['is_rtl']
		];

	    if ($language = $this->languageRepository->storeLanguage($storeData)) {
	        return $this->engineReaction(1, [
                'addLanguageData' => [
                    'id' => $language->_id,
                    'name' => $language->name
                ]
            ], __tr('Language added.'));
	    }

	    return $this->engineReaction(2, null, __tr('Language not added.'));
	}

    /**
      * Language prepare update data
      *
      * @param  mix $languageIdOrUid
      *
      * @return  array
      *---------------------------------------------------------------- */

	public function prepareLanguageUpdateData($languageIdOrUid)
	{
    	$language = $this->languageRepository->fetch($languageIdOrUid);

        // Check if $language not exist then throw not found
        // exception
    	if (__isEmpty($language)) {
         	return $this->engineReaction(18, null, __tr('Language not found.'));
    	}

    	$editData = [
			'name'			=> $language->name,
			'status'		=> $language->status,
			'is_rtl'		=> $language->is_rtl,
    	];

    	return $this->engineReaction(1, [
    		'editData' => $editData
    	]);
	}

    /**
	  * Language process update
	  *
	  * @param  mix $languageIdOrUid
	  * @param  array $inputData
	  *
	  * @return  array
	  *---------------------------------------------------------------- */

	public function processLanguageUpdate($languageIdOrUid, $inputData)
	{
    	$language = $this->languageRepository->fetch($languageIdOrUid);

        // Check if $language not exist then throw not found
        // exception
    	if (__isEmpty($language)) {
         	return $this->engineReaction(18, null, __tr('Language not found.'));
    	}

        $updateData = [
            'name' => $inputData['name'],
            'status' => $inputData['status'],
            'is_rtl' => $inputData['is_rtl']
        ];

        // Check if Language updated
    	if ($this->languageRepository->updateLanguage($language,  $updateData)) {
        	return $this->engineReaction(1, null, __tr('Language updated.'));
    	}

    	return $this->engineReaction(14, null, __tr('Language not updated.'));
	}
}