<?php

namespace App\Yantrana\Components\User\Models;

use App\Yantrana\Base\BaseModel;

class LoginAttempt extends BaseModel
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'login_attempts';

    /**
     * Does it has has Entity Ownership ID
     *
     * @var bool
     *----------------------------------------------------------------------- */
    protected $hasEoId = true;        

    /**
     * The generate UID or not
     *
     * @var string
     *----------------------------------------------------------------------- */
    
    protected $isGenerateUID = false;

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        '_id'      => 'integer',
        'attempts' => 'integer',
    ];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
   // public $timestamps = true;
}
