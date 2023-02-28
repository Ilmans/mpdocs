<?php

namespace App\Yantrana\Components\User\Models;

use App\Yantrana\Base\BaseModel;

class EmailChangeRequest extends BaseModel
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'email_change_requests';

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        '_id'        => 'integer',
        'users__id'  => 'integer',
    ];
}
