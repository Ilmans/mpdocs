<?php
/*
* VersionEngine.php - Main component file
*
* This file is part of the Version component.
*-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\Version;

use App\Yantrana\Base\BaseEngine;
use App\Yantrana\Components\Version\Interfaces\VersionEngineInterface;
 
use App\Yantrana\Components\Version\Repositories\VersionRepository;
use App\Yantrana\Components\Project\Repositories\ProjectRepository;
use App\Yantrana\Components\Article\Repositories\ArticleRepository;
use App\Yantrana\Components\Article\ArticleEngine;
use PDF;
use File;

class VersionEngine extends BaseEngine implements VersionEngineInterface 
{   
     
    /**
     * @var  VersionRepository $versionRepository - Version Repository
     */
    protected $versionRepository;

    /**
     * @var  ProjectRepository $projectRepository - Project Repository
     */
    protected $projectRepository;
    
    /**
     * @var  ArticleRepository $articleRepository - Article Repository
     */
    protected $articleRepository;

    /**
     * @var  ArticleEngine $articleEngine - Article Engine
     */
    protected $articleEngine;

    /**
      * Constructor
      *
      * @param  VersionRepository $versionRepository - Version Repository
      * @param  ArticleRepository $articleRepository - Article Repository
      * @param  ArticleEngine $articleEngine - Article Engine
      *
      * @return  void
      *-----------------------------------------------------------------------*/

    function __construct(
        VersionRepository $versionRepository,
        ProjectRepository $projectRepository,
        ArticleRepository $articleRepository,
    	ArticleEngine $articleEngine)
    {   
        @ini_set('memory_limit', '-1');
        @ini_set('max_execution_time', '-1');
        $this->versionRepository = $versionRepository;
        $this->projectRepository = $projectRepository;
        $this->articleRepository = $articleRepository;
        $this->articleEngine = $articleEngine;
    }

    private function versionSlug($title, $separator = '-')
    {
        // Convert all dashes/underscores into separator
        $flip = $separator == '-' ? '_' : '-';

        $title = preg_replace('!['.preg_quote($flip).']+!u', $separator, $title);
        // Replace all separator characters and whitespace by a single separator
        $title = preg_replace('!['.preg_quote($separator).'\s]+!u', $separator, $title);

        return trim($title, $separator);
    }

    /**
  	* Version datatable source 
  	*
  	* @return  array
  	*---------------------------------------------------------------- */
    public function prepareProjectInfo($projectIdOrUid)
    {   
        $project = $this->projectRepository->fetch($projectIdOrUid);

        if (__isEmpty($project)) {

            return $this->engineReaction(18, null, 'Project not exists.');
        }

        return $this->engineReaction(1, [
            'info' => [
                '_id'  => $project->_id,
                '_uid' => $project->_uid,
                'name' => $project->name
            ]
        ]);
    }

  /**
  	* Version datatable source 
  	*
  	* @return  array
  	*---------------------------------------------------------------- */
    public function prepareVersionList($projectIdOrUid)
    {   
        $project = $this->projectRepository->fetch($projectIdOrUid);

        if (__isEmpty($project)) {

            return $this->engineReaction(18, null, 'Project not exists.');
        }

        $versionData = [];
    
        $versionCollection = $this->versionRepository->fetchProjectVersions($project->_id);
        
        // Check if $versionCollection is exist
        if (!__isEmpty($versionCollection)) {

            foreach($versionCollection as $version) {

                $versionData[] = [
                    '_uid'       => $version->_uid,
                    'version'    => $version->version,
                    'created_at' => formatDateTime($version->created_at),
                    'updated_at' => formatDateTime($version->updated_at),
                    'status' => $version->status,
                    'f_status' => techItemString($version->status),
                    'is_primary' => ($version->mark_as_primary == 1) ? 1 : 2,
                    'download_url' => route('manage.project.version.document_download_pdf', [
                        'projectIdOrUid' => $project->_uid,
                        'versionUid' => $version->_uid
                    ]),
                    'detailUrl' => route('doc.view', [
                        'projectSlug' => $project->slug,
                    	'versionSlug' => $version->slug                    	
                    ]),
                    'canManageArticles' => canAccess('manage.article.read.list'),
                    'canEdit' => canAccess('manage.project.version.write.update'),
                    'canDelete' => canAccess('manage.project.version.write.delete'),
                    'canDownload' => canAccess('manage.project.version.document_download_pdf'),
                    'projectSlug' => $project->_uid,
                    'versionSlug' => $version->_uid,
                    'canGeneratePdf' => configItem('enable_pdf_generation')
                ];
            }
        }
        
        return $this->engineReaction(1, [
            'versionList' => $versionData,
        ]);
    }


    /**
    * Version delete process 
    *
    * @param  mix $projectIdOrUid
    *
    * @return  array
    *---------------------------------------------------------------- */

    public function processVersionDelete($projectIdOrUid, $versionUid)
    {   
        $project = $this->projectRepository->fetch($projectIdOrUid);

        if (__isEmpty($project)) {

            return $this->engineReaction(18, null, 'Project not exists.');
        }

        $version = $this->versionRepository->fetch($versionUid);

        if (__isEmpty($version)) {
            return $this->engineReaction(18, null, __tr('Version not found.'));
        }

        if ($this->versionRepository->delete($version)) {

        	activityLog(16, $version->_id, 3);

            return $this->engineReaction(1, null, __tr('Version deleted.'));
        }

        return $this->engineReaction(2, null, __tr('Version not deleted.'));
    }

    /**
    * Version Add Support Data
    *
    * @return  array
    *---------------------------------------------------------------- */

    public function prepareVersionSupportData($projectIdOrUid)
    {
    	$project = $this->projectRepository->fetch($projectIdOrUid);

        if (__isEmpty($project)) {

            return $this->engineReaction(18, null, 'Project not exists.');
        }

        $allVersions = $this->versionRepository->fetchProjectVersions($project->_id);

        $projectVersions = [];
        //check if versions  not empty
        if (!__isEmpty($allVersions)) {
        	foreach ($allVersions as $key => $version) {
        		$projectVersions[] = [
        			'id' => $version->_id,
        			'version' => $version->version
        		];
        	}
        }

        return $this->engineReaction(1, [
            'existing_versions' => $projectVersions,
            'existing_versions_count' => count($projectVersions),
            'projectId' => $project->_id
        ]);
    }
    /**
    * Version create 
    *
    * @param  array $inputData
    *
    * @return  array
    *---------------------------------------------------------------- */

    public function processVersionCreate($projectIdOrUid, $inputData)
    {
    	$transactionResponse = $this->articleRepository->processTransaction(function() use ($projectIdOrUid, $inputData) {

    		$project = $this->projectRepository->fetch($projectIdOrUid);

	        if (__isEmpty($project)) {

	            return $this->articleRepository->transactionResponse(18, null, 'Project not exists.');
	        }

	        $inputData['projects__id'] = $project->_id;

	        if (isset($inputData['is_primary']) and ($inputData['is_primary'] == '1')) {
	        	$inputData['mark_as_primary'] = 1;
	        } else {
	        	$inputData['mark_as_primary'] = null;
	        }
            
	        $allVersions = $this->versionRepository->fetchProjectVersions($project->_id);

	        if ($allVersions->count() == 0) {
	        	$inputData['mark_as_primary'] = 1;
	        }

            $inputData['version'] = $inputData['version'];

	        if ($newVersion = $this->versionRepository->store($inputData)) {

	        	if ($newVersion->mark_as_primary == 1) {
	        		$this->versionRepository->updateVersionPrimaryStatus($newVersion->_id, $project->_id);
	        	}

	        	/* if version needs to copy content from existing version */
	        	if (isset($inputData['copy_of_version']) && !__isEmpty($inputData['copy_of_version'])) {

	        		$existingVersion = $this->versionRepository->fetch((int)$inputData['copy_of_version']);

			        if (__isEmpty($existingVersion)) {
			            return $this->articleRepository->transactionResponse(18, null, __tr('Version not found.'));
			        }

	        		$projectArticles = $this->articleRepository->fetchProjectArticlesByVersion($project->_id, $existingVersion->_id);

					$articleAdded = $this->copyArticle($projectArticles, $project->_id, $newVersion->_id);

					if ($articleAdded) {
						return $this->articleRepository->transactionResponse(1, null, __tr('Version added and Content Copied.'));
					}
	        	}
                $this->projectRepository->updateProjectModel($project);
	            return $this->articleRepository->transactionResponse(1, null, __tr('Version added.'));
	        }

	        return $this->articleRepository->transactionResponse(2, null, __tr('Version not added.'));

    	});
        
        return $this->engineReaction($transactionResponse);
    }


    public function copyArticle($projectArticles, $projectId, $versionId, $newArticleId = null)
    {
    	$articleAdded = false;
    	if (!__isEmpty($projectArticles)) {

			foreach ($projectArticles as $key => $article) {

				$storeData = [
	                'projects__id' 	=> $projectId,
	                'published_at'  => now(),
	                'status' 		=> $article->status,
	                'previous_articles__id' => $newArticleId,
		            'languages__id' =>  $article->languages__id,
		            'user_authorities__id' => getUserAuthorityId(),
		            'compilation_type' 	=> 1,
	                'type' => $article->type,
	                'doc_versions__id' => $versionId,
	                'slug' => $article->slug,
                    'list_order' => $article->list_order,
	                '__data' => $article->__data,
		        ];

		        $newArticle = [];

			    if ($newArticle = $this->articleRepository->storeArticle($storeData)) {

	                // Article Activity 
	                activityLog(7, $newArticle->_id, 9);

			    	if (!__isEmpty($article->contents)) {

	                    foreach ($article->contents as $key => $content) 
	                    {   
	                        // Store Article content with history
	                        $this->articleEngine->prepareStoreContent([
	                            'title'         => $content->title,
	                            'description'   => $content->description,
	                            'languages__id'  => $content->languages__id,
	                            'status'        => $content->status,
	                            'articles__id'  => $newArticle->_id,
	                        ]);

	                    }
			    	}
	            }

	            if (!__isEmpty($article->subArticles)) {
	            	$this->copyArticle($article->subArticles, $projectId, $versionId, $newArticle->_id);
	            }

			}
		}
    }
   
    /**
    * Version prepare update data 
    *
    * @param  mix $projectIdOrUid
    *
    * @return  array
    *---------------------------------------------------------------- */

    public function prepareVersionUpdateData($projectIdOrUid, $versionUid)
    {   
        $project = $this->projectRepository->fetch($projectIdOrUid);

        if (__isEmpty($project)) {

            return $this->engineReaction(18, null, 'Project not exists.');
        }

        $version = $this->versionRepository->fetch($versionUid);

        if (__isEmpty($version)) {
            return $this->engineReaction(18, null, __tr('Version not found.'));
        }

        return $this->engineReaction(1, [
            'versionData' => [
                '_id'  => $version->_id,
                '_uid' => $version->_uid,
                'version'  => $version->version,
                'slug'  => $version->slug,
                'status'  => $version->status,
                'is_primary'  => $version->mark_as_primary,
                'projects__id'  => $version->projects__id
            ],
            'project_info' => [ 'name' => $project->name ]
        ]);
    }
  
    /**
    * Version process update 
    * 
    * @param  mix $projectIdOrUid
    * @param  array $inputData
    *
    * @return  array
    *---------------------------------------------------------------- */

    public function processVersionUpdate($projectIdOrUid, $versionUid, $inputData)
    {   
        $project = $this->projectRepository->fetch($projectIdOrUid);
        
        if (__isEmpty($project)) {

            return $this->engineReaction(18, null, 'Project not exists.');
        }

        $version = $this->versionRepository->fetch($versionUid);

        if (__isEmpty($version)) {
            return $this->engineReaction(18, null, __tr('Version not found.'));
        }
        
        $updateData = [
            'version' => $inputData['version'],
            'slug' => $inputData['slug'],
            'status' => $inputData['status'],
        ];

        if (isset($inputData['is_primary']) and ($inputData['is_primary'] == '1')) {
        	$updateData['mark_as_primary'] = 1;
        } else {
        	$updateData['mark_as_primary'] = null;
        }

        // Check if Version updated
        if ($updatedversion = $this->versionRepository->update($version,  $updateData)) {
            // Update project updated at
            $this->projectRepository->updateProjectModel($project);
            
			if ($updatedversion->mark_as_primary == 1) {
	    		$this->versionRepository->updateVersionPrimaryStatus($updatedversion->_id, $project->_id);
	    	}

            return $this->engineReaction(1, null, __tr('Version updated.'));
        }

        return $this->engineReaction(14, null, __tr('Version not updated.'));
    }

    private function buildTree($pages, $prevId = null) 
    {   
        if (__isEmpty($pages)) {
            return [];
        }
 
        $collectData = [];
        $count       = 0;

        foreach($pages as $page) 
        {
            if ($page['previous_articles__id'] === $prevId) {

                if (!__isEmpty($page['contents'])) {
                    
                    $contentData = [];

                    foreach ($page['contents'] as $key => $content) 
                    {
                        array_push($collectData, [
                            'article_id'            => $page['_id'],
                            'article_uid'           => $page['_uid'],
                            'article_status'        => $page['status'],
                            'projects__id'          => $page['projects__id'],
                            'previous_articles__id' => $page['_id'],
                            // 'primary_languages_id'  => $page['languages__id'],
                            'slug'                  => $page['slug'],
                            'title'                 => $content['title'],
                            'description'           => $content['description'],
                            'languages__id'         => $content['languages__id']
                        ]);
                    }
                }

                $subArticles = $this->buildTree($pages, $page['_id']);

                if (!__isEmpty($subArticles)) {
                    $collectData[$count]['sub_articles'] = $subArticles;
                }
                
                $count++;

            }

        }

        return $collectData;
    }

    /**
    * Version process update 
    * 
    * @param  mix $projectIdOrUid
    * @param  array $inputData
    *
    * @return  array
    *---------------------------------------------------------------- */

    public function processDownloadDocumentPdf($projectIdOrUid, $versionUid)
    {   
        $project = $this->projectRepository->fetch($projectIdOrUid);

        if (__isEmpty($project)) {

            abort(404);
        }

        $version = $this->versionRepository->fetch($versionUid);

        if (__isEmpty($version)) {
            abort(404);
        }

        $articles = $this->articleRepository->fetchArticlesOfVersion($version->_id, $project->languages__id);

        if (__isEmpty($articles)) {
            abort(404);
        }

        $project = $project->toArray();

        $project['logo_url'] = mediaUrl('project_uploads', ['{_uid}' => $project['_uid']])."/".$project['logo_image'];
        $projectLogoPath = mediaStorage('project_uploads', ['{_uid}' => $project['_uid']])."/".$project['logo_image'];

        // download pdf
        $document = PDF::loadView('version.doc-pdf-view', [
            'articles' => buildTree($articles->toArray()),
            'project'  => $project,
            'logoFileExists' => file_exists($projectLogoPath),
            'version'  => $version->toArray()
        ]);

      //   return $document->stream();
        
        return $document->download($this->versionSlug($project['slug'].'-'.$version['slug']).'.pdf');

    }
}