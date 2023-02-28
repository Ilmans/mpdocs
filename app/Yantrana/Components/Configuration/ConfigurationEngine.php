<?php

/*
* ConfigurationEngine.php - Main component file
*
* This file is part of the Configuration component.
*-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\Configuration;

use App\Yantrana\Base\BaseEngine;

use App\Yantrana\Components\Configuration\Repositories\ConfigurationRepository;
use App\Yantrana\Components\Configuration\Blueprints\ConfigurationEngineBlueprint;
use App\Yantrana\Components\Media\MediaEngine;

class ConfigurationEngine extends BaseEngine implements ConfigurationEngineBlueprint
{
    /**
     * @var ConfigurationRepository $configurationRepository - Configuration Repository
     */
    protected $configurationRepository;

    /**
     * @var MediaEngine - Media Engine
     */
    protected $mediaEngine;

    /**
      * Constructor
      *
      * @param ConfigurationRepository $configurationRepository - Configuration Repository
      * @param MediaEngine              $mediaEngine              - Media Engine
      * @return void
      *-----------------------------------------------------------------------*/

    public function __construct(ConfigurationRepository $configurationRepository, MediaEngine $mediaEngine)
    {
        $this->configurationRepository = $configurationRepository;
        $this->mediaEngine             = $mediaEngine;
    }


    /**
     * get require data for form.
     *
     * @param string $data
     * @param string $string
     *---------------------------------------------------------------- */
    protected function checkIsEmpty($data, $string)
    {
        return isset($data[$string]) ? $data[$string] : '';
    }


    /**
     * check the data is true or false.
     *
     *  @param array $data
     *  @param string $string
     *---------------------------------------------------------------- */
    protected function checkIsValid($data, $string)
    {
        return isset($data[$string]) ? $data[$string] : true;
    }

    /**
     * check the data is true or false.
     *
     *  @param array $data
     *  @param string $string
     *---------------------------------------------------------------- */
    protected function makeItBool($data, $string, $default = false)
    {
        if (__ifIsset($data[$string])) {
            return (bool) $data[$string];
        }

        return $default;
    }

    public function getDataType($datTypeId, $value)
    {
        switch ($datTypeId) {
            case 1:
                return (string) $value;
                break;
            case 2:
                return (bool) $value;
                break;
            case 3:
                return (int) $value;
                break;
            case 4:
                return (!__isEmpty($value)) ? json_decode($value, true) : [];
                break;
            default:
                return $value;
        }
    }

    /**
     * cast each and every value of configuration table.
     *
     * @param array $dataType
     *
     *---------------------------------------------------------------- */
    public function castValue($dataArray)
    {
        $configArray = [];

        $configurationNames = config('__settings.items');

        foreach ($dataArray as $key => $data) {
            $datTypeId = $configurationNames[$key]['data_type'];

            $configArray[$key] = $this->getDataType($datTypeId, $data);
        }
        return $configArray;
    }

    /**
     * prepare data for configuration setting if data not exist then it will get from
     * config settings
     *
     *  @param array $selectedData
     *  @param array $dbArray
     *
     *  @param string $string
     *---------------------------------------------------------------- */
    protected function prepareConfigurationData($selectedData, $dbArray)
    {
        $configArray = config('__settings.items');
       
        if (!__isEmpty($selectedData)) {
            foreach ($selectedData as $key => $selectValue) {
                if (array_key_exists($selectValue, $dbArray) == false) {
                    $dbArray[$selectValue] = $configArray[$selectValue]['default'];
                }
            }
        }

        return $this->castValue($dbArray);
    }

    /**
     * Get Placeholders list
     *
     *  @param array $selectedData
     *
     *  @param string $string
     *---------------------------------------------------------------- */
    protected function getPlaceholders($selectedData)
    {
        $configArray = config('__settings.items');

        $placeHolderArray = [];

        if (!__isEmpty($selectedData)) {
            foreach ($selectedData as $key => $selectValue) {
                if (array_key_exists($selectValue, $configArray)) {
                    if (array_has($configArray[$selectValue], 'placeholder')) {
                        $placeHolderArray[$selectValue] =  $configArray[$selectValue]['placeholder'];
                    }
                }
            }
        }

        return $placeHolderArray;
    }

    /**
     * get only requested data on form
     *
     *  @param string $requestType
     *
     *  @param string $string
     *---------------------------------------------------------------- */
    protected function getRequestedData($selectedData, $requestType)
    {
        $configurationCollection = $this->configurationRepository->fetch();

        $configurationArray = $configurationData = [];

        $isLiveStripeKeysExist = false;
        $isTestingStripeKeysExist = false;

        if (!__isEmpty($configurationCollection)) {
            foreach ($configurationCollection as $configuration) {
                $name   = $configuration->name;
                $value  = $configuration->value;

                // Get logo image URL
                if ($name == 'logo_image') {
                    $configurationData['logoURL'] = getConfigurationSettings('logo_image_url');
                }

                // Get logo image URL
                if ($name == 'favicon_image') {
                    $configurationData['faviconURL'] = getConfigurationSettings('favicon_image_url');
                }

                // when $selectedData is empty then get all configuration
                if (__isEmpty($selectedData)) {
                    $configurationArray[$name] = $value;
                } else {

                    // when $selectedData is not empty get selected configuration
                    if (in_array($name, $selectedData)) {
                        $configurationArray[$name] = $value;
                    }
                }
            }

        }

        $configurationArray = $this->prepareConfigurationData(
                                                        $selectedData,
                                                        $configurationArray
                                                    );
 
        // Check page type and get data as per request type
        if ($requestType == 1) { // general
        	$configArray = config('__settings.items');
            
            $configurationArray['logoURL']          = __ifIsset($configurationData['logoURL']) ? $configurationData['logoURL'] : '';

            $configurationArray['faviconURL']       = __ifIsset($configurationData['faviconURL']) ? $configurationData['faviconURL'] : '';

			if ($configArray['header_background_color'] and $configArray['header_text_link_color']) {
				$configurationArray['default_header_background_color']  = $configArray['header_background_color']['default'];
				$configurationArray['default_header_text_link_color'] = $configArray['header_text_link_color']['default'];
			}
            $configurationArray['theme_colors'] = configItem('theme_colors');
        }
 
        return $configurationArray;
    }

    /**
      * get the edit support data
      *
      * @param string $requestType
      *
      * @return void
      *-----------------------------------------------------------------------*/

    public function prepareSupportData($requestType)
    {
        $selectedData = [];

        // set required keys
        if ($requestType == 1) { // general

            $selectedData = [
                                'name',
                                'logo_image',
                                'favicon_image',
                                'header_background_color',
                                'header_text_link_color',
                                'business_email',
                                'contact_email',
								'show_captcha',
                                'footer_text',
                                'enable_credit_info',
                                'recaptcha_site_key',
                                'recaptcha_secret_key',
                                'enable_login_attempt',
                                'restrict_user_email_update'
                            ];
        }
        
        return $this->engineReaction(1, [
            'configuration' => $this->getRequestedData($selectedData, $requestType),
            'placeholders'  => $this->getPlaceholders($selectedData)
        ]);
    }


    /**
      * process the edit or add config items
      *
      * @param array  $inputs
      * @param string $requestType
      *
      * @return void
      *-----------------------------------------------------------------------*/

    public function processEditOrStore($inputs, $requestType)
    {	 
        $reactionCode =  $this->configurationRepository
                             ->processTransaction(function () use ($inputs, $requestType) {

            // fetch all configuration array
            $configurations = $this->configurationRepository->fetch();
            
            $logoImage = __ifIsset($inputs['logo_image'], $inputs['logo_image'], '');

            $faviconImage = __ifIsset($inputs['favicon_image'], $inputs['favicon_image'], '');
            
            $logoUpdated = false;
            $faviconUpdated = false;

            // Check if logo exist Then Process This Logo Image
            if (!__isEmpty($logoImage)) {

                // Store Logo image
                $newLogoImage = $this->mediaEngine->processLogoMedia($logoImage, 'logo_image');

                if (!__isEmpty($newLogoImage)) {
                    $inputs['logo_image'] = $newLogoImage;

                    $logoUpdated = true;
                }
            }
            
            // Check if favicon exist Then Process This Logo Image
            if (!__isEmpty($faviconImage)) {
                // Store Logo image
                $newFaviconImage = $this->mediaEngine->processFaviconMedia($faviconImage, 'favicon_image');

                if (!__isEmpty($newFaviconImage)) {
                    $inputs['favicon_image'] = $newFaviconImage;
                        $faviconUpdated = true;
                    }
                }

                // If logo not exist then remove logo image from input data
                if ($logoUpdated == false) {
                    $inputs = array_except($inputs, 'logo_image');
                }

                // If favicon not exist then remove logo image from input data
                if ($faviconUpdated == false) {
                    $inputs = array_except($inputs, 'favicon_image');
                }

                if ($requestType == 7) { // Contact
                    $inputs['contact_email'] = strtolower($inputs['contact_email']);
                }

                if ($requestType == 1) { // General
                    $inputs['business_email'] = strtolower($inputs['business_email']);
                }

                // Check if store configuration is empty
                if (__isEmpty($configurations)) {
                    if ($this->configurationRepository->add($inputs)) {
                        return $this->configurationRepository->transactionResponse(1, null, __tr('Configuration Added Successfully.'));
                    }
                
                    return $this->configurationRepository->transactionResponse(14, null, __tr('Configuration Not Added.'));
                }

                // if the item already available in database
                $configurationUpdated = $this->configurationRepository
                                             ->update($configurations, $inputs);

                $showRealodButton = false;

                $reloadRequiredItems = [
                    'logo_image',
                    'favicon_image',
                    'footer_text',
                    'enable_credit_info',
    				'recaptcha_site_key',
    				'recaptcha_secret_key',
					'header_background_color',
					'header_text_link_color'
                ];

                foreach ($reloadRequiredItems as $key => $item) {
                    if (array_has($inputs, $item)
                    and (!__isEmpty($inputs[$item]))) {
                        $showRealodButton = true;
                    }
                }
                                
                // check if the configuration object updated
                if ($configurationUpdated or $logoUpdated or $faviconUpdated) {
                    return $this->configurationRepository->transactionResponse(1, ['showRealodButton' => $showRealodButton], __tr('Configuration Updated Successfully.'));
                }

                    return $this->configurationRepository->transactionResponse(14, null, __tr('Nothing Updated.'));
            });

        return $this->engineReaction($reactionCode);
    }
}
