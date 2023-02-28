<?php
/*
* LoginLog.php - Model file
*
* This file is part of the User component.
*-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\User\Models;

use App\Yantrana\Base\BaseModel;

class LoginLog extends BaseModel
{
    /**
     * @var string $table - The database table used by the model.
     */
    protected $table = "login_logs";

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
    public $timestamps = true;

    /**
     * The generate UID or not
     *
     * @var string
     *----------------------------------------------------------------------- */
    
    protected $isGenerateUID = false;

    /**
     * @var array $casts - The attributes that should be casted to native types..
     */
    protected $casts = [
        '_id'     => 'integer',
        'role'    => 'integer',
        'user_id' => 'integer'
    ];

    /**
     * @var array $fillable - The attributes that are mass assignable.
     */
    protected $fillable = [];
}
