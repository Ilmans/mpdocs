<?php
/*
* Project.php - Model file
*
* This file is part of the Project component.
*-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\Project\Models;

use App\Yantrana\Base\BaseModel;
use App\Yantrana\Components\Version\Models\VersionModel;

class ProjectModel extends BaseModel 
{ 
    /**
     * @var  string $table - The database table used by the model.
     */
    protected $table = "projects";
    
    /**
     * Does it has has Entity Ownership ID
     *
     * @var bool
     *----------------------------------------------------------------------- */
    protected $hasEoId = true;   

    /**
     * @var  array $casts - The attributes that should be casted to native types.
     */
    protected $casts = [
        'status' => 'integer',
        'user_authorities__id'=> 'integer',
        'type'=> 'integer',
    	'__data' => 'array'
    ];

    /**
    * Let the system knows Text columns treated as JSON
    *
    * @var array
    *----------------------------------------------------------------------- */

    protected $jsonColumns = [
        '__data'   => [
            'project_languages'        => 'array'
        ]
    ];


    /**
     * Get all of the owning commentable models.
     */
    public function versions()
    {
        return  $this->hasMany(VersionModel::class, 'projects__id', '_id');
    }

    /**
     * Get all of the owning commentable models.
     */
    public function latestVersion()
    {
        return  $this->hasOne(VersionModel::class, 'projects__id', '_id')->latest();
    }


    /**
     * Get all of the owning commentable models.
     */
    public function primaryVersion()
    {
        return  $this->hasOne(VersionModel::class, 'projects__id', '_id')
                    ->where('mark_as_primary', 1);
    }

    /**
     * Get all of the owning commentable models.
     */
    public function activeVersions()
    {
        return  $this->hasMany(VersionModel::class, 'projects__id', '_id')->with('activeArticles');
    }
}