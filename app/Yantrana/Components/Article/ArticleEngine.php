<?php
/*
* ArticleEngine.php - Main component file
*
* This file is part of the Article component.
*-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\Article;

use App\Yantrana\Base\BaseEngine;

use App\Yantrana\Components\Article\Repositories\{
    ArticleRepository
};
use App\Yantrana\Components\Version\Repositories\VersionRepository;

use App\Yantrana\Components\Project\Repositories\ProjectRepository;
use App\Yantrana\Components\Language\Repositories\LanguageRepository;
use App\Yantrana\Components\Article\Interfaces\ArticleEngineInterface;
use Exception;

class ArticleEngine extends BaseEngine implements ArticleEngineInterface
{
    /**
     * @var  ArticleRepository $articleRepository - Article Repository
     */
    protected $articleRepository;

    /**
     * @var  ProjectRepository $projectRepository - Project Repository
     */
    protected $projectRepository;

    /**
     * @var  LanguageRepository $languageRepository - Language Repository
     */
    protected $languageRepository;

    /**
     * @var  VersionRepository $versionRepository - Version Repository
     */
    protected $versionRepository;

    /**
     * Constructor
     *
     * @param  ArticleRepository $articleRepository - Article Repository
     * @param  ProjectRepository $projectRepository - Project Repository
     * @param  LanguageRepository $languageRepository - Language Repository
     *
     * @return  void
     *-----------------------------------------------------------------------*/

    function __construct(
        ArticleRepository $articleRepository,
        ProjectRepository $projectRepository,
        LanguageRepository $languageRepository,
        VersionRepository $versionRepository
    ) {
        $this->articleRepository = $articleRepository;
        $this->projectRepository = $projectRepository;
        $this->languageRepository = $languageRepository;
        $this->versionRepository = $versionRepository;
    }

    /**
     * Article datatable source
     *
     * @return  array
     *---------------------------------------------------------------- */

    public function prepareArticles($projectUid, $versionUid)
    {
        $project = $this->projectRepository->fetch($projectUid);

        if (__isEmpty($project)) {
            return $this->engineReaction(18, null, 'Project not exists.');
        }

        $version = $this->versionRepository->fetch($versionUid);

        if (__isEmpty($version)) {
            return $this->engineReaction(18, null, 'Version not exists.');
        }

        $projectLanguage = $project->languages__id;
        $rawArticleCollection = $this->articleRepository->fetchArticles($project->_id, $version->_id, $projectLanguage);
        $articleCollection = [];
        if (!__isEmpty($rawArticleCollection)) {
            foreach ($rawArticleCollection as $rawArticle) {
                $articleCollection[] = [
                    '_id'                       => $rawArticle->_id,
                    '_uid'                      => $rawArticle->_uid,
                    'status'                    => $rawArticle->status,
                    'updated_at'                => $rawArticle->updated_at,
                    'previous_articles__id'     => $rawArticle->previous_articles__id,
                    'published_at'              => $rawArticle->published_at,
                    'list_order'                => $rawArticle->list_order,
                    'slug'                      => $rawArticle->slug,
                    'content_uid'               => (!__isEmpty($rawArticle->content))
                        ? $rawArticle->content->content_uid
                        : null,
                    'title'                     => (!__isEmpty($rawArticle->content))
                        ? $rawArticle->content->title
                        : title_case(str_replace('-', ' ', $rawArticle->slug)),
                    'content_status'            => (!__isEmpty($rawArticle->content))
                        ? $rawArticle->content->content_status
                        : '',
                    'content_language_id'       => (!__isEmpty($rawArticle->content))
                        ? $rawArticle->content->content_language_id
                        : null
                ];
            }

            $config = configItem('article.status');
            $parentArticleData = [];
            foreach ($articleCollection as $articleKey => $articleValue) {
                $parentArticle = last(findArticleParents($articleCollection, $articleValue['_id']));
                $parentArticleData[$articleValue['_id']] = array_get($parentArticle, 'slug');
            }

            foreach ($articleCollection as $key => $ar) {
                $isParent = true;
                if (array_key_exists($ar['_id'], $parentArticleData)) {
                    $detailsUrl = route('doc.view', [
                        'projectSlug' => $project->slug,
                        'versionSlug' => $version->slug,
                        'articleSlug'  => $parentArticleData[$ar['_id']]
                    ]);
                }

                $articleCollection[$key]['formatted_status'] = configItemString($config, $ar['status']);
                $articleCollection[$key]['published_at'] = humanReadableFormat($ar['published_at']);
                $articleCollection[$key]['formated_updated_at'] = humanReadableFormat($ar['updated_at']);
                $articleCollection[$key]['slugTitle'] = str_limit($ar['title'], 20, '');
                if (!__isEmpty($ar['previous_articles__id'])) {
                    $isParent = false;
                    $detailsUrl = $detailsUrl . '?lang=' . $projectLanguage . '#' . $ar['slug'] . '-' . $projectLanguage;
                }
                $articleCollection[$key]['detailUrl'] = $detailsUrl;
                $articleCollection[$key]['isParent'] = $isParent;
                $articleCollection[$key]['printUrl'] = route('manage.article.read.print_article', [
                    'projectSlug' => $project->slug,
                    'versionSlug' => $version->slug,
                    'articleSlug' => $ar['slug']
                ]);
                $articleCollection[$key]['children'] = [];
            }
        }

        return $this->engineReaction(1, [
            'articles' => buildTree($articleCollection),
            'projectSlug' => $project->_uid,
            'versionSlug' => $version->_uid,
            'languages__id'        => $project->languages__id
        ]);
    }
    /**
     * Article delete process
     *
     * @param  mix $articleIdOrUid
     *
     * @return  array
     *---------------------------------------------------------------- */

    public function processArticleDelete($articleIdOrUid)
    {
        $article = $this->articleRepository->fetch($articleIdOrUid);

        if (__isEmpty($article)) {
            return $this->engineReaction(18, null, __tr('Article not found.'));
        }

        if ($this->articleRepository->deleteArticle($article)) {

            activityLog(7, $article->_id, 3);

            return $this->engineReaction(1, null, __tr('Article deleted.'));
        }

        return $this->engineReaction(2, null, __tr('Article not deleted.'));
    }

    /**
     * Prepare Data for Selectize
     *
     * @return  array
     *---------------------------------------------------------------- */
    protected function prepareDataForSelectize($articleCollection, $count = 0, $articleContainer = [])
    {
        foreach ($articleCollection as $articleKey => $article) {
            $count++;

            $articleContainer[] = [
                '_id' => $article['_id'],
                'title' => (!__isEmpty($article['title']))
                    ? $article['title']
                    : title_case(str_replace('-', ' ', $article['slug'])),
                'published_at' => $article['published_at'],
                'previous_articles__id' => $article['previous_articles__id'],
                'count' => $count,
            ];

            // Check if children exists
            if (!__isEmpty($article['children'])) {
                $articleContainer = $this->prepareDataForSelectize($article['children'], $count, $articleContainer);
            }
            // If Parent article id is empty means current article is parent article then set count to 0
            if (__isEmpty($article['previous_articles__id'])) {
                $count = 0;
            } else { // Else substract 1 from count
                $count--;
            }
        }
        return $articleContainer;
    }


    /**
     * Article Add Support Data
     *
     * @return  array
     *---------------------------------------------------------------- */
    public function prepareArticleSupportData($projectUid, $versionUid)
    {
        $project = $this->projectRepository->fetch($projectUid);

        if (__isEmpty($project)) {
            return $this->engineReaction(18, null, __tr('Project not found.'));
        }

        $version = $this->versionRepository->fetch($versionUid);

        if (__isEmpty($version)) {
            return $this->engineReaction(18, null, 'Version not exists.');
        }

        $versionData = [
            '_id'  => $version->_id,
            '_uid' => $version->_uid,
            'version'  => $version->version,
            'slug'  => $version->slug,
            'status'  => $version->status,
            'is_primary'  => $version->mark_as_primary,
            'projects__id'  => $version->projects__id
        ];

        $jsondata = $project->__data;

        $projectLanguages = isset($jsondata['project_languages']) ? $jsondata['project_languages'] : [];

        //for selecting parent article
        $rawArticles = $this->articleRepository->fetchVersionArticleChildren($project->_id, $version->_id)->toArray();
        $articles = $this->prepareDataForSelectize($rawArticles);

        $allLanguages = $this->languageRepository->fetchAllRequiredLanguages($projectLanguages);

        $articleContents = [];

        //check if not empty
        if (!__isEmpty($allLanguages)) {
            foreach ($allLanguages as $key => $language) {
                $articleContents[] = [
                    "language_id" => $language->_id,
                    "language_title" => $language->name,
                    "tab_id" => ("language__" . $language->_id),
                    "tab_name" => $language->name,
                    "nav_link_id" => ("language_label_" . $language->_id),
                    "title" => null,
                    "description" => null,
                    "status" => ($project->languages__id === $language->_id) ? 1 : 2,
                    "is_primary" => ($project->languages__id === $language->_id) ? 1 : 2,
                ];
            }
        }

        $isPrimary = array_column($articleContents, 'is_primary');

        array_multisort($isPrimary, SORT_ASC, $articleContents);

        return $this->engineReaction(1, [
            'articles'  => $articles,
            'articles_content' => $articleContents,
            'versionData' => $versionData,
            'projectName' => $project->name,
            'primaryLanguage' => $project->languages__id
        ]);
    }

    /**
     *  Store article content
     */
    public function prepareStoreContent($data)
    {
        if (!$articleContent = $this->articleRepository->storeArticleContent($data)) {
            return false;
        }

        // Content Activity
        activityLog(8, $articleContent['_id'], 9);

        return true;
    }

    /**
     *  Update article content
     */
    private function prepareUpdateContent($contentId, $data)
    {
        $articleContent = $this->articleRepository->fetchContent($contentId);

        if ($updatedContent = $this->articleRepository->update($articleContent, $data)) {

            // Content Activity
            activityLog(8, $contentId, 2);

            return  true;
        }

        return false;
    }

    /**
     * Article create 
     *
     * @param  array $inputData
     *
     * @return  array
     *---------------------------------------------------------------- */

    public function processArticleCreate($inputData, $requestType, $projectUid)
    {
        $project = $this->projectRepository->fetch($projectUid);

        if (__isEmpty($project)) {
            return $this->engineReaction(2, null, 'Project not found.');
        }

        $projectId = $project->_id;


        $transactionResponse = $this->articleRepository->processTransaction(function () use ($inputData, $projectId, $project) {

            $articleAdded = false;
            $articleContent = [];

            $previousArticlesId = $inputData['previous_articles__id'] ?? null;

            $lastListOrderNumber = $this->articleRepository->fetchMaxListOrder($previousArticlesId);

            $storeData = [
                'projects__id'     => $projectId,
                'published_at'  => now(),
                'status'         => $inputData['status'],
                'previous_articles__id' => $previousArticlesId,
                'list_order' => $lastListOrderNumber + 1,
                'user_authorities__id' => getUserAuthorityId(),
                'compilation_type'     => 1,
                'type' => $inputData['article_type'] ?? 2,
                'doc_versions__id' => $inputData['doc_versions__id'],
                'slug' => $inputData['slug']
            ];

            if ($article = $this->articleRepository->storeArticle($storeData)) {

                // Article Activity 
                activityLog(7, $article->_id, 9);

                if (!__isEmpty($inputData['articles_content'])) {

                    foreach ($inputData['articles_content'] as $key => $content) {
                        // Store Article content with history
                        $this->prepareStoreContent([
                            'title'         => isset($content['title']) ? $content['title'] : 'No title here...',
                            'description'   => isset($content['description']) ? $content['description'] : '<p>No content here...</p>',
                            'languages__id'  => $content['language_id'],
                            'status'        => $content['status'],
                            'articles__id'  => $article->_id
                        ]);
                    }

                    $articleAdded = true;
                }
            }

            if ($articleAdded) {
                $this->projectRepository->updateProjectModel($project);
                return $this->articleRepository->transactionResponse(1, ['article_uid' => $article->_uid], __tr('Article added.'));
            }

            return $this->articleRepository->transactionResponse(2, null, __tr('Article not added.'));
        });

        return $this->engineReaction($transactionResponse);
    }

    /**
     * Article prepare update data 
     *
     * @param  mix $articleIdOrUid
     *
     * @return  array
     *---------------------------------------------------------------- */
    public function prepareArticleUpdateData($articleIdOrUid, $projectUid, $versionUid)
    {
        $project = $this->projectRepository->fetch($projectUid);

        if (__isEmpty($project)) {
            return $this->engineReaction(18, null, __tr('Project not found.'));
        }

        $article = $this->articleRepository->fetch($articleIdOrUid);

        // Check if $article not exist then throw not found 
        // exception
        if (__isEmpty($article)) {
            return $this->engineReaction(18, null, __tr('Article not found.'));
        }

        $version = $this->versionRepository->fetch($versionUid);

        if (__isEmpty($version)) {
            return $this->engineReaction(18, null, 'Version not exists.');
        }

        $versionData = [
            '_id'  => $version->_id,
            '_uid' => $version->_uid,
            'version'  => $version->version,
            'slug'  => $version->slug,
            'status'  => $version->status,
            'is_primary'  => $version->mark_as_primary,
            'projects__id'  => $version->projects__id
        ];

        $jsondata = $project->__data;
        $projectLanguages =  isset($jsondata['project_languages']) ? $jsondata['project_languages'] : [];

        $allLanguages = $this->languageRepository->fetchAllRequiredLanguages($projectLanguages);

        $articleContents = $this->articleRepository->fetchArticleContentsbyArticle($article->_id);

        //$articles = $this->articleRepository->fetchVerionArticles($project->_id, $article->doc_versions__id, $article->_id)->toArray();
        //for selecting parent article
        $rawArticles = $this->articleRepository->fetchVersionArticleChildren($project->_id, $article->doc_versions__id)->toArray();
        $articles = $this->prepareDataForSelectize($rawArticles);

        $addedContents = [];
        $existingContent = [];
        if (!__isEmpty($allLanguages)) {
            foreach ($allLanguages as $key => $lang) {

                $existingContent = [];

                //check if not empty
                if (!__isEmpty($articleContents)) {

                    foreach ($articleContents as $key => $content) {

                        if ($lang->_id === $content->language_id) {

                            $existingContent =  [
                                'article_content_id' =>  $content->_id,
                                'article_content_uid' => $content->_uid,
                                'language_id'      => $content->language_id,
                                'language_title' => $content->language_title,
                                'tab_id'     => ('language__' . $content->language_id),
                                'tab_name'     => $content->language_title,
                                'nav_link_id'     => ('language_label_' . $content->language_id),
                                'title' => $content->title,
                                'description' => $content->description,
                                'status' =>  $content->status,
                                'prev_added' => 1,
                                'is_primary' => ($project->languages__id == $content->language_id) ? 1 : 2,
                                'exist' => 1
                            ];
                        }
                    }
                }

                if (!__isEmpty($existingContent)) {
                    $addedContents[] = $existingContent;
                } elseif (!__isEmpty($article) and __isEmpty($articleContents)) {
                    $addedContents[] = [
                        'language_id'    => $lang->_id,
                        'language_title' => $lang->name,
                        'tab_id'    => ('language__' . $lang->_id),
                        'tab_name'  => $lang->name,
                        'nav_link_id'   => ('language_label_' . $lang->_id),
                        'title' => ($lang->_id == $project->languages__id)
                            ? title_case(str_replace('-', ' ', $article->slug))
                            : '',
                        'description' => null,
                        'status' =>  1,
                        'prev_added' => 2,
                        'is_primary' => ($lang->_id == $project->languages__id) ? 1 : 2,
                        'exist' => 2
                    ];
                } else {
                    $addedContents[] = [
                        'language_id'      => $lang->_id,
                        'language_title' => $lang->name,
                        'tab_id'     => ('language__' . $lang->_id),
                        'tab_name'     => $lang->name,
                        'nav_link_id'     => ('language_label_' . $lang->_id),
                        'title' => null,
                        'description' => null,
                        'status' =>  2,
                        'prev_added' => 2,
                        'is_primary' => 2,
                        'exist' => 2
                    ];
                }
            }
        }

        $isPrimary = array_column($addedContents, 'is_primary');
        $exist = array_column($addedContents, 'exist');

        array_multisort($isPrimary, SORT_NUMERIC, $addedContents);

        $articleJsonData = $article->__data;

        $articleData = [
            '_id'    => $article->_id,
            '_uid'    => $article->_uid,
            'compilation_type'    => $article->compilation_type,
            'article_status' => $article->status,
            'slug' => $article->slug,
            '__data'    => $article->__data,
            'previous_articles__id'    => $article->previous_articles__id,
            'article_type'    => $article->type ?? 2,
            'articles_content' => $addedContents,
            'doc_versions__id' => $article->doc_versions__id
        ];

        return $this->engineReaction(1, [
            'articleData'         => $articleData,
            'projectName' => $project->name,
            'primaryLanguage' => $project->languages__id,
            'articles' => $articles,
            'versionData' => $versionData
        ]);
    }

    /**
     * Article process update 
     * 
     * @param  mix $articleIdOrUid
     * @param  array $inputData
     *
     * @return  array
     *---------------------------------------------------------------- */

    public function processArticleUpdate($projectUid, $articleIdOrUid, $inputData)
    {
        $project = $this->projectRepository->fetch($projectUid);

        if (__isEmpty($project)) {
            return $this->engineReaction(18, null, __tr('Project not found.'));
        }

        $article = $this->articleRepository->fetch($articleIdOrUid);

        // Check if $article not exist then throw not found 
        // exception
        if (__isEmpty($article)) {
            return $this->engineReaction(18, null, __tr('Article not found.'));
        }

        if (isset($inputData['previous_articles__id']) and !__isEmpty($inputData['previous_articles__id'])) {
            if ($inputData['previous_articles__id'] == $article->_id) {
                return $this->engineReaction(2, null, __tr('Please select different parent article.'));
            }
        }

        $transactionResponse = $this->articleRepository->processTransaction(function () use ($inputData, $article, $project) {

            $publishedAt = '';

            if ($inputData['status'] == 1) {
                $publishedAt = date('Y-m-d H:i:s');
            } else if ($inputData['status'] == 3) {
                $publishedAt = null;
            } else {
                $publishedAt = $article->published_at;
            }

            $updateData = [
                'status'         => $inputData['status'],
                'published_at'  => $publishedAt,
                'slug' => $inputData['slug'],
                'previous_articles__id' => $inputData['previous_articles__id'] ?? null,
                'type' => $inputData['article_type'] ?? 2
            ];

            $articleUpdated = false;

            try {

                // Check if Article updated
                if ($this->articleRepository->updateArticle($article,  $updateData)) {

                    // Article Activity
                    activityLog(7, $article->_id, 2);

                    $articleUpdated = true;
                }
               
                $updateArticleContent = [];
                $storeArticleContent = [];
                $removeArticleContents = [];

                //fetch existing article content by article id
                $articleContentData = $this->articleRepository->fetchArticleContentsbyArticle($article->_id);
                           
                //check is empty
                if (!__isEmpty($inputData['articles_content'])) {
                    foreach ($inputData['articles_content'] as $key => $contentData) {
                        $languageId = [];
                        if (!__isEmpty($articleContentData)) {
                            foreach ($articleContentData as $key => $articleContent) {
                                $languageId[] = $articleContent->languages__id;
                                //check article is existing content
                                if ($articleContent->languages__id == $contentData['language_id']) {
                                    $updateArticleContent[] = [
                                        'article_content_id' => $articleContent->_id,
                                        'article_content_uid' => $articleContent->_uid,
                                        'language_id' => $contentData['language_id'],
                                        'language_title'=> $contentData['language_title'],
                                        'tab_id'        => $contentData['tab_id'],
                                        'tab_name'      => $contentData['tab_name'],
                                        'nav_link_id'   => $contentData['nav_link_id'],
                                        'title'         => $contentData['title'],
                                        'description'   => $contentData['description'],
                                        'status'        => $contentData['status'],
                                        'prev_added'    => 1,
                                        'is_primary'    => $contentData['is_primary'],
                                        'exist'         => $contentData['exist']
                                    ];
                                }
                            }
                        }
                        
                        //check article content not exist
                        if (!in_array($contentData['language_id'], $languageId)) {
                            $storeArticleContent[] = [
                                'language_id'   => $contentData['language_id'],
                                'language_title'=> $contentData['language_title'],
                                'title'         => $contentData['title'],
                                'tab_id'        => $contentData['tab_id'],
                                'tab_name'      => $contentData['tab_name'],
                                'nav_link_id'   => $contentData['nav_link_id'],
                                'description'   => $contentData['description'],
                                'status'        => $contentData['status'],
                                'prev_added'    => 2,
                                'is_primary'    => $contentData['is_primary'],
                                'exist'         => $contentData['exist']
                            ];
                        }
                    }
                }

                //merge update and store article content data
                $artcleUpdateData = array_merge($updateArticleContent, $storeArticleContent);
                
                if (!__isEmpty($artcleUpdateData)) {

                    foreach ($artcleUpdateData as $key => $content) {

                        //existing content if change then update
                        if ($content['prev_added'] == 1 && !__isEmpty($content['title']) && !__isEmpty($content['description'])) {

                            // Update Article Content with store history
                            $this->prepareUpdateContent($content['article_content_id'], [
                                '_id'           => $content['article_content_id'],
                                'languages__id'  => $content['language_id'],
                                'title'         => $content['title'],
                                'description'   => $content['description'],
                                'status'        => $content['status']
                            ]);

                            //insert new content
                        } else if ($content['prev_added'] == 2 && !__isEmpty($content['title']) && !__isEmpty($content['description'])) {

                            $this->prepareStoreContent([
                                'title'         => $content['title'],
                                'description'   => $content['description'],
                                'status'        => $content['status'],
                                'languages__id' => $content['language_id'],
                                'articles__id'  => $article->_id
                            ]);

                            //delete content if title and description are removed
                        } else if ($content['prev_added'] == 1 && !__isEmpty($content['article_content_id'])) {

                            if ($project->languages__id != $content['language_id']) {

                                $removeArticleContents[] = $content['article_content_id'];
                            }
                        }
                    }

                    $articleUpdated = true;
                }

                //check if deleted
                if (!__isEmpty($removeArticleContents)) {
                    // Check if Article updated
                    if ($this->articleRepository->deleteArticleContents($removeArticleContents)) {
                        $articleUpdated = true;
                    }
                }

                if ($articleUpdated) {
                    $this->projectRepository->updateProjectModel($project);
                    return $this->articleRepository->transactionResponse(1, null, __tr('Article updated.'));
                }

                return $this->articleRepository->transactionResponse(14, null, __tr('Article not updated.'));
            }

            // catch exception
            catch (Exception $e) {
                return $this->articleRepository->transactionResponse(2, null, config('app.debug') ? $e->getMessage() : __tr('Something went wrong'));
            }
        });

        return $this->engineReaction($transactionResponse);
    }


    /**
     * Article detail 
     *
     * @param  mix $articleIdOrUid
     * @param  mix $contentUid
     *
     * @return  array
     *---------------------------------------------------------------- */
    public function prepareArticleContentDetails($articleIdOrUid, $contentUid)
    {
        $article = $this->articleRepository->fetch($articleIdOrUid);

        if (__isEmpty($article)) {
            return $this->engineReaction(18, null, __tr('Article not found.'));
        }

        $content = $this->articleRepository->fetchArticleContentDetails($contentUid);

        if (__isEmpty($article)) {
            return $this->engineReaction(18, null, __tr('Article not found.'));
        }

        $articleContent = [
            'created_at'    => formatDateTime($content->created_at),
            'updated_at'    => formatDateTime($content->updated_at),
            'title'            => $content->title,
            'description'    => $content->description,
            'language_title' => $content->language_title,
        ];

        return $this->engineReaction(1, [
            'article_content' => $articleContent
        ]);
    }

    /**
     * Article detail 
     *
     * @param  mix $articleIdOrUid
     * @param  mix $contentUid
     *
     * @return  array
     *---------------------------------------------------------------- */
    public function prepareArticleDetails($articleIdOrUid)
    {
        $article = $this->articleRepository->fetch($articleIdOrUid);

        // Check if $article not exist then throw not found 
        // exception
        if (__isEmpty($article)) {
            return $this->engineReaction(18, null, __tr('Article not found.'));
        }

        $project = $this->projectRepository->fetch($article->projects__id);

        if (__isEmpty($project)) {
            return $this->engineReaction(18, null, __tr('Project not found.'));
        }

        $articleContents = $this->articleRepository->fetchArticleContentsbyArticle($article->_id);

        $addedContents = [];
        //check if not empty
        if (!__isEmpty($articleContents)) {
            foreach ($articleContents as $key => $content) {

                $addedContents[] = [
                    'article_content_id'     => $content->_id,
                    'article_content_uid'     => $content->_uid,
                    'language_id'        => $content->language_id,
                    'language_title'    => $content->language_title,
                    'title'                => $content->title,
                    'description'        => $content->description,
                    'status'            => $content->status,
                    'is_primary'        => ($content->languages__id == $project->languages__id) ?? false
                ];
            }
        }

        $jsondata = $article->__data;

        $articleTypeConfig = configItem('article.type');

        $articleData = [
            '_id'    => $article->_id,
            '_uid'    => $article->_uid,
            'article_status' => techItemString($article->status),
            'languages_id'    => $article->languages__id,
            'article_type'    => configItemString($articleTypeConfig, $article->type),
            'articles_content' => $addedContents,
        ];

        return $this->engineReaction(1, [
            'article_data'         => $articleData
        ]);
    }


    /**
     * Article List for public side
     *
     * @param  mix $articleIdOrUid
     *
     * @return  array
     *---------------------------------------------------------------- */

    public function preparePublicLists()
    {
        $articles = $this->articleRepository->allPrimaryArticlesWithPaginate(1);
        $metastring = '';
        if (!__isEmpty($articles)) {
            foreach ($articles as $key => $article) {
                $metastring .= $article->title . ' ';
            }
        }

        return [
            'pagination_link'  => sprintf($articles->links()),
            'articles' => $articles,
            'metaDescription' => getUniqueWords($metastring, null),
            'metaKeywords' => getUniqueWords($metastring, null, ',')
        ];
    }

    /**
     * Article List for public side
     *
     * @param  mix $articleUid
     *
     * @return  array
     *---------------------------------------------------------------- */

    public function preparePublicDetailsView($articleContentUid)
    {
        $content = $this->articleRepository->fetchContent($articleContentUid, 1);

        if (__isEmpty($content)) {
            return $this->engineReaction(18, null, 'Article not found.');
        }

        $article = $this->articleRepository->fetch($content->articles__id, 1);

        if (__isEmpty($article)) {
            return $this->engineReaction(18, null, 'Article not found.');
        }

        $jData = $article->__data;

        $article_type = false;


        if (__ifIsset($jData['article_type'])) {
            $article_type = ((int) $article->type == 1) ? false : true;
        }


        $data = [
            'content_id'   => $content->_id,
            'article_id'   => $article->_id,
            'content_uid'  => $content->_uid,
            'title'        => $content->title,
            'description'  => $content->description,
            'published_at'  => formatDateTime($content->published_at, 'l jS F Y'),
            'isPublic'     => $article_type,
        ];

        $prev_article = $this->articleRepository->fetchArticleWithContent($article->previous_articles__id);
        if (!__isEmpty($prev_article)) {
            $data['prev_article_url'] = route('public.article.read.details_view', [
                'articleContentUid' => $prev_article->content_uid
            ]);
        }

        $articleContents = $this->articleRepository->fetchArticleContentsbyArticle($content->articles__id);
        $languageContents = [];
        if (!__isEmpty($articleContents)) {
            foreach ($articleContents as $key => $articleContent) {
                if ($content->_id != $articleContent->_id) {
                    $languageContents[] = [
                        'content_language' => $articleContent->language_title,
                        'content_url' => route('public.article.read.details_view', ['articleContentUid' => $articleContent->_uid])
                    ];
                }
            }
        }

        $data['languages'] = $languageContents;

        return $this->engineReaction(1, [
            'article' => $data
        ]);
    }


    /**
     * Article process update 
     * 
     * @param  mix $articleIdOrUid
     * @param  array $inputData
     *
     * @return  array
     *---------------------------------------------------------------- */

    public function processUpdateParent($articleIdOrUid, $inputData)
    {
        $article = $this->articleRepository->fetch($articleIdOrUid);

        // Check if $article not exist then throw not found 
        // exception
        if (__isEmpty($article)) {
            return $this->engineReaction(18, null, __tr('Article not found.'));
        }

        $lastListOrder = $this->articleRepository->fetchLastListOrder();

        // Check if $article not exist then throw not found 
        // exception
        if (__isEmpty($article)) {
            return $this->engineReaction(18, null, __tr('Article not found.'));
        }

        if ($this->articleRepository->batchUpdate($inputData['listOrderData'], '_id')) {

            activityLog(7, $article->_id, 2);

            return $this->engineReaction(1);
        }

        return $this->engineReaction(14);
    }
}
