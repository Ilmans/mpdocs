<?php

/*
* UserProfile.php - Model file
*
* This file is part of the User component.
*-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\User\Models;

use App\Yantrana\Base\BaseModel;
use App\Yantrana\Components\Country\Models\Country;

class UserProfile extends BaseModel
{
    /**
     * @var string $table - The database table used by the model.
     */
    protected $table = "user_profiles";

    /**
     * @var array $casts - The attributes that should be casted to native types.
     */
    protected $casts = [
        "_id"            => "integer",
        "countries__id" => "integer",
        "users__id"        => "integer"
    ];

    /**
     * @var array $fillable - The attributes that are mass assignable.
     */
    protected $fillable = [];

    /**
    * Get the profile record associated with the user.
    */
    public function country()
    {
        return $this->hasOne(Country::class, '_id', 'countries__id');
    }
}
