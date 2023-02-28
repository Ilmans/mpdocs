<?php
/*
* LanguageController.php - Controller file
*
* This file is part of the Language component.
*-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\Language\Controllers;


use App\Yantrana\Components\Language\Requests\LanguageAddRequest; 
use App\Yantrana\Components\Language\Requests\LanguageEditRequest;
use App\Yantrana\Base\BaseController;
use App\Yantrana\Support\CommonPostRequest; 
use App\Yantrana\Components\Language\LanguageEngine;

class LanguageController extends BaseController 
{    
    /**
     * @var  LanguageEngine $languageEngine - Language Engine
     */
    protected $languageEngine;

    /**
      * Constructor
      *
      * @param  LanguageEngine $languageEngine - Language Engine
      *
      * @return  void
      *-----------------------------------------------------------------------*/

    function __construct(LanguageEngine $languageEngine)
    {
        $this->languageEngine = $languageEngine;
    }
    
     
    /**
      * list of Language
      *
      * @return  json object
      *---------------------------------------------------------------- */

  	public function prepareLanguageList() 
  	{ 
      	return $this->languageEngine
                  	->prepareLanguageDataTableSource();
  	}
     
	/**
	  * Language process delete 
	  *
	  * @param  mix $languageIdOrUid
	  *
	  * @return  json object
	  *---------------------------------------------------------------- */

	public function processLanguageDelete($languageIdOrUid, CommonPostRequest $request)
	{   
	    $processReaction = $this->languageEngine
	                            ->processLanguageDelete($languageIdOrUid);

	    return __processResponse($processReaction, [], [], true);
	}
 
    /**
      * Language Add Support Data 
      *
      * @return  json object
      *---------------------------------------------------------------- */

    public function prepareLanguageSupportData()
    {   
        $processReaction = $this->languageEngine
                                ->prepareLanguageSupportData();

        return __processResponse($processReaction);
    }
 
	/**
	  * Language create process 
	  *
	  * @param  object LanguageListRequest $request
	  *
	  * @return  json object
	  *---------------------------------------------------------------- */

	public function processLanguageCreate(LanguageAddRequest $request)
	{   
	    $processReaction = $this->languageEngine
	                            ->processLanguageCreate($request->all());

	    return __processResponse($processReaction, [], [], true);
	}
 
    /**
  	  * Language get update data 
  	  *
  	  * @param  mix $languageIdOrUid
  	  *
  	  * @return  json object
  	  *---------------------------------------------------------------- */

	public function updateLanguageData($languageIdOrUid)
	{   
    	$processReaction = $this->languageEngine
                            ->prepareLanguageUpdateData($languageIdOrUid);

    	return __processResponse($processReaction, [], [], true);
	}

	/**
  	  * Language process update 
  	  * 
  	  * @param  mix @param  mix $languageIdOrUid
  	  * @param  object LanguageListRequest $request
  	  *
  	  * @return  json object
  	  *---------------------------------------------------------------- */

	public function processLanguageUpdate($languageIdOrUid, LanguageEditRequest $request)
	{   
    	$processReaction = $this->languageEngine
                            	->processLanguageUpdate($languageIdOrUid, $request->all());

    	return __processResponse($processReaction, [], [], true);
	}

}