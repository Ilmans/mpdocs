<?php
/*
* ProjectRepository.php - Repository file
*
* This file is part of the Project component.
*-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\Project\Repositories;

use App\Yantrana\Base\BaseRepository;
 
use App\Yantrana\Components\Project\Models\ProjectModel;
use App\Yantrana\Components\Version\Models\VersionModel;
use App\Yantrana\Components\Project\Interfaces\ProjectRepositoryInterface;

class ProjectRepository extends BaseRepository
                          implements ProjectRepositoryInterface 
{ 
    
    /**
      * Fetch the record of Project
      *
      * @param    int || string $idOrUid
      *
      * @return    eloquent collection object
      *---------------------------------------------------------------- */

    public function fetch($idOrUid, $status = null)
    {   
        $query = ProjectModel::query();

        if (is_numeric($idOrUid)) {

            $query->where('_id', $idOrUid);

        } else {

            $query->where('_uid', $idOrUid);
        }

        return $query
                ->when($status !== null, function($qu) use($status) {
                    $qu->where('status', '=', $status);
                })->first();
    }

    /**
    * Fetch the record of Project
    *
    * @param  int || string $idOrUid
    *
    * @return  eloquent collection object
    *---------------------------------------------------------------- */

    public function all($status = '')
    {   
        return ProjectModel::when($status, function($q) use($status) {
            $q->where('status', $status);
        })->get();
    }

    /**
      * Fetch the record of Project
      *
      * @return    eloquent collection object
      *---------------------------------------------------------------- */

    public function fetchAllProjects()
    {   
    	return ProjectModel::where('status', 1)->get();
    }
    
    
    /**
      * Fetch project datatable source
      *
      * @return  mixed
      *---------------------------------------------------------------- */
 
	public function fetchProjectDataTableSource()
	{   
    	$dataTableConfig = [
        	'searchable' => [
                'name',            
                'short_description',            
                'status',            
                'type'          
            ]
    	];

    	return ProjectModel::dataTables($dataTableConfig)->toArray();
	}

    /**
	  * Delete $project record and return response
	  *
	  * @param  array $inputData
	  *
	  * @return  mixed
	  *---------------------------------------------------------------- */

	public function deleteProject($project)
	{   
	    // Check if $project deleted
	    if ($project->delete()) {

	        return true;
	    }

	    return false;
	}

	/**
	  * Store new project record and return response
	  *
	  * @param  array $inputData
	  *
	  * @return  mixed
	  *---------------------------------------------------------------- */

	public function storeProject($inputData)
	{   
        $keyValues = [
            'name',
            'slug',
            'short_description',
            'status',
            'type',
            'languages__id' => $inputData['primary_language'],
            'logo_image' => isset($inputData['logo_image']) ? $inputData['logo_image'] : null,
            'favicon_image' => configItem('faviconName'),
            'user_authorities__id' => getUserAuthorityId(),
            '__data'
        ];

	    $newProject = new ProjectModel;
	    
	    // Check if task testing record added then return positive response
	    if ($newProject->assignInputsAndSave($inputData, $keyValues)) {

	        return $newProject;
	    }

	    return false;
	}

    /**
  	  * Update project record and return response
  	  *
  	  * @param  object $project
  	  * @param  array $inputData
  	  *
  	  * @return  mixed
  	  *---------------------------------------------------------------- */

	public function updateProject($project, $inputData)
	{       
    	// Check if project updated then return positive response
    	if ($project->modelUpdate($inputData)) {

        	return true;
    	}

    	return false;
    }


    /**
    * Fetch the record of Project
    *
    * @param  int || string $idOrUid
    *
    * @return  eloquent collection object
    *---------------------------------------------------------------- */

    public function fetchProjectWithPrimaryVersion()
    {   
        return ProjectModel::where('status', 1)->where(function($query) {
                    if (isLoggedIn()) {
                        $query->whereIn('type', [1, 2]);
                    } else {
                       $query->where('type', 1);  
                    }                    
                })->with(['primaryVersion' => function($q) {
                    $q->select('_id', '_uid', 'version', 'slug', 'mark_as_primary', 'projects__id');
                }])
                ->with('latestVersion')
                ->select('_id', '_uid', 'logo_image', 'name', 'slug', 'short_description', 'languages__id', 'type')
                ->get();
    }
    
    /**
     * Fetch primary lang of project
     * @return eloquent collection object
     */
    public function fetchPrimaryLanguage($projectSlug)
    {
        $projectQuery = new ProjectModel;
        if (isFromEmbedView($projectSlug)) {
            return ProjectModel::where([
                            '_uid' => $projectSlug
                        ])
                        ->where(function($query) {
                            if (!isLoggedIn()) {
                                $query->where('status', 1);
                            }                  
                        })
                        ->pluck('languages__id')->first();
        } else {
            return ProjectModel::where([
                            'slug' => $projectSlug
                        ])
                        ->where(function($query) {
                            if (!isLoggedIn()) {
                                $query->where('status', 1);
                            }                  
                        })
                        ->pluck('languages__id')->first();
        }
    }

    /**
     * Fetch version of project
     * @return eloquent collection object
     */
    public function fetchVersionOfProject($projectId)
    {
        $version = VersionModel::where([
                                    'projects__id'    => $projectId,
                                    'status'          => 1,
                                    'mark_as_primary' => 1
                                ])
                                ->select('_id', '_uid', 'slug', 'version', 'mark_as_primary')
                                ->first();

        if (__isEmpty($version)) {
            $version = VersionModel::where([
                                'projects__id'    => $projectId,
                                'status'          => 1
                            ])
                            ->latest()
                            ->select('_id', 'slug', 'version', 'mark_as_primary')
                            ->first();
        }

        return $version;
    }

    /**
    * Project DOc view Data
    *
    * @param  string $projectSlug
    * @param  string $versionSlug
    * @param  string $articleSlug
    *
    * @return  mixed
    *---------------------------------------------------------------- */

	public function fetchBySlugDocData($projectSlug, $versionSlug = null, $articleIds = null, $lang = null)
	{   
        return ProjectModel::with([
                'versions' => function($vq) use($versionSlug, $lang, $articleIds) {

                    if (!__isEmpty($versionSlug)) {
                        if (isFromEmbedView($versionSlug)) {
                            $vq->where('_uid', $versionSlug);
                        } else {
                            $vq->where('slug', $versionSlug);
                        }
                    }

                    // Article relationship
                    return $vq->where('status', 1)->with([
                        'articles' => function($articleQuery) use($lang, $articleIds)  {
                            
                            if (!__isEmpty($articleIds)) {
                                return $articleQuery->whereIn('_id', $articleIds);
                            }

                            //check can this article data access via edit article permission
                            if (!canThisArticleAccess()) {
                               return $articleQuery->where('status', 1);
                            }

                            // article content
                            return $articleQuery->orderBy('list_order')->with([
                                'contents' => function($acq) use($lang) {

                                    return $acq->where(['status' => 1, 'languages__id' => $lang]);
                                }
                            ]);

                        }
                    ]);
                }
            ])
            ->where(function($projectQuery) use($projectSlug) {
                if (isFromEmbedView($projectSlug)) {
                    $projectQuery->where('_uid', $projectSlug);
                } else {
                    $projectQuery->where('slug', $projectSlug);
                }
            })
            ->where(function($query) {
                if (!isLoggedIn()) {
                    $query->where('status', 1);
                }                  
            })
            ->first();
	}

	/**
     * Fetch latest project by user
     * @return eloquent collection object
     */
    public function latestProjectsByUser($userAuthorityId)
    {
    	return ProjectModel::where('user_authorities__id', '=', $userAuthorityId)
    						->orderBy('created_at', 'DESC')->first();
    }

    /**
     * Fetch latest project by language
     * @return eloquent collection object
     */
    public function fetchProjectsbyLanguage($language)
    {
    	return ProjectModel::where('languages__id', '=', $language)->get();
    }
	
    /**
     * Fetch latest project by slug
     * @return eloquent collection object
     */
    public function fetchProjectsbySlug($slug)
    {
        if (isFromEmbedView($slug)) {
            return ProjectModel::where('_uid', $slug)->first();
        } else {
            return ProjectModel::where('slug', $slug)->first();
        }
    }

    /**
     * Udpate Project model
     *
     * @return eloquent collection object
     */
    public function updateProjectModel($projectModel)
    {
        return $projectModel->touch();
    }
}