<?php

namespace App\Http\ViewComposers;

use Illuminate\View\View;
use App\Yantrana\Components\Home\HomeEngine;

class PublicMasterComposer
{
    /**
     * The menuEngine.
     *
     * @var MenuEngine
     */
    protected $homeEngine;

    /**
     * Create a new menu composer.
     *
     * @param HomeEngine  $homeEngine
     * @return void
     */
    public function __construct(HomeEngine $homeEngine)
    {
        // Dependencies automatically resolved by service container...
        $this->homeEngine = $homeEngine;
    }

    /**
     * Bind data to the view.
     *
     * @param  View  $view
     * @return void
     */
    public function compose(View $view)
    { 	
        $view->with('viewComposer', [
            'content' => $this->homeEngine->prepareData()
        ]);
    }
}