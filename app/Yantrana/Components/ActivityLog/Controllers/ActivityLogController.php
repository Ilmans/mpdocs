<?php
/*
* ActivityLogController.php - Controller file
*
* This file is part of the Activity component.
*-----------------------------------------------------------------------------*/
namespace App\Yantrana\Components\ActivityLog\Controllers;

use App\Yantrana\Base\BaseController;
use App\Yantrana\Components\ActivityLog\ActivityLogEngine;

class ActivityLogController extends BaseController 
{    
    /**
     * @var  ActivityLogEngine $activityLogEngine - ActivityLog Engine
     */
    protected $activityLogEngine;

    /**
      * Constructor
      *
      * @param  ActivityLogEngine $activityLogEngine - ActivityLog Engine
      *
      * @return  void
      *-----------------------------------------------------------------------*/

    function __construct(ActivityLogEngine $activityLogEngine)
    {
        $this->activityLogEngine = $activityLogEngine;
    }

	 
	/**
	* Request to engine for activityLog data
	*
	* @return  json object
	*---------------------------------------------------------------- */

	public function prepareActivityLogList($startDate, $endDate) 
	{       
		return $this->activityLogEngine->prepareDatatableActivityList($startDate, $endDate);
	}
}