<?php
/*
* LanguageRepository.php - Repository file
*
* This file is part of the Language component.
*-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\Language\Repositories;

use App\Yantrana\Base\BaseRepository;
 
use App\Yantrana\Components\Language\Models\LanguageModel;
use App\Yantrana\Components\Language\Interfaces\LanguageRepositoryInterface;

class LanguageRepository extends BaseRepository implements LanguageRepositoryInterface 
{ 
    
    /**
      * Fetch the record of Language
      *
      * @param    int || string $idOrUid
      *
      * @return    eloquent collection object
      *---------------------------------------------------------------- */

    public function fetch($idOrUid)
    {   
        if (is_array($idOrUid)) 
        {
            return LanguageModel::whereIn('_id', $idOrUid)->get();
        }

        return LanguageModel::where('_id', $idOrUid)->first();
    }

    /**
    * Fetch the record of Language
    *
    * @param    int || string $idOrUid
    *
    * @return    eloquent collection object
    *---------------------------------------------------------------- */

    public function fetchRTL()
    {   
        return LanguageModel::where('is_rtl', 1)->get();
    }

    /**
      * Fetch all languages
      *
      * @return    eloquent collection object
      *---------------------------------------------------------------- */

    public function fetchLanguagebyIds($languages)
    {   
      return LanguageModel::whereIn('_id', $languages)->get();
    }
	
	/**
      * Fetch all languages
      *
      * @return    eloquent collection object
      *---------------------------------------------------------------- */

    public function fetchAllLanguages()
    {   
        return LanguageModel::where('status', 1)->get();
    }

    /**
      * Fetch language datatable source
      *
      * @return  mixed
      *---------------------------------------------------------------- */
 
	public function fetchLanguageDataTableSource()
	{   
    	$dataTableConfig = [
        	'searchable' => [
                '_id',            
                'name',
                'status'            
            ]
    	];

    	return LanguageModel::dataTables($dataTableConfig)
                    ->toArray();
	}

    /**
	  * Delete $language record and return response
	  *
	  * @param  array $inputData
	  *
	  * @return  mixed
	  *---------------------------------------------------------------- */

	public function deleteLanguage($language)
	{   
	    // Check if $language deleted
	    if ($language->delete()) {

	        return true;
	    }

	    return false;
	}

	/**
	  * Store new language record and return response
	  *
	  * @param  array $inputData
	  *
	  * @return  mixed
	  *---------------------------------------------------------------- */

	public function storeLanguage($inputData)
	{   
        $keyValues = [
            'name',         
            '_id',    
            'status',
            'is_rtl'    
        ];

	    $newLanguage = new LanguageModel;
	    
	    // Check if task testing record added then return positive response
	    if ($newLanguage->assignInputsAndSave($inputData, $keyValues)) {

	        return $newLanguage;
	    }

	    return false;
	}

    /**
  	  * Update language record and return response
  	  *
  	  * @param  object $language
  	  * @param  array $inputData
  	  *
  	  * @return  mixed
  	  *---------------------------------------------------------------- */

	public function updateLanguage($language, $inputData)
	{       
    	// Check if language updated then return positive response
    	if ($language->modelUpdate($inputData)) {

        	return true;
    	}

    	return false;
	}

    /**
      * Fetch all languages
      *
      * @return    eloquent collection object
      *---------------------------------------------------------------- */

    public function fetchAllRequiredLanguages($languages = [])
    {   
    	if (!__isEmpty($languages)) {
	        return LanguageModel::where('status', 1)
	    					->whereIn('_id', $languages)
	    					->get();
    	}

    	return LanguageModel::where('status', 1)->get();
    }
}