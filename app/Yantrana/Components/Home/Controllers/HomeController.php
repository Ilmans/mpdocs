<?php
/*
* HomeController.php - Controller file
*
* This file is part of the Home component.
*-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\Home\Controllers;

use App\Yantrana\Base\BaseController;
use App\Yantrana\Components\Home\HomeEngine;
use Illuminate\Http\Request;

class HomeController extends BaseController 
{    
    /**
     * @var  HomeEngine $homeEngine - Home Engine
     */
    protected $homeEngine;

    /**
      * Constructor
      *
      * @param  HomeEngine $homeEngine - Home Engine
      *
      * @return  void
      *-----------------------------------------------------------------------*/

    function __construct(HomeEngine $homeEngine)
    {
        $this->homeEngine = $homeEngine;
    }


    /**
     * Get not found error view template.
     *---------------------------------------------------------------- */
    public function publicIndex()
    {   
        return $this->loadPublicView('home.list', $this->homeEngine->prepareData());
    }

    /**
     * Get not found error view template.
     *---------------------------------------------------------------- */
    public function readSearchData(Request $request)
    {   
        return $this->homeEngine->prepareSearchData(
                                            $request->input('search_term'),
                                            $request->input('lang'),
                                            $request->input('ver'),
                                            $request->input('project')
                                        );
        
    }

    /*
    *
    * Get Document view 
    *---------------------------------------------------------------- */

    public function showDocView(Request $request, $projectSlug, $versionSlug = null, $articleSlug = null)
    {  
        if (!defined('REQUEST_FROM_EMBED_VIEW')) {
            define('REQUEST_FROM_EMBED_VIEW', false);
        }
        
        $processResponse =  $this->homeEngine
                                ->prepareDocData(
                                    $projectSlug, $versionSlug, $articleSlug, $request->input('lang')
                                );
                              
        return $this->loadArticleView('home.doc-view', $processResponse);
    }
    

    /*
    *
    * Get Document view 
    *---------------------------------------------------------------- */

    public function showDocEmbedView($projectSlug, $versionSlug = null, $articleSlug = null, Request $request)
    { 
        if (!defined('REQUEST_FROM_EMBED_VIEW')) {
            define('REQUEST_FROM_EMBED_VIEW', true);
        }

        $processResponse =  $this->homeEngine
                                ->prepareDocData(
                                    $projectSlug, $versionSlug, $articleSlug, $request->input('lang')
                                );

        $processResponse['embedDataUrl'] = route('embed.doc.view', [
                                                'projectSlug' => $projectSlug,
                                                'versionSlug' => $versionSlug,
                                                'articleSlug' => $articleSlug
                                            ]);
        
        return $this->loadArticleView('home.doc-view', $processResponse);
    }
}