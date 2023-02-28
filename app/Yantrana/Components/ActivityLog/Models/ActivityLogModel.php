<?php
/*
* ActivityLogModel.php - Model file
*
* This file is part of the ActivityLog component.
*-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\ActivityLog\Models;

use App\Yantrana\__Laraware\Core\CoreModel;

class ActivityLogModel extends CoreModel 
{
    
     /**
     * @var string - The database table used by the model.
     */

    protected $table = 'activity_logs';

    /**
     * Does it has has Entity Ownership ID
     *
     * @var bool
     *----------------------------------------------------------------------- */
    protected $hasEoId = true;        

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */

    public $timestamps = false;

    /**
     * The generate UID or not.
     *
     * @var string
     *----------------------------------------------------------------------- */
    protected $isGenerateUID = false;

    /**
     * @var array - The attributes that should be casted to native types..
     */

    protected $casts = [
        '_id'       => 'integer',
        'users_id'  => 'integer',
        '__data'    => 'array',
        'entity_id'	=> 'integer',
        'user_role_id' => 'integer',
        'project_id' => 'integer'
    ];

    /**
     * Let the system knows Text columns treated as JSON
     *
     * @var array
     *----------------------------------------------------------------------- */
    protected $jsonColumns = [
        '__data' => [
            'user_info'      => 'array',
            'itemId'         => 'integer',
            'ip'             => 'string',
            'action'         => 'string',
            'item'           => 'string',
            'itemName'		 => 'string',
            'description'    => 'string'
        ]
    ];

    /**
     * @var array - The attributes that are mass assignable.
     */

    protected $fillable = ['created_at', 'user_id', '__data', 'action_type', 'entity_type', 'entity_id', 'user_role_id', 'project_id', 'eo_uid'];
}
