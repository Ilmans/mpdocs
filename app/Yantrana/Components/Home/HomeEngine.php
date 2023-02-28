<?php
/*
* HomeEngine.php - Main component file
*
* This file is part of the Home component.
*-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\Home;

use App\Yantrana\Base\BaseEngine;
use App\Yantrana\Components\Article\Models\ArticleModel;
use App\Yantrana\Components\Home\Interfaces\HomeEngineInterface;
use App\Yantrana\Components\Project\Repositories\ProjectRepository;
use App\Yantrana\Components\Language\Repositories\LanguageRepository;
use App\Yantrana\Components\Version\Repositories\VersionRepository;

use App\Yantrana\Components\Article\Repositories\{
    ArticleRepository
};
use Illuminate\Support\Facades\Auth;

class HomeEngine extends BaseEngine implements HomeEngineInterface 
{   
    
    /**
     * @var  ProjectRepository $projectRepository - Project Repository
     */
    protected $projectRepository;

    /**
     * @var  ArticleRepository $articleRepository - Article Repository
     */
    protected $articleRepository;

    /**
     * @var  VersionRepository $versionRepository - Version Repository
     */
    protected $versionRepository;

    /**
    * Constructor
    *
    * @param  ProjectRepository $projectRepository - Project Repository
    * @param  ArticleRepository $articleRepository - Article Repository
    *
    * @return  void
    *-----------------------------------------------------------------------*/

    function __construct(
        ProjectRepository $projectRepository,
        ArticleRepository $articleRepository,
        LanguageRepository $languageRepository,
        VersionRepository $versionRepository
    ) {
        
        $this->projectRepository = $projectRepository;
        $this->articleRepository = $articleRepository;
        $this->languageRepository = $languageRepository;
        $this->versionRepository = $versionRepository;
    }

    /**
     * Get project logo url
     * @return  string
     */
    private function getProjectLogo($uid, $filename)
    {   
        if (\File::exists(mediaStorage('project_uploads', ['{_uid}' => $uid ]).'/'.$filename)) {

            return mediaUrl('project_uploads', ['{_uid}' => $uid])."/".$filename;
        }

        return false;
    }

    /**
     *  Prepare Get Tags
     *  @param 
     *  @return 
     */
    public function prepareLatestArticles() 
    {
        return $this->articleRepository->fetchPrimaryLatestArticles(10);
    }


    /**
  	* Prepare Data for home page 
  	*
  	* @return  array
  	*---------------------------------------------------------------- */

	public function prepareData()
	{   
        $projects = $this->projectRepository->fetchProjectWithPrimaryVersion(1);
         
        $projectData = [];
        foreach ($projects as $key => $project) {

            $longUrl = __isEmpty($project->logo_image) ? '' : $this->getProjectLogo(
                $project->_uid,
                $project->logo_image
            );

            $versionData = [];
            if (!__isEmpty($project->primaryVersion)) {
                $versionData = $project->primaryVersion->toArray();
            } else if(__isEmpty($project->primaryVersion) and !__isEmpty($project->latestVersion)) {
                $versionData = $project->latestVersion->toArray();
            }

            $projectData[] = [
                "_id"           => $project->_id,
                "_uid"          => $project->_uid,
                "logo_image"    => $project->logo_image,
                "name"          => $project->name,
                "slug"          => $project->slug,
                "is_private"          => $project->type === 2, //  private
                "short_description" =>$project->short_description,
                "languages__id"     => $project->languages__id,
                "logo_url"          => $longUrl,
                "primary_version"   => $versionData
            ];
        }
        
        return [
            'projects' => array_chunk($projectData, 3)
        ];
    }


    
    /**
  	* Prepare search data
  	*
  	* @return  array
  	*---------------------------------------------------------------- */

	public function prepareSearchData($searchTerm = null, $language = null, $version = null, $project = null)
	{
        $projectData = $this->projectRepository->fetchProjectsbySlug($project);

        $projectId = null;
        if (!__isEmpty($projectData)) {
            $projectId = $projectData->_id;
        }
        $versionId = null;
        $versionData = $this->versionRepository->fetchVersionBySlug($version, $projectId);
        // check if version exists
        if (!__isEmpty($versionData)) {
            $versionId = $versionData->_id;
        }
        // Fetch project and version related article
        $articleCollection = $this->articleRepository->fetchArticleForSearchData($projectId, $versionId);

        $articleIds = $articleCollection->pluck('_id')->toArray();

        $searchData = $this->articleRepository->fetchSearchData($searchTerm, $language, $version, $articleIds);
       
        $parentArticleData = [];
        if (!__isEmpty($searchData)) {
            foreach ($searchData as $searchDataValue) {
                $parentArticle = last(findParents($articleCollection, $searchDataValue->_id));
                $parentArticleData[$searchDataValue->_id] = array_get($parentArticle, 'slug');
            }
            if (!__isEmpty($parentArticleData)) {
                foreach ($searchData as $searchKey => $searchValue) {
                    if (array_key_exists($searchValue->_id, $parentArticleData)) {
                        $searchData[$searchKey]['parentArticleSlug'] = $parentArticleData[$searchValue->_id];
                        $searchData[$searchKey]['searchContentRoute'] = route('doc.view', [
                                                                        'projectSlug' => $project,
                                                                        'versionSlug' => $version,
                                                                        'articleSlug' => $parentArticleData[$searchValue->_id]
                                                                    ]);
                    }
                }
            }
        }
        
        return json_encode(array(
            "status" => true,
            "error"  => null,
            "data"   => array(
                "searchData" => $searchData
            )
        ));
    }

    protected function findChilds($itemCollection, $itemID = null, $activeItemsContainer = [])
    {
        $itemID = (int) $itemID;

        foreach ($itemCollection as $item) {
            if (($item->_id === $itemID)
                and in_array($itemID, $activeItemsContainer) !== true) {
                $activeItemsContainer[] = $itemID;
            }

            if ($item->previous_articles__id == (int) $itemID) {
                $activeItemsContainer[] = $item->_id;
                $activeItemsContainer[] = $this->findChilds($itemCollection, $item->_id, $activeItemsContainer);
            }
        }

        return array_values(array_unique(array_flatten($activeItemsContainer)));
    }

    /**
  	* Prepare Data for doc view
  	*
  	* @return  array 
  	*---------------------------------------------------------------- */

	public function prepareDocData($projectSlug, $versionSlug = null, $articleSlug = null, $lang = null)
	{   
        // Fetch project by project id
        $projectData = $this->projectRepository->fetchProjectsbySlug($projectSlug);
        // Check if project exist
        if (__isEmpty($projectData)) {
            abort(404);
        }
        // Prepare Project Favicon and Logo URL
        $logoUrl = $this->getProjectLogo($projectData->_uid, $projectData->logo_image);
        $faviconUrl = $this->getProjectLogo($projectData->_uid, $projectData->favicon_image);

        // Check if project is private or not
        if ($projectData->type == 2) {
            if (!isLoggedIn()) {
                abort(404);
            }
        }
      
        $projectId = $projectData->_id;
        // Get Project Languages
        $currentProjectLanguages = array_get($projectData['__data'], 'project_languages');
        // Assign language to primary language
        $primaryLanguage = $lang;
        // Check if primary language exist
        if (__isEmpty($lang) or !in_array($lang, $currentProjectLanguages)) {
            // Fetch Primary Language data
            $primaryLanguage = $this->projectRepository->fetchPrimaryLanguage($projectSlug);
        }
        // Declare version id with null value
        $versionId = null;
        // Fetch Version Data
        $versionData = $this->projectRepository->fetchVersionOfProject($projectId);

        // Check if version exist
        if (__isEmpty($versionData)) {
            // App: abort(404);
            return [
                'lang'                  => $primaryLanguage,
                'projectName'           => $projectData->name,
                'projectStatus'         => $projectData->status,
                'logoUrl'               => $logoUrl,
                'faviconUrl'            => $faviconUrl,
                'projectSlug'           => $projectSlug,
                'projectVersions'       => []
            ];
        }

        $versionId = $versionData->_id;
        $newVersionSlug = null;
        $articleVersionSLug = '';
        // Check if version slug exist
        if (__isEmpty($versionSlug)) {
            $versionSlug = $versionData->slug;
            $newVersionSlug = $versionSlug;
            $articleVersionSLug = $versionData->version;
            if (REQUEST_FROM_EMBED_VIEW) {
                $newVersionSlug = $versionData->_uid;
            }
        } else {
            $versionSlugData = $this->versionRepository->fetchVersionBySlug($versionSlug, $projectId);
            if (__isEmpty($versionSlugData)) {
                abort(404);
            }
            $articleVersionSLug = $versionSlugData->version;
            $versionId = $versionSlugData->_id;
            $newVersionSlug = $versionSlug;
            if (REQUEST_FROM_EMBED_VIEW) {
                $newVersionSlug = $versionSlugData->_uid;
            }
        }
    
        // Check if article slug exist
        if (__isEmpty($articleSlug)) {
            // Fetch Primary Article Data
            $primaryArticle = $this->articleRepository->fetchArticlesByVersionId($versionId);
            // Check if article exist
            if (!__isEmpty($primaryArticle)) {
                $articleSlug = $primaryArticle->slug;
            }            
        }
           $singleArticle = ArticleModel::whereSlug($articleSlug)->first();
     
     


        $projectVersions = [];
        $versionCollections = $this->versionRepository->fetchProjectVersionsWithArticle($projectId);
        
        // Check if project versions exist
        if (!__isEmpty($versionCollections)) {
            foreach ($versionCollections as $projectVersion) {
                $projectVersions[] = [
                    'slug'      => $projectVersion->slug,
                    'version'   => $projectVersion->version,
                    'status'   	=> $projectVersion->status,
                    'article_slug'      => array_get($projectVersion['articles'], '0.slug'),
                ];
            }
        }

        $articleId = null;
        // Fetch Article By Article Slug
        $articleDetails = $this->articleRepository->fetchAllChildByArticleSlug($articleSlug, $versionId);
       

        // Check if article exist
        if (!__isEmpty($articleDetails)) {
            $articleId = $articleDetails->_id;
        } else {
            // App: abort(404);
            return [
                'lang'                  => $primaryLanguage,
                'projectName'           => $projectData->name,
                'projectStatus'         => $projectData->status,
                'logoUrl'               => $logoUrl,
                'faviconUrl'            => $faviconUrl,
                'projectSlug'           => $projectSlug,
                'projectVersions'       => $projectVersions,
                'allArticleContents'    => []
            ];
        }
        
        // Create blank array for article ids
        $articleIds = [];
        // Fetch current project and version related all articles
        $articleCollection = $this->articleRepository->fetchArticleByProjectAndVersionId($projectId, $versionId, $primaryLanguage);
        
        // Find all children of current article
        $articleIds = $this->findChilds($articleCollection, $articleId);

        // Fetch only one Articles and its child with content
        $project = $this->projectRepository->fetchBySlugDocData($projectSlug, $newVersionSlug, $articleIds, $primaryLanguage);
        // Check if project exists
        if (__isEmpty($project)) {
            abort(404);
        }

        // GeconUoject languages
        $projectLanguages = $this->languageRepository->fetch($project['__data']['project_languages'])->pluck('name', '_id')->toArray();
        // IF any rtl language is there then fetch it
        $rtlLanguages = $this->languageRepository->fetchRTL()->pluck('name', '_id')->toArray();
        // Prepare Article Content Data
        $allArticleContents = $this->buildTree($articleCollection);
        
        // Declare project versions array
        $articleContents = [];
        if (!__isEmpty($project['versions'])) {
            foreach($project['versions'] as $key => $version) {  
                // Check if article exists
                if (!__isEmpty($version['articles'])) {
                    $articleContents = $this->buildTree($version['articles']);
                   
                }                
            }
		}
		
       
        // if type article is = 2, then check if user is logged in or not
  
     
        return [
            'lang'                  => $primaryLanguage,
            'rtlLanguages'          => $rtlLanguages,
            'projectName'           => $project->name,
            'projectStatus'         => $project->status,
            'logoUrl'               => $logoUrl,
            'faviconUrl'            => $faviconUrl,
            'projectSlug'           => $projectSlug,
            'versionSlug'           => $versionSlug,
            'articleSlug'           => $articleSlug,
            'projectVersions'       => $projectVersions,
            'projectLanguages'      => $projectLanguages,
            'allArticleContents'    => $allArticleContents,
            'articleContents'       => $articleContents,
            'articleVersionSLug'    => $articleVersionSLug
        ];
	}
	
	/**
    * Build Tree
    * @return array
    */
    private function buildTree($pages, $prevId = null, $parentSlug = '', $depthArray = []) 
    {   
        if (__isEmpty($pages)) {
            return [];
        }
 
        $collectData = [];
     

        foreach($pages as $page) 
        {   
            if (!__isEmpty($page['contents'])) {

              
                if ($page['previous_articles__id'] === $prevId) {
					$uniqueContentId = null;
					$firstContentUid = null;
					foreach ($page['contents'] as $key => $content) 
					{
						if (__isEmpty($firstContentUid)) {
							$firstContentUid = $content['_uid'];
						}
						$isParent = false;                            
						if (__isEmpty($page['previous_articles__id'])) {
							$isParent = true;
							$parentSlug = $page['slug'];
							$depthArray[$page['_id']] =  0;
						} else {
							$depthArray[$page['_id']] =  array_get($depthArray, $page['previous_articles__id'], 0) + 1;
						}

						$uniqueContentId = $content['_uid'];
						  // if user not logged in , and article type is 2 , fill $content['description'] with text "please login to view this content"
                                  

						$collectData[$uniqueContentId] = [
							'article_id'            => $page['_id'],
							'article_uid'           => $page['_uid'],
							'article_status'        => $page['status'],
                            'article_type'          => $page['type'],
							'projects__id'          => $page['projects__id'],
							'previous_articles__id' => $page['previous_articles__id'],
							'slug'                  => $page['slug'].'-'.$content['languages__id'],
							'title'                 => $content['title'],
                          
							'description'           => $content['description'],
							'languages__id'         => $content['languages__id'],
							'isParent'              => $isParent,
							'parentSlug'            => $parentSlug,
							'depth'            		=> array_get($depthArray, $page['_id'], 0),
						];
					}
                    
                    $subArticles = $this->buildTree($pages, $page['_id'], $parentSlug, $depthArray);
                    
                    if (!__isEmpty($subArticles)) {
                        $collectData[$firstContentUid]['sub_articles'] = $subArticles;
                    }
                  
                }
            }
        }

      
        return $collectData;
    }

    /**
    * Prepare Article Contents Index
    *
    * @param array $articleContent
    *
    * @return  array 
    *---------------------------------------------------------------- */

    public function prepareArticleContentIndex($articleContents, $lang, $articleContentIndex = '')
    {
        if (__isEmpty($articleContents)) {
            return $articleContents;
        }

        foreach ($articleContents as $articleContent) {
            if ($articleContent['languages__id'] == $lang) {
                $articleContentIndex .= $articleContent['description'];
                if (isset($articleContent['sub_articles']) and 
                    is_array($articleContent['sub_articles'])) {
                    $articleContentIndex .= $this->prepareArticleContentIndex($articleContent['sub_articles'], $lang, $articleContentIndex);
                }
            }
        }       
        return $articleContentIndex;
    }
}