<?php
/*
* ActivityLogRepository.php - Repository file
*
* This file is part of the ActivityLog component.
*-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\ActivityLog\Repositories;

use Auth;
use DB;
use App\Yantrana\Base\BaseRepository;
 
use App\Yantrana\Components\ActivityLog\Models\ActivityLogModel;	

class ActivityLogRepository extends BaseRepository
{ 
   /**
      * Fetch the record of ActivityLog
      *
      * @param    int || string $idOrUid
      *
      * @return    eloquent collection object
      *---------------------------------------------------------------- */

    public function fetch($idOrUid)
    {   
        if (is_numeric($idOrUid)) {

            return ActivityLogModel::where('_id', $idOrUid)->first();
        }

        return ActivityLogModel::where('_uid', $idOrUid)->first();
    }

     /**
      * Fetch sample datatable source
      *
      * @return array
      *---------------------------------------------------------------- */
 
	public function fetchActivityDataTableSource($startDate, $endDate)
	{   
    	$dataTableConfig = [
        	'searchable' => [   
        		'created_by_user' => DB::raw('CONCAT(first_name, " ", last_name)')       
            ]
    	];

    	$query 	=	ActivityLogModel::leftJoin('users', 'activity_logs.user_id', '=', 'users._id');

    	return $query->whereBetween(DB::raw('DATE(activity_logs.created_at)'), [$startDate, $endDate])
    							->select(
    								__nestedKeyValues([
    									'activity_logs' => [
    										'_id',
											'created_at',
											'__data',
											'action_type',
											'entity_type',
                                            'entity_id'
    									],
										'users' => [
											DB::raw('CONCAT(first_name, " ", last_name) as created_by_user'),
										]
    								])
    							)
    							->dataTables($dataTableConfig)->toArray();
	}

}