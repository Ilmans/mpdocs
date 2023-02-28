<?php
/*
* ProjectEngine.php - Main component file
*
* This file is part of the Project component.
*-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\Project;

use App\Yantrana\Base\BaseEngine;

use App\Yantrana\Components\Project\Repositories\ProjectRepository;
use App\Yantrana\Components\Language\Repositories\LanguageRepository;
use App\Yantrana\Components\Article\Repositories\ArticleRepository;
use App\Yantrana\Components\Project\Interfaces\ProjectEngineInterface;
use App\Yantrana\Components\Media\MediaEngine;
use File;

class ProjectEngine extends BaseEngine implements ProjectEngineInterface
{
    /**
     * @var  ProjectRepository $projectRepository - Project Repository
     */
    protected $projectRepository;

    /**
     * @var  LanguageRepository $languageRepository - Language Repository
     */
    protected $languageRepository;

    /**
     * @var  ArticleRepository $articleRepository - Article Repository
     */
    protected $articleRepository;

    /**
     * @var MediaEngine - Media Engine
     */
    protected $mediaEngine;

    /**
      * Constructor
      *
      * @param  ProjectRepository $projectRepository - Project Repository
      * @param  LanguageRepository $languageRepository - Language Repository
      * @param  ArticleRepository $articleRepository - Language Repository
      *
      * @return  void
      *-----------------------------------------------------------------------*/

    function __construct(
        ProjectRepository $projectRepository, 
        LanguageRepository $languageRepository,
        ArticleRepository $articleRepository,
    	MediaEngine $mediaEngine)
    {
        $this->projectRepository 	= $projectRepository;
        $this->languageRepository 	= $languageRepository;
        $this->articleRepository 	= $articleRepository;
        $this->mediaEngine			= $mediaEngine;
    }

  /**
  	* Project datatable source
  	*
  	* @return  array
  	*---------------------------------------------------------------- */

	public function prepareProjectDataTableSource()
	{
    	$projectCollection = $this->projectRepository->fetchProjectDataTableSource();
    	$projectConfig = configItem('project');

    	$requireColumns = [
        	'_id',
            '_uid',
            'name',
            'short_description',
            'status',
            'created_at',
            'slug',
            'updated_at' => function($key) {
                return formatDateTime($key['updated_at']);
            },
            'formatted_status' => function($key) {
            	return techItemString($key['status']);
            },
            'f_created_at' => function($key) {
            	return formatDateTime($key['created_at']);
            },
            'type',
            'formatted_type' => function($key) use ($projectConfig) {
            	return configItem('project.type.'.$key['type']);
            },
            'canViewProject' => function(){
                return canAccess('manage.project.read.list');
            },
			'canEditProject' => function(){
				return canAccess('manage.project.write.update');
			},
			'canDeleteProject' => function(){
				return canAccess('manage.project.write.delete');
			},
			'canViewArticleTag' => function(){
				return canAccess('manage.article.read.list');
			},
			'canManageProjectVersions' => function(){
				return canAccess('manage.project.version.read.list');
			},
            'externalDetailUrl' => function($key) {
                return route('doc.view', [
                            'projectSlug' => $key['slug']
                        ]);
            }
        ];

    	return $this->dataTableResponse($projectCollection, $requireColumns);
	}

	/**
	  * Project delete process
	  *
	  * @param  mix $projectIdOrUid
	  *
	  * @return  array
	  *---------------------------------------------------------------- */

	public function processProjectDelete($projectIdOrUid)
	{
	    $project = $this->projectRepository->fetch($projectIdOrUid);

	    if (__isEmpty($project)) {
	        return $this->engineReaction(18, null, __tr('Project not found.'));
	    }

	    if ($this->projectRepository->deleteProject($project)) {
            
            activityLog(13, $project->_id, 3);

	        return $this->engineReaction(1, null, __tr('Project deleted.'));
	    }

	    return $this->engineReaction(2, null, __tr('Project not deleted.'));
	}

    /**
      * Project Add Support Data
      *
      * @return  array
      *---------------------------------------------------------------- */

    public function prepareProjectSupportData()
    {
    	$allLanguages = $this->languageRepository->fetchAllLanguages();
    	$latestProjects = $this->projectRepository->latestProjectsByUser(getUserAuthorityId());

    	$jsondata = [];
    	if (!__isEmpty($latestProjects)) {
         	$jsondata = $latestProjects->__data;
    	}
    	

    	$languages = [];
    	$recentLanguages = [];
    	//check if not empty
    	if (!__isEmpty($allLanguages)) {
    		foreach ($allLanguages as $key => $language) {

    			$languages[] = [
    				'id'	=> $language->_id,
    				'name'	=> $language->name
    			];

    			if (isset($jsondata['project_languages']) && in_array($language->_id, $jsondata['project_languages'])) {
    				$recentLanguages[] = [
	    				'id'	=> $language->_id,
	    				'name'	=> $language->name
	    			];
    			}
    		}
    	}

        return $this->engineReaction(1, [
        	'project_type' => configItem('project.type'),
        	'languages' => $languages,
        	'recent_languages' => $recentLanguages
        ]);
    }
	/**
	  * Project create
	  *
	  * @param  array $inputData
	  *
	  * @return  array
	  *---------------------------------------------------------------- */

	public function processProjectCreate($inputData)
	{
        $inputData['__data']	= [
            'project_languages' => $inputData['project_languages']
        ];

        $inputData['slug'] = $inputData['slug'];

        $logoImage = null;
        if (isset($inputData['logo_image']) && !__isEmpty($inputData['logo_image'])) {
	        $sourcePath = mediaStorage('user_temp_uploads', ['{_uid}' => authUID() ]).'/'.$inputData['logo_image'];
			$imageInfo = pathinfo($sourcePath);
			$extension = $imageInfo['extension'];
			$logoImage = $inputData['logo_image'];
			$inputData['logo_image'] = configItem('project.logoName').'.'.$extension;
        }
 
        if ($project = $this->projectRepository->storeProject($inputData)) {

	    	activityLog(13, $project->_id, 9);

	    	if (isset($logoImage) && !__isEmpty($logoImage)) {
	    		$this->mediaEngine->storeProjectLogo($logoImage, $project->_uid);
	    	}

	    	if (isset($inputData['favicon_image']) && !__isEmpty($inputData['favicon_image'])) {
	    		$this->mediaEngine->storeProjectFavicon($inputData['favicon_image'], $project->_uid);
	    	}

	        return $this->engineReaction(1, [ 
	        	'projectIdOrUid' => $project->_uid
	        ], __tr('Project added.'));
	    }

	    return $this->engineReaction(2, null, __tr('Project not added.'));
	}

    /**
      * Project prepare update data
      *
      * @param  mix $projectIdOrUid
      *
      * @return  array
      *---------------------------------------------------------------- */

	public function prepareProjectUpdateData($projectIdOrUid)
	{
    	$project = $this->projectRepository->fetch($projectIdOrUid);

        // Check if $project not exist then throw not found
        // exception
    	if (__isEmpty($project)) {
         	return $this->engineReaction(18, null, __tr('Project not found.'));
    	}

    	$allLanguages = $this->languageRepository->fetchAllLanguages();

    	$languages = [];
    	//check if not empty
    	if (!__isEmpty($allLanguages)) {
    		foreach ($allLanguages as $key => $language) {
                if ($language->_id != $project->languages__id) {
        			$languages[] = [
        				'lang_id'	=> $language->_id,
        				'name'	=> $language->name
        			];
                } else if ($language->_id == $project->languages__id) {
                    $primaryLanguageText = $language->name;
                }
    		}
    	}

    	$jsondata = $project->__data;
        $projectLanguages = [];
        $projectLangJson = isset($jsondata['project_languages']) ? $jsondata['project_languages'] : [];
        if (!__isEmpty($projectLangJson)) {
            foreach ($projectLangJson as $projectLang) {
                if ($project->languages__id != $projectLang) {
                    $projectLanguages[] = $projectLang;
                }
            }
        }
        
    	$editData = [
			'status'				=> $project->status,
			'name'					=> $project->name,
			'slug'					=> $project->slug,
			'short_description'		=> $project->short_description,
			'primary_language'		=> $project->languages__id,
            'primary_language_text' => $primaryLanguageText,
            'type'					=> $project->type,
            'project_languages'     => $projectLanguages
    	];

        $logoImageExists = false;
    	$editData['logo_image_url'] = '';
    	if (!__isEmpty($project->logo_image)) {
    		$logoUrl = mediaStorage('project_uploads', ['{_uid}'    => $project->_uid], true);
            $logoPath = mediaStorage('project_uploads', ['{_uid}'    => $project->_uid]).'/'.$project->logo_image;
            if (File::exists($logoPath)) {
    		  $editData['logo_image_url'] = ($logoUrl.'/'.$project->logo_image);
              $logoImageExists = true;
            } else {
                $editData['logo_image_url'] = '';
            }
    	}

        $faviconImageExist = false;
		$editData['favicon_image_url'] = '';
    	if (!__isEmpty($project->favicon_image)) {
    		$faviconUrl = mediaStorage('project_uploads', ['{_uid}'    => $project->_uid], true);
            $faviconPath = mediaStorage('project_uploads', ['{_uid}'    => $project->_uid]).'/'.$project->favicon_image;
            if (File::exists($faviconPath)) {
                $editData['favicon_image_url'] = ($faviconUrl.'/'.$project->favicon_image);
                $faviconImageExist = true;
            } else {
                $editData['favicon_image_url'] = '';
            }
    		
    	}

    	return $this->engineReaction(1, [
    		'edit_data'           => $editData,
            'logoImageExists'     => $logoImageExists,
            'faviconImageExist'   => $faviconImageExist,
    		'languages'           => $languages,
            'projectLanguages'    => $projectLanguages
    	]);
	}

    /**
	  * Project process update
	  *
	  * @param  mix $projectIdOrUid
	  * @param  array $inputData
	  *
	  * @return  array
	  *---------------------------------------------------------------- */

	public function processProjectUpdate($projectIdOrUid, $inputData)
	{
        $transactionResponse = $this->articleRepository->processTransaction(function () use ($projectIdOrUid, $inputData) {
            $project = $this->projectRepository->fetch($projectIdOrUid);

            // Check if $project not exist then throw not found
            // exception
            if (__isEmpty($project)) {
                return $this->projectRepository->transactionResponse(18, null, __tr('Project not found.'));
            }
            $inputData['project_languages'][] = $project->languages__id;
            $updateData = [
                'name' => $inputData['name'],
                'slug' => $inputData['slug'],
                'short_description' => isset($inputData['short_description']) ? $inputData['short_description'] : '',
                'status' => $inputData['status'],
                'type' => $inputData['type'],
                'favicon_image' => configItem('faviconName'),
                'user_authorities__id' => getUserAuthorityId(),
                'user_authorities__id' => getUserAuthorityId(),
                    '__data' => [
                        'project_languages' => $inputData['project_languages']
                    ]
            ];

            $logoImage = null;
            if (isset($inputData['logo_image']) && !__isEmpty($inputData['logo_image'])) {
                $sourcePath = mediaStorage('user_temp_uploads', ['{_uid}' => authUID() ]).'/'.$inputData['logo_image'];
                $imageInfo = pathinfo($sourcePath);
                $extension = $imageInfo['extension'];

                $updateData['logo_image'] = configItem('project.logoName').'.'.$extension;
            }

            $projectUpdated = false;
            $logoUpdated = false;
            $faviconUpdated = false;
            //get new language entry data
            $newLanguage = array_diff($inputData['project_languages'], $project['__data']['project_languages']);

            // Check if Project updated
            if ($projectUpdated =  $this->projectRepository->updateProject($project, $updateData)) {
                //fetch project articles
                $projectarticles = $this->articleRepository->fetchProjectArticls($project->_id);

                //fetch project language data
                $languageData = $this->languageRepository->fetchLanguagebyIds($newLanguage);

                $storeContentData = [];
                foreach ($languageData as $key => $language) {
                    // Store Article content with history
                    foreach ($projectarticles as $key => $article) {
                        $storeContentData[] = [
                            'title' => 'No title here...',
                            'description' => '<p>No content here...</p>',
                            'languages__id' => $language['_id'],
                            'status' => 2,
                            'articles__id' => $article->_id
                        ];
                    }
                }

                //store  project article multiple contents 
                if (!__isEmpty($storeContentData) and 
                    !$this->articleRepository->storeMultiplArticleContent($storeContentData)) {
                    return $this->projectRepository->transactionResponse(18, null, __tr('Project articles contents not updated.'));
                }

                activityLog(13, $project->_id, 2);
            }

            if (isset($inputData['logo_image']) && !__isEmpty($inputData['logo_image'])) {
                $logoUpdated = $this->mediaEngine->storeProjectLogo($inputData['logo_image'], $project->_uid);
            }

            if (isset($inputData['favicon_image']) && !__isEmpty($inputData['favicon_image'])) {
                $faviconUpdated = $this->mediaEngine->storeProjectFavicon($inputData['favicon_image'], $project->_uid);
            }

            if ($projectUpdated || $logoUpdated || $faviconUpdated) {
                return $this->projectRepository->transactionResponse(1, null, __tr('Project updated.'));
            }

            return $this->projectRepository->transactionResponse(14, null, __tr('Project not updated.'));
        });

        return $this->engineReaction($transactionResponse);
    }

    /**
    * Process Project Language Delete
    *
    * @param  mix $projectId
    *
    * @return  array
    *---------------------------------------------------------------- */

    public function processProjectLanguageDelete($projectId, $languageId)
    {
        $project = $this->projectRepository->fetch($projectId);

        // Check if $project not exist then throw not found
        // exception
        if (__isEmpty($project)) {
            return $this->engineReaction(18, null, __tr('Project does not exist.'));
        }

        $language = $this->languageRepository->fetch($languageId);

        // Check if language not exist then throw not found
        if (__isEmpty($language)) {
            return $this->engineReaction(18, null, __tr('Language does not exist.'));
        }

        $articleContent = $this->articleRepository->fetchArticleContentByProjectAndLangId($project->_id, $languageId);

        $articleContentIds = $articleContent->pluck('article_content_id')->toArray();

        if (!__isEmpty($articleContentIds)) {
            $this->articleRepository->deleteArticlesContentByIds($articleContentIds);
        }

        $projectLanguages = array_get($project['__data'], 'project_languages');
        $newProjectLanguages = [];
        foreach ($projectLanguages as $key => $projectLanguage) {
            if ($projectLanguage != $languageId) {
                $newProjectLanguages[] = $projectLanguage;
            }            
        }
        
        $updateData = [
            '__data' => [
                'project_languages' => $newProjectLanguages
            ]
        ];
         
        // Check if project language deleted
        if ($this->projectRepository->updateProject($project, $updateData)) {
            return $this->engineReaction(1, null, __tr('Project language deleted successfully.'));
        }

        return $this->engineReaction(2, null, __tr('Project language not deleted.'));
    }
    

     /**
	* Article List for public side
	*
	* @param  mix $articleUid
	*
	* @return  array
	*---------------------------------------------------------------- */

	public function preparePublicProjectDetails($projectUid)
	{   
        $project = $this->projectRepository->fetch($projectUid, 1);

        if (__isEmpty($project)) {
            return $this->engineReaction(18, null, __tr('Project not found.'));
        }

        $data = [
            '_id'   => $project->_id,
            '_uid'  => $project->_uid,
            'name'  => $project->name,
            'short_description'  => $project->short_description
        ];

        $projectArticles = $this->articleRepository
                                ->allPrimaryArticles(1, $project->_id)->toArray();


        return $this->engineReaction(1, [
            'project'  => $data,
            'articles' => $projectArticles
        ]);
	}

	/**
      * Project prepare update data
      *
      * @param  mix $projectIdOrUid
      *
      * @return  array
      *---------------------------------------------------------------- */

	public function prepareProjectDetails($projectIdOrUid)
	{
    	$project = $this->projectRepository->fetch($projectIdOrUid);

        // Check if $project not exist then throw not found
        // exception
    	if (__isEmpty($project)) {
         	return $this->engineReaction(18, null, __tr('Project not found.'));
    	}

    	$allLanguages = $this->languageRepository->fetchAllLanguages();

    	$languages = [];
    	
    	$jsondata = $project->__data;
    	
    	//check if not empty
    	if (!__isEmpty($allLanguages) && isset($jsondata['project_languages'])) {

    		foreach ($allLanguages as $key => $language) {
    			if (in_array($language->_id, $jsondata['project_languages'])) {
	    			$languages[] = [
	    				'lang_id'	=> $language->_id,
	    				'name'	=> $language->name
	    			];
    			}
    		}
    	}

    	$projectData = [
			'status'				=> techItemString($project->status),
			'name'					=> $project->name,
			'added_on'				=> formatDateTime($project->created_at),
			'short_description'		=> $project->short_description,
            'type'					=> configItem('project.type', $project->type),
            'languages' => $languages,
    	];

    	return $this->engineReaction(1, [
    		'projectData' => $projectData
    	]);
	}

    /**
      * Process Project Media Delete
      *
      * @param  mix $projectIdOrUid
      * @param  number $mediaType
      *
      * @return  array
      *---------------------------------------------------------------- */

    public function processDeleteProjectMedia($projectIdOrUid, $mediaType)
    {
        $project = $this->projectRepository->fetch($projectIdOrUid);

        // Check if $project not exist then throw not found
        // exception
        if (__isEmpty($project)) {
            return $this->engineReaction(18, null, __tr('Project not found.'));
        }

        if ($mediaType == 1) { // Delete Logo
            if ($this->mediaEngine->deleteProjectLogoOrFavicon($project->_uid, $project->logo_image)) {
                return $this->engineReaction(1, null, __tr('Logo deleted successfully.'));
            }
        } else if ($mediaType == 2) { // Delete Favicon
            if ($this->mediaEngine->deleteProjectLogoOrFavicon($project->_uid, $project->favicon_image)) {
                return $this->engineReaction(1, null, __tr('Favicon deleted successfully.'));
            }
        }

        return $this->engineReaction(2, null, __tr('Something went wrong on server.'));
    }
}