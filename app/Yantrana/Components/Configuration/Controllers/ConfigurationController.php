<?php

/*
* ConfigurationController.php - Controller file
*
* This file is part of the Configuration component.
*-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\Configuration\Controllers;

use App\Yantrana\Base\BaseController;
use App\Yantrana\Components\Configuration\ConfigurationEngine;
use App\Yantrana\Components\Configuration\Requests\ConfigurationRequest;

class ConfigurationController extends BaseController
{
    /**
     * @var ConfigurationEngine $configurationEngine - Configuration Engine
     */
    protected $configurationEngine;

    /**
      * Constructor
      *
      * @param ConfigurationEngine $configurationEngine - Configuration Engine
      *
      * @return void
      *-----------------------------------------------------------------------*/

    public function __construct(ConfigurationEngine $configurationEngine)
    {
        $this->configurationEngine = $configurationEngine;
    }


    /**
     * Handle support data.
     *
     * @param string $formType
     *
     * @return json object
     *---------------------------------------------------------------- */
    public function getSupportData($formType)
    {
    	 
        $processReaction = $this->configurationEngine
                                ->prepareSupportData($formType);

        return __processResponse($processReaction, [], $processReaction['data']);
    }


    /**
     * Handle edit config request.
     *
     * @param string $formType
     * @param object ConfigurationRequest $request
     *
     * @return json object
     *---------------------------------------------------------------- */
    public function process(ConfigurationRequest $request, $formType)
    {
        $processReaction = $this->configurationEngine
                                ->processEditOrStore($request->all(), $formType);

        return __processResponse($processReaction, [], [], true);
    }
}
