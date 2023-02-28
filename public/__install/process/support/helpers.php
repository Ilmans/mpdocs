<?php
/**
 *  LivelyWorks PHP Application Installer
 *
 * @package    EasyInstall - PHP Application Installer
 * @author     Vinod <livelyworks@gmail.com>
 * @copyright  2019 LivelyWorks. (http://livelyworks.net)
 * @link       http://livelyworks.net
 * @since      Version 1.0
 * @deprecated NA
 */
    /**
     * Update ENV file values
     * src - https://stackoverflow.com/questions/40450162/how-to-set-env-values-in-laravel-programmatically-on-the-fly
     * @return mixed
     *---------------------------------------------------------------- */
    if (!function_exists('updateEnvValue')) {
        function updateEnvValue(array $envItems, $envPath)
        {
            $str = file_get_contents($envPath);
            if (count($envItems) > 0) {
                foreach ($envItems as $envKey => $envValue) {

                    $str .= "\n"; // In case the searched variable is in the last line without \n
                    $keyPosition = strpos($str, "{$envKey}=");
                    $endOfLinePosition = strpos($str, "\n", $keyPosition);
                    $oldLine = substr($str, $keyPosition, $endOfLinePosition - $keyPosition);

                    // If key does not exist, add it
                    if (!$keyPosition || !$endOfLinePosition || !$oldLine) {
                        $str .= "{$envKey}={$envValue}\n";
                    } else {
                        $str = str_replace($oldLine, "{$envKey}={$envValue}", $str);
                    }
                }
            }
            $str = substr($str, 0, -1);
            if (!file_put_contents($envPath, rtrim($str))) return false;
            return true;
        }
    }
/**
 * Get Config Item
 *
 * @return mixed
 *---------------------------------------------------------------- */
if (!function_exists('configItem')) {
    function configItem($item = null) {

        if (!defined('LW_INSTALLER')) {
            define('LW_INSTALLER', true);
        }
        $configFile = __DIR__.'/../../../../app/Yantrana/Support/install/config.php';
        if(file_exists($configFile )) {
            $configItems = require $configFile;
        } else {
            $configItems = require './../install-config.php';
        }

        if($item) {
            
            if(isset($configItems[$item])) {
                return $configItems[$item];
            }

            return null;
        }
        return $configItems;
    }
}
/**
 * Get ENV items from Config file
 *
 * @return mixed
 *---------------------------------------------------------------- */
if (!function_exists('envItems')) {
    function envItems($item = null) {
        $envItems = configItem('envItems');

        if(empty($envItems)) {
            return [];
        }

        foreach ($envItems as $envItemKey => $envItemConf)
        {
            $envItems[$envItemKey] = array_merge([
                'type' => 'text',
                'required' => false,
                'item_type' => '',
                'value' => '',
                'note' => '',
                'placeholder' => ''
            ], $envItemConf);
        }

        if($item) {
            if(isset($envItems[$item])) {
                return $envItems[$item];
            }
            return null;
        }
        return $envItems;
    }
}
/**
 * Clean input data
 *
 * @return mixed
 *---------------------------------------------------------------- */
if (!function_exists('cleanData')) {
    function cleanData($data) {
        $data = trim($data);
        $data = stripslashes($data);
        // $data = htmlspecialchars($data);
        $data = filter_var($data, FILTER_SANITIZE_STRING);
        return $data;
    }
}