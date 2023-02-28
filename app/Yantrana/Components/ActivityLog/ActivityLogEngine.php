<?php
/*
* ActivityLogEngine.php - Main component file
*
* This file is part of the ActivityLog component.
*-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\ActivityLog;

use App\Yantrana\Base\BaseEngine;
use App\Yantrana\Components\ActivityLog\Repositories\ActivityLogRepository;

class ActivityLogEngine extends BaseEngine
{     
    /**
     * @var  ActivityLogRepository $activityLogRepository - ActivityLog Repository
     */
    protected $activityLogRepository;
    

    /**
    * Constructor
    *
    * @param  ActivityLogRepository $activityLogRepository - ActivityLog Repository
    * @param  UserRepository $userRepository    - User Repository
    * @param  CompanyRepository $companyRepository    - Company Repository
    *
    * @return  void
    *-----------------------------------------------------------------------*/
    function __construct(
    	ActivityLogRepository $activityLogRepository
 	)
    {
        $this->activityLogRepository = $activityLogRepository;
    }

	
  /**
  	* ActivityLog datatable source 
  	*
  	* @return  array
  	*---------------------------------------------------------------- */
    public function prepareDatatableActivityList($startDate, $endDate)
    { 
        $user = getAuthUser();
        $usertype =  $user->user_roles__id;

 	
        $activityCollection = $this->activityLogRepository->fetchActivityDataTableSource($startDate, $endDate);
	
    	$requireColumns = [
        	'_id',
        	'action_type',
            'formatted_action_type'  => function($key) {
				
                return config('__tech.activity_log.action_type.'.$key['action_type']);
            },
			'created_at' => function($key) {
				
                return formatDateTime($key['created_at'], 'd-m-Y g:i:s a');
            },
			'created_by_user',
			'ip'	=>	function($key) {
				
				return $key['__data']['ip'];
			},
			'entity_type' =>	function($key) {
				return config('__tech.activity_log.entity_type.'.$key['entity_type']);
			},
            'activity' => function($activity) {
                $activityLogData = $this->engineData($this->prepareActionTypeData($activity));
                return $activityLogData['actionTypeData']['activity'];
            },
            'description' => function($activity) {
                $activityLogData = $this->engineData($this->prepareActionTypeData($activity));
                return $activityLogData['actionTypeData']['description'];
            }
        ];
	    
    	return $this->dataTableResponse($activityCollection, $requireColumns, [
			'durations'     => 	configItem('durations'),
		]);

    }

    /**
  	* ActivityLog datatable source 
  	*
  	* @return  array
  	*---------------------------------------------------------------- */
    public function prepareActionTypeData($activityLogData)
    { 
        $entityType = config('__tech.activity_log.entity_type.'.$activityLogData['entity_type']);
        $actionType = config('__tech.activity_log.action_type.'.$activityLogData['action_type']);
        $itemName   = isset($activityLogData['__data']['itemName']) ? $activityLogData['__data']['itemName'] : null;
      	$entityID   = $activityLogData['entity_id'];
       	$activity 	= $entityType.' <b>'.$itemName.'</b> is '.$actionType;
		
        $actionTypeData = [
        	'activityLogId' => $activityLogData['_id'],
        	'entityID' 		=> $entityID,
        	'entity_type'	=> $activityLogData['entity_type'],
        	'itemTitle'		=> $itemName,
        	'entityType' 	=> $entityType,
        	'activity'		=> $activity,
            'description'   => isset($activityLogData['__data']['description']) ? $activityLogData['__data']['description'] : '-'
        ];

        return   $this->engineReaction(1, [
            'actionTypeData'      => $actionTypeData,
		]);
    }

}