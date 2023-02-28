<?php
/*
* ArticleRepository.php - Repository file
*
* This file is part of the Article component.
*-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\Article\Repositories;

use App\Yantrana\Base\BaseRepository;
 
use App\Yantrana\Components\Article\Models\ArticleModel;
use App\Yantrana\Components\Article\Models\ArticleContentModel;
use App\Yantrana\Components\Article\Interfaces\ArticleRepositoryInterface;
use DB;

class ArticleRepository extends BaseRepository
                          implements ArticleRepositoryInterface 
{ 
    
    /**
      * Fetch the record of Article
      *
      * @param    int || string $idOrUid
      *
      * @return    eloquent collection object
      *---------------------------------------------------------------- */

    public function fetch($idOrUid, $status = null)
    {   
        $query = ArticleModel::query();

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
    * Fetch the record of Article Contents
    *
    * @param  int $id
    *
    * @return eloquent collection object
    *---------------------------------------------------------------- */

    public function fetchProjectArticls($projectId)
    {   
        return ArticleModel::where('projects__id', $projectId)
                            ->get();
    }


    /**
    * Fetch the record of Article
    *
    * @param    int || string $idOrUid
    *
    * @return    eloquent collection object
    *---------------------------------------------------------------- */

    public function fetchContent($idOrUid, $status = null)
    {   
        $query = ArticleContentModel::query();

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
    * Delete Articles by article ids
    *
    * @param array $articleIds
    *
    * @return eloquent collection object
    *---------------------------------------------------------------- */

    public function deleteArticlesContentByIds($articleContentIds)
    {
        return ArticleContentModel::whereIn('_id', $articleContentIds)->delete();
    }

    /**
    * Fetch the record of Article
    *
    * @param  int || string $idOrUid
    *
    * @return  eloquent collection object
    *---------------------------------------------------------------- */

    public function all($projectId = '', $articleId = '', $status = '')
    {   
        $projectId = (int) $projectId;
        $articleId = (int) $articleId;
        $status = (int) $status;

        return ArticleContentModel::leftJoin('articles', 'article_contents.articles__id', '=', 'articles._id')
                ->when($projectId !== 0, function($q) use($projectId) {
                    $q->where('projects__id', $projectId);
                })
                ->when($articleId !== 0, function($qu) use($articleId) {
                    $qu->where('articles._id', '!=', $articleId);
                })
                ->when($status !== 0, function($qu) use($status) {
                    $qu->where('articles.status', '=', $status);
                })
                ->when($projectId === 0, function($q) {
                    $q->where('projects__id', null);
                })
                ->where('article_contents.languages__id', '=', DB::raw('articles.languages__id'))
                ->select(
                    __nestedKeyValues([
                        'article_contents' => [
                            'articles__id AS _id',
                            'title'
                        ],
                        'articles' => [
                            'published_at'
                        ]
                    ])
                )
                ->get();
    }

    /**
    * Fetch Version Article Children
    *
    * @param  int || string $idOrUid
    *
    * @return  eloquent collection object
    *---------------------------------------------------------------- */

    public function fetchVersionArticleChildren($projectId, $versionId, $articleId = '')
    {
        $projectId = (int) $projectId;
        $versionId = (int) $versionId;

        return ArticleModel::with('children')
                            ->leftJoin('article_contents', 'articles._id', '=', 'article_contents.articles__id')
                            ->whereNull('articles.previous_articles__id')
                            //->groupBy('_id')
                            ->when($projectId !== 0, function($q) use($projectId) {
                                $q->where('projects__id', $projectId);
                            })
                            ->when($versionId !== 0, function($qu) use($versionId) {
                                $qu->where('articles.doc_versions__id', $versionId);
                            })
                            ->when($articleId !== 0, function($qu) use($articleId) {
                                $qu->where('articles._id', '!=', $articleId);
                            })
                            ->select(
                                __nestedKeyValues([
                                    'article_contents' => [
                                        'title'
                                    ],
                                    'articles' => [
                                        '_id',
                                        'published_at',
                                        'previous_articles__id',
                                        'slug'
                                    ]
                                ])
                            )
                            ->get();
    }

    /**
    * Fetch the record of Article
    *
    * @param  int || string $idOrUid
    *
    * @return  eloquent collection object
    *---------------------------------------------------------------- */

    public function fetchVerionArticles($projectId, $versionId, $articleId = '')
    {   
        $projectId = (int) $projectId;
        $versionId = (int) $versionId;

        return ArticleContentModel::join('articles', 'article_contents.articles__id', '=', 'articles._id')
                ->when($projectId !== 0, function($q) use($projectId) {
                    $q->where('projects__id', $projectId);
                })
                ->when($versionId !== 0, function($qu) use($versionId) {
                    $qu->where('articles.doc_versions__id', $versionId);
                })
                ->when($articleId !== 0, function($qu) use($articleId) {
                    $qu->where('articles._id', '!=', $articleId);
                })
                // ->where('article_contents.languages__id', '=', DB::raw('articles.languages__id'))
                ->select(
                    __nestedKeyValues([
                        'article_contents' => [
                            'articles__id AS _id',
                            'title'
                        ],
                        'articles' => [
                            'published_at'
                        ]
                    ])
                )
                ->get();
    }


    /**
    * Fetch the record of Article
    *
    * @param  int || string $idOrUid
    *
    * @return  eloquent collection object
    *---------------------------------------------------------------- */

    public function allPrimaryArticles($status = '', $projectId = '')
    {   
        $status = (int) $status;
        $projectId = (int) $projectId;

        return ArticleContentModel::orderBy('published_at', 'DESC')->leftJoin('articles', 'article_contents.articles__id', '=', 'articles._id')
                ->when($status !== 0, function($qu) use($status) {
                    $qu->where('articles.status', '=', $status);
                })
                ->when($projectId !== 0, function($qu) use($projectId) {
                    $qu->where('articles.projects__id', '=', $projectId);
                })
                ->where('article_contents.languages__id', '=', DB::raw('articles.languages__id'))
                ->select(
                    __nestedKeyValues([
                        'article_contents' => [
                            'title', 
                            '_id as artilce_content_id', '_uid as artilce_content_uid',
                            'description'
                        ],
                        'articles' => [
                            '_id', '_uid', 'published_at'
                        ]
                    ])
                )
                ->get();
    }


    /**
    * Fetch the record of Article
    *
    * @param  int || string $idOrUid
    *
    * @return  eloquent collection object
    *---------------------------------------------------------------- */

    public function fetchPrimaryLatestArticles($latest = 5)
    {   
        return ArticleContentModel::orderBy('published_at', 'DESC')->leftJoin('articles', 'article_contents.articles__id', '=', 'articles._id')
                ->where('article_contents.languages__id', '=', DB::raw('articles.languages__id'))
                ->where('article_contents.status', '=', 1)
                ->where('articles.status', '=', 1)
                ->take($latest)
                ->select(
                    __nestedKeyValues([
                        'articles' => ['published_at'],
                        'article_contents' => [
                            'title', '_uid as content_uid', 'description'
                        ]
                    ])
                )
                ->get();
    }


    /**
    * Fetch the record of Article
    *
    * @param  int || string $idOrUid
    *
    * @return  eloquent collection object
    *---------------------------------------------------------------- */

    public function allPrimaryArticlesWithPaginate($status = '')
    {   
        $status = (int) $status;

        return ArticleContentModel::orderBy('published_at', 'desc')->leftJoin('articles', 'article_contents.articles__id', '=', 'articles._id')
                ->when($status !== 0, function($qu) use($status) {
                    $qu->where('articles.status', '=', $status);
                })
                ->where('article_contents.languages__id', '=', DB::raw('articles.languages__id'))
                ->where('article_contents.status', '=', 1)
                ->where('articles.status', '=', 1)
                ->leftJoin('projects', 'projects._id', '=', 'articles.projects__id')
                ->select(
                    __nestedKeyValues([
                        'article_contents' => [
                            'title', 
                            '_id as artilce_content_id', '_uid as artilce_content_uid',
                            'description'
                        ],
                        'projects' => [
                            '_uid as project_uid',
                            'name as project_name'
                        ],
                        'articles' => [
                            '_id', '_uid', 'published_at', 'type'
                        ]
                    ])
                )
                ->simplePaginate(5);
    }
    

    /**
    * Fetch the record of Article Contents
    *
    * @param  int $id
    *
    * @return eloquent collection object
    *---------------------------------------------------------------- */

    public function fetchContents($id)
    {   
        return ArticleContentModel::orderBy('updated_at', 'DESC')->where('articles__id', $id)->get();
    }


	/**
      * Fetch projects article datatables source
      *
      * @return  mixed
      *---------------------------------------------------------------- */
 
	public function fetchArticles($projectId, $versionId, $language)
	{   
        return ArticleModel::with([
                            'content' => function($query) use($language) {
                                $query->where('article_contents.languages__id', $language)
                                    ->select(
                                        'articles__id',
                                        '_uid AS content_uid',
                                        'title',
                                        'status As content_status',
                                        'languages__id As content_language_id', 'updated_at'
                                    );
                            }
                        ])
                        ->orderBy('list_order')
                        ->where('projects__id', '=', $projectId)
                        ->where('doc_versions__id', '=', $versionId)
                        ->select('_id', '_uid', 'status', 'updated_at', 'previous_articles__id', 'published_at', 'list_order', 'slug')
                        ->get();
	}


	/**
      * Fetch the record of Article
      *
      * @param    int  $idOrUid
      *
      * @return    eloquent collection object
      *---------------------------------------------------------------- */

    public function fetchArticleWithContent($prevArticle)
    {   
        return ArticleContentModel::leftJoin('articles', 'article_contents.articles__id', '=', 'articles._id')
    				->where('article_contents.languages__id', '=', DB::raw('articles.languages__id'))
    				->where('articles._id', '=', $prevArticle)
    				->select(
    					__nestedKeyValues([
	    					'articles.*',
	    					'article_contents' => [
	    						'_uid AS content_uid',
	    						'title',
	    						'description',
	    						'languages__id As content_language_id'
	    					]
	    				])
    				)
                    ->first();
    }

    /**
	  * Delete $article record and return response
	  *
	  * @param  array $inputData
	  *
	  * @return  mixed
	  *---------------------------------------------------------------- */

	public function deleteArticle($article)
	{   
	    // Check if $article deleted
	    if ($article->delete()) {

	        return true;
	    }

	    return false;
	}

	/**
	  * Store new article record and return response
	  *
	  * @param  array $inputData
	  *
	  * @return  mixed
	  *---------------------------------------------------------------- */

	public function storeArticle($inputData)
	{
		$keyValues = [
            'projects__id' => !__isEmpty($inputData['projects__id']) ? $inputData['projects__id'] : null,
            'status',
            'user_authorities__id' => getUserAuthorityId(),
            'compilation_type',
            '__data',
            'previous_articles__id',
            'published_at', 
            'type',
            'doc_versions__id',
            'slug',
            'list_order'
        ];

	    $newArticle = new ArticleModel;
	    
	    // Check if task testing record added then return positive response
	    if ($newArticle->assignInputsAndSave($inputData, $keyValues)) {

	        return $newArticle;
	    }

	    return false;
	}

	/**
	  * Store new article record and return response
	  *
	  * @param  array $inputData
	  *
	  * @return  mixed
	  *---------------------------------------------------------------- */

	public function storeArticleContent($inputData)
	{   
        $keyValues = [
            'title',
            'description',
            'status',
            'languages__id',
            'articles__id'
        ];

	    $newArticleContent = new ArticleContentModel;
	    
	    // Check if task testing record added then return positive response
	    if ($newArticleContent->assignInputsAndSave($inputData, $keyValues)) {

	        return $newArticleContent;
	    }

	    return false;
	}

	/**
	  * Store new package record and return response
	  *
	  * @param  array $inputData
	  *
	  * @return  mixed
	  *---------------------------------------------------------------- */

	public function storeMultiplArticleContent($articleContent)
	{   
	    $newArticle = new ArticleContentModel;

	    // Check if task testing record added then return positive response
	    if ($newArticle->prepareAndInsert($articleContent)) {

	        return true;

	    }

	    return false;
	}

    /**
  	  * Update article record and return response
  	  *
  	  * @param  object $article
  	  * @param  array $inputData
  	  *
  	  * @return  mixed
  	  *---------------------------------------------------------------- */

	public function updateArticle($article, $inputData)
	{       
    	// Check if article updated then return positive response
    	if ($article->modelUpdate($inputData)) {

        	return true;
    	}

    	return false;
    }
    

    /**
  	  * Update article record and return response
  	  *
  	  * @param  object $article
  	  * @param  array $inputData
  	  *
  	  * @return  mixed
  	  *---------------------------------------------------------------- */

	public function update($article, $inputData)
	{       
    	// Check if article updated then return positive response
    	if ($article->modelUpdate($inputData)) {

        	return true;
    	}

    	return false;
	}

	/**
	  * fetch Article Contents by Article
	  *
	  * @param  int $articleId
	  *
	  * @return  mixed
	  *---------------------------------------------------------------- */

	public function fetchArticleContentsbyArticle($articleId)
	{   
		return ArticleContentModel::where('articles__id', '=', $articleId)
					->leftJoin('languages', 'article_contents.languages__id', '=', 'languages._id')
					->select(__nestedKeyValues([
						'article_contents.*',
						'languages' => [
							'_id As language_id',
							'name As language_title',
							'is_rtl',
						]
					]))
					->get();
 
	}

	/**
	  * remove article contents
	  *
	  * @param  array $contentIds
	  *
	  * @return  mixed
	  *---------------------------------------------------------------- */

	public function deleteArticleContents($contentIds)
	{   
		return ArticleContentModel::whereIn('_id', $contentIds)->delete();
	}

	/**
	  * update article contents
	  *
	  * @param  array $articleContents
	  *
	  * @return  mixed
	  *---------------------------------------------------------------- */

	public function updateMultiplArticleContent($articleContents)
	{   
	    $articleContent = new ArticleContentModel;

	    // Check if task testing record added then return positive response
	    if ($articleContent->batchUpdate($articleContents, '_id')) {

	        return true;

	    }

	    return false;
	}


	/**
      * Fetch the record of Article
      *
      * @param    int || string $idOrUid
      *
      * @return    eloquent collection object
      *---------------------------------------------------------------- */

      public function fetchArticleContentDetails($idOrUid)
      {   
          if (is_numeric($idOrUid)) {
  
              return ArticleContentModel::where('_id', $idOrUid)
              			->leftJoin('languages', 'article_contents.languages__id', '=', 'languages._id')
              			->select(
              				__nestedKeyValues([
              					'article_contents.*',
              					'languages.name as language_title'
              				])
              			)
              			->first();
          }
  
          return ArticleContentModel::where('_uid', $idOrUid)
          			->leftJoin('languages', 'article_contents.languages__id', '=', 'languages._id')
          			->select(
          				__nestedKeyValues([
          					'article_contents.*',
          					'languages.name as language_title'
          				])
          			)
          			->first();
      }

    

    /**
    * Fetch the record of Article
    *
    * @param  int || string $idOrUid
    *
    * @return    eloquent collection object
    *---------------------------------------------------------------- */

    public function fetchSearchData($searchTerm = null, $language, $version, $articleIds)
    {   
        return ArticleContentModel::join('articles', 'article_contents.articles__id', '=', 'articles._id')
            ->join('doc_versions', 'articles.doc_versions__id', '=', 'doc_versions._id')
            ->where('doc_versions.slug', $version)
            ->where('article_contents.languages__id', $language)
            ->where(function($query) {
                //check can this article data access via edit article permission
                if (!canThisArticleAccess()) {
                    $query->where('articles.status', 1)
                        ->where('article_contents.status', 1);
                }
            })
            ->whereIn('articles._id', $articleIds)
            ->select(
                __nestedKeyValues([
                    'articles' => ['_id','_uid AS article_uid', 'slug', 'doc_versions__id', 'status as articleStatus'],
                    'article_contents' => [
                        '_uid AS content_uid',
                        'title', 'description',
                        'languages__id', 'status'
                    ],
                    'doc_versions' => ['_id AS doc_id', 'version']
                ])
            )
            ->shodh($searchTerm, ['title', 'description'])
            ->get();
    }


   
    /**
      * Fetch all articles contents by language
      *
      * @param    string $languageId
      *
      * @return    eloquent collection object
      *---------------------------------------------------------------- */

	public function fetchArticleContentsbyLanguage($languageId)
	{   
		return ArticleContentModel::where('languages__id', $languageId)->get();
	}

	/**
      * Fetch all articles by language
      *
      * @param    string $languageId
      *
      * @return    eloquent collection object
      *---------------------------------------------------------------- */

	public function fetchArticlesbyLanguage($languageId)
	{   
		return ArticleModel::where('languages__id', $languageId)->get();
	}
	

	/**
      * Fetch the record of Article
      *
      * @param    int  $articleId
      *
      * @return    eloquent collection object
      *---------------------------------------------------------------- */

    public function fetchArticleWithPrimaryContent($articleId)
    {   
        return ArticleContentModel::leftJoin('articles', 'article_contents.articles__id', '=', 'articles._id')
    				->where('article_contents.languages__id', '=', DB::raw('articles.languages__id'))
    				->where('articles._uid', '=', $articleId)
    				->select(
    					__nestedKeyValues([
	    					'articles.*',
	    					'article_contents' => [
	    						'_uid AS content_uid',
	    						'title',
	    						'description',
	    						'languages__id As content_language_id'
	    					]
	    				])
    				)
                    ->first();
    }

    /**
	  * Fetch article with contents by project ID
	  *
	  * @param    int  $projectId
	  *
	  * @return    eloquent collection object
	  *---------------------------------------------------------------- */

	public function fetchProjectArticlesWithContent($projectId)
	{   
		return ArticleModel::where('projects__id', $projectId)
							->with('contents')
							->get();
    }
    
    /**
	  * Fetch article with contents by project ID
	  *
	  * @param    int  $projectId
	  *
	  * @return    eloquent collection object
	  *---------------------------------------------------------------- */

	public function fetchArticlesOfVersion($versionId, $language)
	{   
		return ArticleModel::where('doc_versions__id', $versionId)
                        ->with(['content' => function($q) use($language) {
                            return $q->where('languages__id', $language);
                        }])->get();
	}

	/**
	  * Fetch article with contents by project ID
	  *
	  * @param    int  $projectId
	  *
	  * @return    eloquent collection object
	  *---------------------------------------------------------------- */

	public function fetchProjectArticlesByVersion($projectId, $versionId)
	{
		return ArticleModel::where([
				'projects__id' => $projectId,
				'doc_versions__id' => $versionId,
				'previous_articles__id' => null
			])
			->with('contents', 'subArticles')
			->get();
    }


    /**
    * Fetch article with contents by project ID
    *
    * @param int  $projectId
    *
    * @return eloquent collection object
    *---------------------------------------------------------------- */

	public function fetchLastListOrder()
	{
		return ArticleModel::orderBy('list_order', 'desc')->pluck('list_order')->all();
    }


    /**
    * Fetch article with contents by project ID
    *
    * @param int  $projectId
    *
    * @return eloquent collection object
    *---------------------------------------------------------------- */

	public function batchUpdate($inputData, $primaryColumn)
	{
		return ArticleModel::bunchUpdate($inputData, $primaryColumn);
    }


    /**
    * Fetch last list order
    *
    * @param int  $projectId
    *
    * @return eloquent collection object
    *---------------------------------------------------------------- */

	public function fetchMaxListOrder($parent)
	{
		return ArticleModel::where('previous_articles__id', $parent)->max('list_order');
    }

    /**
    * Fetch Article By version id
    *
    * @param int  $versionId
    *
    * @return eloquent collection object
    *---------------------------------------------------------------- */

    public function fetchArticlesByVersionId($versionId)
    {
        return ArticleModel::where('doc_versions__id', $versionId)
                            ->whereNull('previous_articles__id')
                            ->orderBy('list_order', 'asc')
                            ->first();
    }

    /**
    * Fetch child Article by article id
    *
    * @param int  $articleSlug
    *
    * @return eloquent collection object
    *---------------------------------------------------------------- */

    public function fetchAllChildByArticleSlug($articleSlug, $versionId)
    {
        if (isFromEmbedView($articleSlug)) {
            return ArticleModel::where([
                                    '_uid' => $articleSlug, 
                                    'doc_versions__id' => $versionId
                                ])->with('subArticles')->first();
        } else {
            return ArticleModel::where([
                                'slug' => $articleSlug, 
                                'doc_versions__id' => $versionId
                            ])->with('subArticles')->first();
        }        
    }

    /**
    * Fetch Article by project and article id
    *
    * @param int $projectId
    * @param int $versionId
    *
    * @return eloquent collection object
    *---------------------------------------------------------------- */

    public function fetchArticleByProjectAndVersionId($projectId, $versionId, $lang)
    {
        return ArticleModel::where([
                                'projects__id' => $projectId,
                                'doc_versions__id' => $versionId
                            ])
                            ->with([
                                'contents' => function($acq) use($lang) {
                                    return $acq->where(['languages__id' => $lang])
                                                ->where(function($contentQuery) {
                                                    //check can this article data access via edit article permission
                                                    if (!canThisArticleAccess()) {
                                                        $contentQuery->where('status', 1);
                                                    }
                                                });
                                }
                            ])
                            ->where(function($query) {
                                //check can this article data access via edit article permission
                                if (!canThisArticleAccess()) {
                                    //__dd("NOT ACCESS");
                                    $query->where('status', 1);
                                }
                            })
                            ->orderBy('list_order')
                            ->get();
    }

    /**
    * Fetch Article by project and article id
    *
    * @param int $projectId
    * @param int $versionId
    *
    * @return eloquent collection object
    *---------------------------------------------------------------- */

    public function fetchArticleForSearchData($projectId, $versionId)
    {
        return ArticleModel::where([
                                'projects__id' => $projectId,
                                'doc_versions__id' => $versionId
                            ])->get();
    }

    /**
    * Fetch Article content by project and language id
    *
    * @param int $projectId
    * @param int $languageId
    *
    * @return eloquent collection object
    *---------------------------------------------------------------- */

    public function fetchArticleContentByProjectAndLangId($projectId, $languageId)
    {
        return ArticleModel::join('article_contents', 'articles._id', '=', 'article_contents.articles__id')
                            ->where([
                                'articles.projects__id' => $projectId,
                                'article_contents.languages__id' => $languageId
                            ])
                            ->select(
                                __nestedKeyValues([
                                    'articles' => [
                                        '_id',
                                        'projects__id'
                                    ],
                                    'article_contents' => [
                                        '_id AS article_content_id',
                                        'articles__id',
                                        'languages__id'
                                    ]
                                ])
                            )->get();
    }

    /**
    * Delete Articles by article ids
    *
    * @param array $articleIds
    *
    * @return eloquent collection object
    *---------------------------------------------------------------- */

    public function deleteArticlesByIds($articleIds)
    {
        return ArticleModel::whereIn('_id', $articleIds)->delete();
    }
}