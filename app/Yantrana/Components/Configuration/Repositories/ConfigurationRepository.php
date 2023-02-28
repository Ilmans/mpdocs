<?php

/*
* ConfigurationRepository.php - Repository file
*
* This file is part of the Configuration component.
*-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\Configuration\Repositories;

use App\Yantrana\Base\BaseRepository;
use App\Yantrana\Components\Configuration\Models\Configuration as ConfigurationModel;
use App\Yantrana\Components\Configuration\Blueprints\ConfigurationRepositoryBlueprint;

class ConfigurationRepository extends BaseRepository implements ConfigurationRepositoryBlueprint
{
    /**
     * @var ConfigurationModel $configurationModel - Configuration Model
     */
    protected $configurationModel;

    /**
      * Constructor
      *
      * @param ConfigurationModel $configurationModel - Configuration Model
      *
      * @return void
      *-----------------------------------------------------------------------*/

    public function __construct(ConfigurationModel $configurationModel)
    {
        $this->configurationModel = $configurationModel;
    }


    /**
      * Fetch all or selected config data
      *
      * @return array
      *-----------------------------------------------------------------------*/

    public function fetch()
    {
        return $this->configurationModel->all();
    }

    /**
      * Fetch all or selected config data
      *
      * @param array $configNames
      *
      * @return array
      *-----------------------------------------------------------------------*/

    public function fetchConfiguration($configNames)
    {
        return $this->configurationModel
       				->whereIn('name', $configNames)
        			->get();
    }

    /**
     * Prepare value according to datatype
     *
     * @param string $data
     * @param string $string
     *
     *---------------------------------------------------------------- */
    protected function prepareValue($settingName, $settingValue)
    {
        $configArray = config('__settings.items');

        $settingKeyDatatype = $configArray[$settingName]['data_type'];

        if ($settingKeyDatatype == 4) { // JSON
            return json_encode($settingValue);
        }
        
        return $settingValue;
    }


    /**
     * get require data for form.
     *
     * @param string $data
     * @param string $string
     *---------------------------------------------------------------- */
    protected function getDatatype($string)
    {
        $configArray = config('__settings.items');

        return $configArray[$string]['data_type'];
    }

    /**
     * Add new configuration object.
     *
     * @param array $input
     *
     * @return bool
     *---------------------------------------------------------------- */
    public function add($input)
    {
        $configArray = config('__settings.items');

        $insertData = [];

        foreach ($input as $key => $value) {
            if (array_key_exists($key, $configArray)) {
                $insertData[] = [
                    'name'      => $key,
                    'value'     => $this->prepareValue($key, $value),
                    'data_type' => $this->getDatatype($key)
                ];
            }
        }

        if ($this->configurationModel->prepareAndInsert($insertData)) {
            return true;
        }

        return false;
    }


    /**
     * Update configuration.
     *
     * @param array $input
     *
     * @return bool
     *---------------------------------------------------------------- */
    public function update($configurations, $inputs)
    {
        $configArray = config('__settings.items');
        
        $existingConfigurations = $configurations->pluck('name')->all();
        $inputSettingName       = $updateData = [];
        
        // get input & check if the input already available in array then
        // set in array
        
        foreach ($inputs as $key => $input) {
            if (!__isEmpty($existingConfigurations)) {
                if (array_key_exists($key, $configArray) and in_array($key, $existingConfigurations)) {
                    $updateData[] = [
                        'name'   => $key,
                        'value' => $this->prepareValue($key, $input),
                        'data_type' => $this->getDatatype($key)
                    ];
                }
            } else {
                $updateData[] = [
                    'name'   => $key,
                    'value' => $this->prepareValue($key, $input),
                    'data_type' => $this->getDatatype($key)
                ];
            }

            $inputSettingName[] = $key;
        }

        // Create new array for setting name
        $settingName = [];

        foreach ($configurations as $setting) {
            $settingName[] = $setting['name'];
        }


        $getNewConfigName = [];

        // Check input setting exist
        // compare database name and input name and return differences
        if (!empty($inputSettingName)) {
            $newConfigurationKey = array_diff($inputSettingName, $settingName);

            if (!empty($newConfigurationKey)) {
                foreach ($newConfigurationKey as $newConfigKey) {
                    if (array_key_exists($newConfigKey, $configArray)) {
                        $getNewConfigName[] = $newConfigKey;
                    }
                }
            }
        }

        $insertData = [];
        // Check if input exist and check input data exist in new configuration name
        if (!__isEmpty($inputs)) {
            foreach ($inputs as $inputConfigKey => $inputConfigValue) {
                if (in_array($inputConfigKey, $getNewConfigName)) {
                    $insertData[] = [
                        'name'   => $inputConfigKey,
                        'value' => $this->prepareValue($inputConfigKey, $inputConfigValue),
                        'data_type' => $this->getDatatype($inputConfigKey)
                    ];
                }
            }
        }

        // if insert data exist then insert new value in database
        if (!__isEmpty($insertData)) {
            if ($this->configurationModel->prepareAndInsert($insertData)) {
                return true;
            }
        }

        // if existing setting exist then update data
        if (!__isEmpty($existingConfigurations)) {
            if ($this->configurationModel->batchUpdate($updateData, 'name')) {
                return true;
            }
        } else {
            if ($this->configurationModel->prepareAndInsert($updateData)) {
                return true;
            }
        }

        return false;
    }
}
