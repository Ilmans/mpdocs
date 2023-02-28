<?php
/*
* ArticleController.php - Controller file
*
* This file is part of the Article component.
*-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\Article\Controllers;


use App\Yantrana\Components\Article\Requests\ArticleAddRequest; 
use App\Yantrana\Components\Article\Requests\ArticleEditRequest;
use App\Yantrana\Base\BaseController;
use App\Yantrana\Components\Article\ArticleEngine;
use App\Yantrana\Components\Home\HomeEngine;
use App\Yantrana\Support\CommonPostRequest;

use Illuminate\Http\Request;

class ArticleController extends BaseController 
{    
    /**
     * @var  ArticleEngine $articleEngine - Article Engine
     */
    protected $articleEngine;

    /**
     * @var  HomeEngine $homeEngine - Home Engine
     */
    protected $homeEngine;

    /**
      * Constructor
      *
      * @param  ArticleEngine $articleEngine - Article Engine
      *
      * @return  void
      *-----------------------------------------------------------------------*/

    function __construct(
        ArticleEngine $articleEngine,
        HomeEngine $homeEngine
    )
    {
        $this->articleEngine = $articleEngine;
        $this->homeEngine = $homeEngine;
    }


    /**
    * list of Article
    *
    * @return  json object
    *---------------------------------------------------------------- */

  	public function prepareArticleList($projectUid, $verionUid) 
  	{
        $processReaction =  $this->articleEngine->prepareArticles($projectUid, $verionUid);
          
        return __processResponse($processReaction, [], [], true);
  	}
         
    
	/**
	  * Article process delete 
	  *
	  * @param  mix $articleIdOrUid
	  *
	  * @return  json object
	  *---------------------------------------------------------------- */

	public function processArticleDelete($articleIdOrUid, CommonPostRequest $request)
	{   
	    $processReaction = $this->articleEngine->processArticleDelete($articleIdOrUid);

	    return __processResponse($processReaction, [], [], true);
	}
 
    /**
      * Article Add Support Data 
      *
      * @return  json object
      *---------------------------------------------------------------- */

    public function prepareArticleSupportData($projectUid, $versionUid)
    {
        $processReaction = $this->articleEngine->prepareArticleSupportData($projectUid, $versionUid);

        return __processResponse($processReaction, [], $processReaction['data']);
    }
 
	/**
	  * Article create process 
	  *
	  * @param  object ArticleListRequest $request
	  *
	  * @return  json object
	  *---------------------------------------------------------------- */

	public function processArticleCreate(ArticleAddRequest $request, $requestType, $projectUid)
	{   
	    $processReaction = $this->articleEngine->processArticleCreate($request->all(), $requestType, $projectUid);

	    return __processResponse($processReaction, [], [], true);
	}
 
    /**
  	  * Article get update data 
  	  *
  	  * @param  mix $articleIdOrUid
  	  *
  	  * @return  json object
  	  *---------------------------------------------------------------- */

	public function updateArticleData($articleIdOrUid, $projectUid, $versionUid)
	{
    	$processReaction = $this->articleEngine->prepareArticleUpdateData($articleIdOrUid, $projectUid, $versionUid);

    	return __processResponse($processReaction, [], [], true);
	}

	/**
  	  * Article process update 
  	  * 
  	  * @param  mix @param  mix $articleIdOrUid
  	  * @param  object ArticleListRequest $request
  	  *
  	  * @return  json object
  	  *---------------------------------------------------------------- */

	public function processArticleUpdate($projectUid, $articleIdOrUid, $requestType, ArticleEditRequest $request)
	{   
    	$processReaction = $this->articleEngine->processArticleUpdate($projectUid, $articleIdOrUid, $request->all());

    	return __processResponse($processReaction, [], [], true);
    }
    
	/**
  	  * Article content data 
  	  *
  	  * @param  mix $articleIdOrUid
  	  * @param  mix $contentUid
  	  *
  	  * @return  json object
  	  *---------------------------------------------------------------- */

	public function readArticleContentDetails($articleIdOrUid, $contentUid)
	{
    	$processReaction = $this->articleEngine->prepareArticleContentDetails($articleIdOrUid, $contentUid);

    	return __processResponse($processReaction, [], [], true);
    }


    /**
    * Get the home article lists
    *---------------------------------------------------------------- */

    public function publicDetailsView($articleContentUid)
    {   
        $processReaction = $this->articleEngine->preparePublicDetailsView($articleContentUid);

        if ($processReaction['reaction_code'] !== 1) {

            return redirect()->route('public.app'
                                )->with([
                                    'error' => true,
                                    'message' => $processReaction['message'],
                                ]);
        }

        return $this->loadPublicView('article.public.details', $processReaction['data']);
    }
    

	/**
  	  * Article details 
  	  *
  	  * @param  mix $articleIdOrUid
  	  *
  	  * @return  json object
  	  *---------------------------------------------------------------- */

	public function readArticleDetails($articleIdOrUid)
	{
    	$processReaction = $this->articleEngine->prepareArticleDetails($articleIdOrUid);

    	return __processResponse($processReaction, [], [], true);
    }

    /**
    * for iframe
    *---------------------------------------------------------------- */

    public function publicEmbedDetailsView($articleContentUid)
    {
        $processReaction = $this->articleEngine->preparePublicDetailsView($articleContentUid);

        if ($processReaction['reaction_code'] !== 1) {

            return redirect()->route('public.app'
                                )->with([
                                    'error' => true,
                                    'message' => $processReaction['message'],
                                ]);
        }

        return $this->loadEmbeddedPublicView('article.public.details', $processReaction['data']);
    }

    /**
    * for iframe
    *---------------------------------------------------------------- */

    public function loadEmbedScript()
    {
     	$jsContent = view('embed.embed-script')->render();

	    return response()
		    	->make($jsContent, 200)
		        ->header('Content-Type', 'application/javascript');
    }

    /**
    * Article details 
    *
    * @param  mix $articleIdOrUid
    *
    * @return  json object
    *---------------------------------------------------------------- */

	public function updateParent($articleIdOrUid, CommonPostRequest $request)
	{
        $processReaction = $this->articleEngine
                                ->processUpdateParent(
                                    $articleIdOrUid, $request->all()
                                );

    	return __processResponse($processReaction, [], [], true);
    }

    /**
     * process download invoice.
     *
     * @param string $orderUid
     *
     * @return json response
     *---------------------------------------------------------------- */

    public function printArticleDocument($projectSlug, $versionSlug, $articleSlug, CommonPostRequest $request)
    {
        if (!defined('REQUEST_FROM_EMBED_VIEW')) {
            define('REQUEST_FROM_EMBED_VIEW', false);
        }
        
        $processResponce = $this->homeEngine->prepareDocData($projectSlug, $versionSlug, $articleSlug, $request->input('lang'));

        $processResponce['showPrintView'] = true;
        return $this->loadView('home.doc-view', $processResponce);
    }

}