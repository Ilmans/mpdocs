<?php

namespace App\Yantrana\Components\User\Models;

use App\Yantrana\Base\BaseModel;
use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use App\Yantrana\Components\User\Models\UserAuthorityModel;

use App\Yantrana\Components\User\Models\UserProfile;
use App\Yantrana\Components\User\Models\SocialAccess;

class User extends BaseModel implements
    AuthenticatableContract,
                                    AuthorizableContract,
                                    CanResetPasswordContract
{
    use Authenticatable, Authorizable, CanResetPassword;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['password', 'remember_token'];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        '_id'               => 'integer',
        'status'            => 'integer',
        'user_roles__id'    => 'integer',
        '__permissions'     => 'array',
    ];

    /**
     * @var  array $fillable - The attributes that are mass assignable.
     */
    // protected $fillable = [
    //     'first_name', 
    //     'last_name',    
    //     'username',   
    //     'email',      
    //     'status',             
    // ];

    /**
     * Let the system knows Text columns treated as JSON
     *
     * @var array
     *----------------------------------------------------------------------- */
    protected $jsonColumns = [
        '__permissions' => [
            'allow' => 'array',
            'deny'  => 'array'
        ]
    ];

    protected $cacheIds = [
        'cache.users'
    ];

    /**
    * Get the profile record associated with the user.
    */
    public function profile()
    {
        return $this->hasOne(UserProfile::class, 'users__id')->with('country');
    }

    /**
        * Get the profile record associated with the user.
        */
    public function userAuthority()
    {
        return $this->hasOne(UserAuthorityModel::class, 'users__id');
    }
}
