<?php
/*
* VersionController.php - Controller file
*
* This file is part of the Version component.
*-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\Version\Controllers;

use App\Yantrana\Base\BaseController;

use App\Yantrana\Components\Version\VersionEngine;
use App\Yantrana\Components\Home\HomeEngine;
use App\Yantrana\Support\CommonPostRequest;
use App\Yantrana\Components\Version\Requests\{
    VersionAddRequest, VersionEditRequest
}; 

class VersionController extends BaseController 
{    
    /**
     * @var  VersionEngine $versionEngine - Version Engine
     */
    protected $versionEngine;

    /**
     * @var  HomeEngine $homeEngine - Home Engine
     */
    protected $homeEngine;

    /**
      * Constructor
      *
      * @param  VersionEngine $versionEngine - Version Engine
      *
      * @return  void
      *-----------------------------------------------------------------------*/

    function __construct(
        VersionEngine $versionEngine,
        HomeEngine $homeEngine
    )
    {
        $this->versionEngine    = $versionEngine;
        $this->homeEngine       = $homeEngine;
    }

    /**
      * list of Version
      *
      * @return  json object
      *---------------------------------------------------------------- */

  	public function readProjectInfo($projectIdOrUid) 
  	{        
        $processReaction = $this->versionEngine->prepareProjectInfo($projectIdOrUid);

        return __processResponse($processReaction, [], [], true);
  	}

    /**
      * list of Version
      *
      * @return  json object
      *---------------------------------------------------------------- */

  	public function prepareVersionList($projectIdOrUid) 
  	{        
        $processReaction = $this->versionEngine->prepareVersionList($projectIdOrUid);

        return __processResponse($processReaction, [], [], true);
  	}
     
	/**
	  * Version process delete 
	  *
	  * @param  mix $projectIdOrUid
	  *
	  * @return  json object
	  *---------------------------------------------------------------- */

	public function processVersionDelete($projectIdOrUid, $versionUid, CommonPostRequest $request)
	{   
	    $processReaction = $this->versionEngine->processVersionDelete($projectIdOrUid, $versionUid);

	    return __processResponse($processReaction, [], [], true);
	}
 
    /**
      * Version Add Support Data 
      *
      * @return  json object
      *---------------------------------------------------------------- */

    public function prepareVersionSupportData($projectIdOrUid)
    {   
        $processReaction = $this->versionEngine
                                ->prepareVersionSupportData($projectIdOrUid);

        return __processResponse($processReaction, [], [], true);
    }
 
	/**
	  * Version create process 
	  *
	  * @param  object VersionListRequest $request
	  *
	  * @return  json object
	  *---------------------------------------------------------------- */

	public function processVersionCreate($projectIdOrUid, VersionAddRequest $request)
	{   
	    $processReaction = $this->versionEngine
	                            ->processVersionCreate($projectIdOrUid, $request->all());

	    return __processResponse($processReaction);
	}
 
    /**
  	  * Version get update data 
  	  *
  	  * @param  mix $projectIdOrUid
  	  *
  	  * @return  json object
  	  *---------------------------------------------------------------- */

	public function updateVersionData($projectIdOrUid, $versionUid)
	{   
    	$processReaction = $this->versionEngine
                            ->prepareVersionUpdateData($projectIdOrUid, $versionUid);

    	return __processResponse($processReaction, [], [], true);
	}

	/**
  	  * Version process update 
  	  * 
  	  * @param  mix @param  mix $projectIdOrUid
  	  * @param  object VersionListRequest $request
  	  *
  	  * @return  json object
  	  *---------------------------------------------------------------- */

	public function processVersionUpdate($projectIdOrUid, $versionUid, VersionEditRequest $request)
	{   
    	$processReaction = $this->versionEngine
                            	->processVersionUpdate($projectIdOrUid, $versionUid, $request->all());

    	return __processResponse($processReaction, [], [], true);
    }    

     /**
     * process download invoice.
     *
     * @param string $orderUid
     *
     * @return json response
     *---------------------------------------------------------------- */

    public function downloadDocumentPdf($projectIdOrUid, $versionUid)
    {
        return $this->versionEngine->processDownloadDocumentPdf($projectIdOrUid, $versionUid);
    }
}