<?php

namespace App\Yantrana\__Laraware\Core;

/*
 * Core Repository - 1.0.4 - 14 AUG 2020
 * 
 * Base Repository for Laravel applications
 *
 *
 * Dependencies:
 * 
 * Laravel     5.0 +     - http://laravel.com
 * 
 *
 *-------------------------------------------------------- */

use DB;
use Closure;
use Exception;
use Cache;

abstract class CoreRepository
{
    /**
     * enable or disable caching viaCache.
     *
     * @var string
     */
    protected $enableCache = true;

    // Except this columns & return another columns.
    protected $exceptColumns = [];

    // Exclude only this columns.
    protected $onlyColumns   = [];

    /**
     * primary model instance
     * eg. YourModelModel::class;
     *
     * @var object
     */
    protected $primaryModel;

    /**
     * Except columns
     *
     * @param array $columns
     *
     * @return array
     * @since  1.0.0 - 11 JUN 2019 - Added from Base
     *-----------------------------------------------------------------------*/

    public function exceptColumns(array $exceptColumns)
    {
        $this->exceptColumns = $exceptColumns;

        return $this;
    }

    /**
     * Only this columns
     *
     * @param array $columns
     *
     * @return array
     * @since  1.0.0 - 11 JUN 2019 - Added from Base
     *-----------------------------------------------------------------------*/

    public function onlyColumns(array $onlyColumns)
    {
        $this->onlyColumns = $onlyColumns;

        return $this;
    }

    /**
     * DB transaction process.
     *
     * @param Closure $callback
     */
    public function processTransaction(Closure $callback, Closure $failback = null)
    {
        $reactionCode = 14;
        $returnProcessReaction = null;

        DB::beginTransaction();

        // We'll simply execute the given callback within a try / catch block
        // and if we catch any exception we can rollback the transaction
        // so that none of the changes are persisted to the database.
        try {
            $reactionCode = $callback($this);

            if (is_array($reactionCode) === true) {
                $returnProcessReaction = $reactionCode;
                $reactionCode = $reactionCode[0];
            }

            if ($reactionCode == 1) {
                DB::commit();
            } else {
                DB::rollBack();
                // custom rollback logic
                if ($failback) {
                    $failback($this);
                }
            }
        }

        // If we catch an exception, we will roll back so nothing gets messed
        // up in the database. Then we'll re-throw the exception so it can
        // be handled how the developer sees fit for their applications.

        catch (Exception $e) {
            DB::rollBack();
            // custom rollback logic
            if ($failback) {
                $failback($this);
            }

            $reactionCode = 19;

            throw $e;
        }

        return (__isEmpty($returnProcessReaction) === true)
            ? $reactionCode : $returnProcessReaction;
    }

    /**
     * To return response from Process Transaction.
     *
     * @param array  $reactionCode - Reaction from Repo
     * @param array  $data         - Array of data if needed
     * @param string $message      - Message if any
     * 
     * @return array
     *---------------------------------------------------------------- */
    public function transactionResponse($reactionCode, $data = null, $message = null)
    {
        return [
            $reactionCode,
            $data,
            $message,
        ];
    }

    /**
     * Controllable Cache function for DB Queries.
     *
     * @param string      $cacheId           - Cache ID/Key
     * @param int/Closure $minutesOrCallback - Number of minutes to remember / Callback containing query to cache
     * @param Closure     $callback          - Callback containing query to cache
     * 
     * @return array
     *---------------------------------------------------------------- */
    public function viaCache($cacheId, $minutesOrCallback, Closure $callback = null)
    {
        // check if query cache disabled
        if ((env('ENABLE_DB_CACHE', true) == false) or ($this->enableCache === false)) {
            // minutes not sent it must be callable function
            if ($minutesOrCallback instanceof Closure) {
                return $minutesOrCallback();
            }

            return $callback();
        }

        // if minutes sent then remember accordingly 
        if ($minutesOrCallback and is_numeric($minutesOrCallback) == true) {
            return Cache::remember($cacheId, $minutesOrCallback, $callback);
        }
        // minutes not sent it must be callable function
        if ($minutesOrCallback instanceof Closure) {
            return Cache::rememberForever($cacheId, $minutesOrCallback);
        }

        return Cache::rememberForever($cacheId, $callback);
    }

    /**
     * Remove Cache item.
     *
     * @param string $cacheId - Cache ID/Key
     * 
     * @return array
     *---------------------------------------------------------------- */
    public function clearCache($cacheId)
    {
        return Cache::forget($cacheId);
    }

    /**
     * Fetch the record
     *
     * @param int || int/string $idOrUid
     *
     * @return eloquent collection object
     * @since  1.0.0 - 11 JUN 2019
     *---------------------------------------------------------------- */

    public function fetchIt($idOrUid)
    {
        // create eloquent instance
        $primaryModel = new $this->primaryModel;
        // start query
        $query = $primaryModel::query();
        // check if its numeric id or uuid
        if (is_numeric($idOrUid)) {
            // if numeric id it must be primary key
            $query->where($primaryModel->getKeyName(), $idOrUid);
        } else {
            // if not then its uuid
            $query->where($primaryModel->getUidKeyName(), $idOrUid);
        }
        // get all the columns except 
        if (!empty($this->exceptColumns)) {
            $query->selectExcept($this->exceptColumns);
        }
        // get only defined columns
        if (!empty($this->onlyColumns)) {
            $query->selectOnly($this->onlyColumns);
        }
        // return the result
        return $query->first();
    }

    /**
     * Store record
     *
     * @param array $inputData
     * @param array $keyValues    
     *
     * @return eloquent collection object | bool (false)
     * @since  1.0.0 - 11 JUN 2019
     *---------------------------------------------------------------- */

    public function storeIt(array $inputData, array $keyValues = [])
    {
        if(empty($keyValues)) {
            $keyValues = array_keys($inputData);
        }
        // create eloquent instance
        $primaryModel = new $this->primaryModel;
        // assign & save
        if ($primaryModel->assignInputsAndSave($inputData, $keyValues)) {
            return $primaryModel;
        }

        return false;
    }

    /**
     * Delete Record
     *
     * @param object $eloquentModel
     *
     * @return bool
     * @since  1.0.0 - 11 JUN 2019
     *-----------------------------------------------------------------------*/

    public function deleteIt($eloquentModel)
    {
        if ($eloquentModel->deleteIt()) {
            return true;
        }

        return false;
    }

    /**
     * update Record
     *
     * @param object $eloquentModel
     * @param array $inputData    
     *
     * @return bool
     * @since  1.0.0 - 11 JUN 2019
     *-----------------------------------------------------------------------*/

    public function updateIt($eloquentModel, $inputData)
    {
        if ($eloquentModel->modelUpdate($inputData)) {
            return true;
        }

        return false;
    }
}
