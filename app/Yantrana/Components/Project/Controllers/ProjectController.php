<?php
/*
* ProjectController.php - Controller file
*
* This file is part of the Project component.
*-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\Project\Controllers;

use App\Yantrana\Components\Project\Requests\ProjectAddRequest; 
use App\Yantrana\Components\Project\Requests\ProjectEditRequest;
use App\Yantrana\Support\CommonPostRequest;
use App\Yantrana\Base\BaseController;

 
use App\Yantrana\Components\Project\ProjectEngine;
class ProjectController extends BaseController 
{    
    /**
     * @var  ProjectEngine $projectEngine - Project Engine
     */
    protected $projectEngine;

    /**
      * Constructor
      *
      * @param  ProjectEngine $projectEngine - Project Engine
      *
      * @return  void
      *-----------------------------------------------------------------------*/

    function __construct(ProjectEngine $projectEngine)
    {
        $this->projectEngine = $projectEngine;
    }
    
     
    /**
      * list of Project
      *
      * @return  json object
      *---------------------------------------------------------------- */

  	public function prepareProjectList() 
  	{ 
      	return $this->projectEngine
                  	->prepareProjectDataTableSource();
  	}
     
	/**
	  * Project process delete 
	  *
	  * @param  mix $projectIdOrUid
	  *
	  * @return  json object
	  *---------------------------------------------------------------- */

	public function processProjectDelete($projectIdOrUid, CommonPostRequest $request)
	{   
	    $processReaction = $this->projectEngine
	                            ->processProjectDelete($projectIdOrUid);

	    return __processResponse($processReaction, [], [], true);
	}
 
    /**
      * Project Add Support Data 
      *
      * @return  json object
      *---------------------------------------------------------------- */

    public function prepareProjectSupportData()
    {   
        $processReaction = $this->projectEngine->prepareProjectSupportData();

        return __processResponse($processReaction, [], [], true);
    }
 
	/**
	  * Project create process 
	  *
	  * @param  object ProjectListRequest $request
	  *
	  * @return  json object
	  *---------------------------------------------------------------- */

	public function processProjectCreate(ProjectAddRequest $request)
	{   
	    $processReaction = $this->projectEngine->processProjectCreate($request->all());

	    return __processResponse($processReaction, [], [], true);
	}
 
    /**
  	  * Project get update data 
  	  *
  	  * @param  mix $projectIdOrUid
  	  *
  	  * @return  json object
  	  *---------------------------------------------------------------- */

	public function updateProjectData($projectIdOrUid)
	{   
    	$processReaction = $this->projectEngine->prepareProjectUpdateData($projectIdOrUid);

    	return __processResponse($processReaction, [], [], true);
	}

	/**
  	  * Project process update 
  	  * 
  	  * @param  mix @param  mix $projectIdOrUid
  	  * @param  object ProjectListRequest $request
  	  *
  	  * @return  json object
  	  *---------------------------------------------------------------- */

	public function processProjectUpdate($projectIdOrUid, ProjectEditRequest $request)
	{   
    	$processReaction = $this->projectEngine
                            	->processProjectUpdate($projectIdOrUid, $request->all());

    	return __processResponse($processReaction, [], [], true);
    }

    /**
      * Project process delete 
      * 
      * @param  mix @param  mix $projectIdOrUid
      * @param  object ProjectListRequest $request
      *
      * @return  json object
      *---------------------------------------------------------------- */

    public function processProjectLanguageDelete($projectIdOrUid, $languageId, CommonPostRequest $request)
    {   
        $processReaction = $this->projectEngine
                                ->processProjectLanguageDelete($projectIdOrUid, $languageId);

        return __processResponse($processReaction, [], [], true);
    }
    

    /**
    * Get the home article lists
    *---------------------------------------------------------------- */

    public function publicProjectDetailsView($projectUid)
    {   
        $processReaction = $this->projectEngine->preparePublicProjectDetails($projectUid);

        if ($processReaction['reaction_code'] !== 1) {

            return redirect()->route('public.app'
                                )->with([
                                    'error' => true,
                                    'message' => $processReaction['message'],
                                ]);
        }

        
        return $this->loadPublicView('project.public.details', $processReaction['data']);
    }

    /**
      * Project details
      *
      * @return  json object
      *---------------------------------------------------------------- */

    public function prepareProjectDetails($projectIdOrUid)
    {   
        $processReaction = $this->projectEngine->prepareProjectDetails($projectIdOrUid);

        return __processResponse($processReaction, [], [], true);
    }
    
    /**
      * Delete Project Media
      *
      * @param string $projectIdOrUid
      * @param number $mediaType
      *
      * @return  json object
      *---------------------------------------------------------------- */

    public function deleteProjectMedia($projectIdOrUid, $mediaType)
    {
        $processReaction = $this->projectEngine->processDeleteProjectMedia($projectIdOrUid, $mediaType);

        return __processResponse($processReaction, [], [], true);
    }
}