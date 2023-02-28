<?php namespace App\Yantrana\Base;

use App\Yantrana\__Laraware\Core\CoreModel;
use App\Yantrana\Components\User\Models\ActivityLog;
use Auth;
use Datetime;
use Illuminate\Database\Eloquent\Builder;

abstract class BaseModel extends CoreModel
{

    /**
     * The custom primary key.
     *
     * @var string
     *----------------------------------------------------------------------- */
    
    protected $primaryKey = '_id';

    /**
     * The generate UID or not
     *
     * @var string
     *----------------------------------------------------------------------- */
    
    protected $isGenerateUID = true;
    
    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if($model->hasEoId) {
                $model->eo_uid = entityOwnershipId();
            }
        });

        /* static::updating(function ($model) {
            if($model->hasEoId) {
                $model->eo_uid = entityOwnershipId();
            }
        });
        */
        // clear cache if exist
        static::saving(function ($model) {
            if($model->hasEoId) {
                $model->eo_uid = entityOwnershipId();
            }
        });

        // clear cache if exist
        static::deleting(function ($model) {
            if($model->hasEoId) {
                $model->eo_uid = entityOwnershipId();
            }
        });

        static::addGlobalScope(function (Builder $builder) {
            // get the modal instance
            $model = new static;
            if($model->hasEoId) {
                // A much better way to restrict access to all base on entity
                $builder->where($model->getTable().'.eo_uid', entityOwnershipId());
            }
        });        
    }

    /*public function getCreatedAtAttribute()
    {
        return formatDateTime($this->attributes['created_at']);
    }*/

    /**
     * Generate activity log format
     *
     * @param object $model
     * @param object $description
     *
     * @var string
     *----------------------------------------------------------------------- */

    public static function activityFormat($model, $description)
    {
        // $model->getDirty() -  get which column & what vlaue change.
        // $model->table      - table name
        // Auth::user()       - user information
        // Request::ip()      - ip address


        $user = \Auth::user();

        ActivityLog::create([
            'user_id'    => $user->_id,
            'created_at' => new Datetime(),
            '__data'  => [
                'user_info' => [
                    'fname' => $user->first_name,
                    'lname' => $user->last_name,
                    'email' => $user->email
                ],
                'description'    => $description,
                'ip'             => \Request::ip(),
                'table'          => $model->table,
                'updated_column' => $model->getDirty()
            ]
            
        ]);
    }

    /**
     * Get the array of columns
     * @return mixed
     */
    private function getTableColumns()
    {
        return $this->getConnection()->getSchemaBuilder()->getColumnListing($this->getTable());
    }

    /**
     *  Event: onPrepareAndInserting
     *  You should return the same $item array to avoid any issues
     * 
     * @return array
     */
     function onPrepareAndInserting(array $item) {
        if($this->hasEoId) {
            $item['eo_uid'] = entityOwnershipId();
        }
        return $item;
    }    


    /**
     * Exclude an array of elements from the result.
     * @param $query
     * @param $columns
     * @return mixed
     */
    public function scopeSelectExcept($query, $columns)
    {
        $fetchColumns = empty($columns) ? [] : array_diff($this->getTableColumns(), (array) $columns);

        return empty($fetchColumns) ? $query : $query->select($fetchColumns);
    }

    /**
     * Exclude an array of elements from the result.
     * @param $query
     * @param $columns
     * @return mixed
     */
    public function scopeSelectOnly($query, $columns)
    {
        $fetchColumns = empty($columns) ? [] : array_intersect($this->getTableColumns(), (array) $columns);

        return empty($fetchColumns) ? $query : $query->select($fetchColumns);
    }
    

    /*public static function boot()
    {
        parent::boot();

        // Attach event handler, on deleting of the user
        static::creating(function($model)
        {
            static::activityFormat($model, 'record added sucessfully.');

        });

        // Attach event handler, on deleting of the user
        static::updated(function($model)
        {
            static::activityFormat($model, 'record updated sucessfully.');

        });


        static::saved(function ($model) {

           // static::activityFormat($model, 'record saved sucessfully.');

        });


        static::deleted(function ($model) {

            static::activityFormat($model, 'record deleted sucessfully.');

        });

    }*/
}
