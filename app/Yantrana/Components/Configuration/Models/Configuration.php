<?php

/*
* Configuration.php - Model file
*
* This file is part of the Configuration component.
*-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\Configuration\Models;

use App\Yantrana\Base\BaseModel;

class Configuration extends BaseModel
{
    /**
     * @var string $table - The database table used by the model.
     */
    protected $table = "configurations";

    /**
     * Does it has has Entity Ownership ID
     *
     * @var bool
     *----------------------------------------------------------------------- */
    protected $hasEoId = true;        

    /**
     * Caching Ids related to this model which may need to clear on add/update/delete.
     *
     * @var string
     *----------------------------------------------------------------------- */
    protected $cacheIds = [
        'cache.all.configurations'
    ];

    /**
     * @var array $casts - The attributes that should be casted to native types.
     */
    protected $casts = [
        "_id"       => "integer",
        "data_type" => "integer"
    ];

    /**
     * @var array $fillable - The attributes that are mass assignable.
     */
    protected $fillable = [];

    /**
     * The generate UID or not
     *
     * @var string
     *----------------------------------------------------------------------- */
    
    protected $isGenerateUID = false;
}
