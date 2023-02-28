<?php
/*
* VersionRepository.php - Repository file
*
* This file is part of the Version component.
*-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\Version\Repositories;

use App\Yantrana\Base\BaseRepository;
 
use App\Yantrana\Components\Version\Models\VersionModel;
use App\Yantrana\Components\Version\Interfaces\VersionRepositoryInterface;

class VersionRepository extends BaseRepository
                          implements VersionRepositoryInterface 
{ 
    
    /**
      * Fetch the record of Version
      *
      * @param    int || string $idOrUid
      *
      * @return    eloquent collection object
      *---------------------------------------------------------------- */

    public function fetch($idOrUid)
    {   
        if (is_numeric($idOrUid)) {

            return VersionModel::where('_id', $idOrUid)->first();
        }

        return VersionModel::where('_uid', $idOrUid)->first();
    }

    
    /**
     * Fetch version datatable source
    *
    * @return  mixed
    *---------------------------------------------------------------- */

    public function fetchProjectVersions($projectId)
    {
        return VersionModel::orderBy('created_at', 'desc')->where('projects__id', $projectId)->get();
    }

    /**
    * Delete $version record and return response
    *
    * @param  array $inputData
    *
    * @return  mixed
    *---------------------------------------------------------------- */

    public function delete($object)
    {   
        // Check if $version deleted
        if ($object->delete()) {

            return true;
        }

        return false;
    }

    /**
    * Store new version record and return response
    *
    * @param  array $inputData
    *
    * @return  mixed
    *---------------------------------------------------------------- */

    public function store($inputData)
    {   
        $keyValues = [
            'version',
            'slug' => $inputData['slug'],
            'mark_as_primary',
            'projects__id',
            'status'      
        ];

        $newVersion = new VersionModel;

        // Check if task testing record added then return positive response
        if ($newVersion->assignInputsAndSave($inputData, $keyValues)) {
            activityLog(16, $newVersion->_id, 1);
            return $newVersion;
        }

        return false;
    }

    /**
     * Update version record and return response
    *
    * @param  object $version
    * @param  array $inputData
    *
    * @return  mixed
    *---------------------------------------------------------------- */

    public function update($version, $inputData)
    {       
        // Check if version updated then return positive response
        if ($version->modelUpdate($inputData)) {
            activityLog(16, $version->_id, 2);
            return $version;
        }

        return false;
    }
  	
  	 /**
    * Store new version record and return response
    *
    * @param  array $inputData
    *
    * @return  mixed
    *---------------------------------------------------------------- */

    public function updateVersionPrimaryStatus($exceptVersion, $projectId)
    {   
    	return VersionModel::where('_id', '!=', $exceptVersion)
					->where('projects__id', '=', $projectId)
					->update(['mark_as_primary' => null]);
    }

    /**
    * Fetch Version by slug
    *
    * @param string $versionSlug
    *
    * @return  mixed
    *---------------------------------------------------------------- */

    public function fetchVersionBySlug($versionSlug, $projectId)
    {
        if (isFromEmbedView($versionSlug)) {
            return VersionModel::where([
                                    '_uid' => $versionSlug,
                                    'projects__id' => $projectId
                                ])
                                ->first();
        } else {
            return VersionModel::where([
                                        'slug' => $versionSlug,
                                        'projects__id' => $projectId
                                    ])
                                ->first();
        }
    }

    /**
     * Fetch version datatable source
    *
    * @return  mixed
    *---------------------------------------------------------------- */

    public function fetchProjectVersionsWithArticle($projectId)
    {
        return VersionModel::orderBy('doc_versions.created_at', 'desc')
							->where('projects__id', $projectId)
							->where('status', 1)
                            ->with(['articles' => function($query) {
                                $query->orderBy('list_order', 'asc')->whereNull('previous_articles__id');
                            }])->get();
    }
}