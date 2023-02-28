<?php
/*
* InstallationVerification.php - Controller file
*-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\Installation;

use DB;
use Exception;
use Schema;
use Artisan;
use App\Yantrana\Support\Utils;

class InstallationVerification
{
    protected $projectName = 'Docsyard';
    protected $problems = [];
    protected $defaultKey = 'base64:ZeeEOXG3/jMoO/C6m3e8fl2GkndivS7pAsQUMupqxuw=';
    protected $styleLines = "padding:20px; border-bottom:1px dotted #ddd;margin:16px;";

    /**
     * Constructor.
     *
     *-----------------------------------------------------------------------*/
    public function __construct()
    {
        if (!file_exists(\public_path('install.php'))) {
            abort(404);
        }
    }

    /**
     * Verify the installation
     *
     * @return void
     *---------------------------------------------------------------- */
    public function verify()
    {
        echo '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><title>' . $this->projectName . ' Installation Verification</title>

                <style>
                    .lw-button {
                    background-color: #64c11f; /* Green */
                    border: none;
                    color: white;
                    padding: 8px 10px;
                    text-align: center;
                    text-decoration: none;
                    display: inline-block;
                    font-size: 16px;
                    margin: 4px 2px;
                    cursor: pointer;
                    border-radius: 5px;
                }
                </style>

                </head><body style="text-align:center;">
            ';

        echo "<h2>" . $this->projectName . " Installation Verification!!</h2>";

        try {
            Artisan::call('cache:clear');
            if (!env('APP_KEY') or (env('APP_KEY') === $this->defaultKey)) {
                Artisan::call('key:generate');
                echo "<div style='$this->styleLines'>✓ New <strong style='color:green'>API KEY</strong> Generated</div>";
                $generatedKeys = $this->generateFormEncryptionKeys();
                // store password
                updateCreateArrayFileItem(base_path('user-tech-config'), 'form_encryption.default_rsa_passphrase', $generatedKeys['password'], [
                    'prepend_comment' => '/*
    IF you want to change some settings in config/__tech.php then do not edit that file you should use this file.
    Then you should add those overwritten settings in the following array
*/'
                ]);
                // store public key
                updateCreateArrayFileItem(base_path('user-tech-config'), 'form_encryption.default_rsa_public_key', $generatedKeys['publicKey'], [
                    'prepend_comment' => '/*
    IF you want to change some settings in config/__tech.php then do not edit that file you should use this file.
    Then you should add those overwritten settings in the following array
*/'
                ]);
                // store private key
                updateCreateArrayFileItem(base_path('user-tech-config'), 'form_encryption.default_rsa_private_key', $generatedKeys['privateKey'], [
                    'prepend_comment' => '/*
    IF you want to change some settings in config/__tech.php then do not edit that file you should use this file.
    Then you should add those overwritten settings in the following array
*/'
                ]);
                echo "<div style='$this->styleLines'>✓ New <strong style='color:green'>Form Encryption Keys</strong> Generated</div>";
            } else {
                echo "<div style='$this->styleLines'>✓ <strong style='color:green'>API KEY</strong> exists</div>";
            }
        } catch (Exception $e) {
            echo "<div style='$this->styleLines color:red'>" . $e->getMessage() . "</div>";
            $this->problems[] = 'env_keys_generate';
        }

        if (empty($this->problems)) {
            try {
                if (env('APP_ENV') != 'production') {
                    $this->updateEnvValue('APP_ENV', 'production');
                    echo "<div style='$this->styleLines'>✓ New <strong style='color:green'>APP_ENV</strong> set to production</div>";
                }
                if (env('APP_DEBUG') != false) {
                    $this->updateEnvValue('APP_DEBUG', false);
                    echo "<div style='$this->styleLines'>✓ New <strong style='color:green'>APP_DEBUG</strong> set to false</div>";
                }
                echo "<div style='text-align:center;'><h1 style='color:green'>Congratulation!! All seems to be GOOD!!<br></h1>You can proceed, <h3>For security purposes, please delete <strong style='color:red;'>public/install.php</strong> file & <strong style='color:red;'>public/__install</strong> folder</h3></div><br><br> <div style='color:gray;' >You need use credentials from <strong>cred.txt</strong> to login as admin, file provided with installation</div>";
                echo "<br><br><a class='lw-button' href='" . route('manage.app') . "'>Go to login</a>";
            } catch (Exception $e) {

                echo "<div style='$this->styleLines color:red'>" . $e->getMessage() . "</div>";
                $this->problems[] = 'env_update';

                echo "<div style='text-align:center;'><h1 style='color:red'>Ooooops .... Please fix highlighted issues in RED above.</h1></div>";
            }
        } else {
            echo "<div style='text-align:center;'><h1 style='color:red'>Ooooops .... Please fix highlighted issues in RED above.</h1></div>";
        }

        echo '</body></html>';
    }

    /**
     * Update __tech config item file
     *
     * @return void
     *---------------------------------------------------------------- */
    protected function updateTechConfigItem($itemName, $itemValue)
    {
        return file_put_contents(config_path('__tech.php'), str_replace(
            config('__tech.form_encryption.' . $itemName),
            $itemValue,
            file_get_contents(config_path('__tech.php'))
        ));
    }

    /**
     * Generate Keys for Form Encryption
     *
     * @return void
     *---------------------------------------------------------------- */
    protected function generateFormEncryptionKeys()
    {
        $rsa = new \phpseclib\Crypt\RSA();
        $password = Utils::generateStrongPassword(43);

        $rsa->comment = $this->projectName . " YesSecurity";
        //$rsa->setPublicKeyFormat(6);
        $rsa->setPassword($password);

        $keysCreated = $rsa->createKey(2048);

        return [
            'publicKey'  => $keysCreated['publickey'],
            'privateKey' => $keysCreated['privatekey'],
            'password'  => $password
        ];
    }

    /**
     * Update ENV file values
     * src - https://stackoverflow.com/questions/40450162/how-to-set-env-values-in-laravel-programmatically-on-the-fly
     * @return void
     *---------------------------------------------------------------- */
    protected function updateEnvValue($envKey, $envValue)
    {
        $envFile = app()->environmentFilePath();
        $str = file_get_contents($envFile);

        //$oldValue = strtok($str, "{$envKey}=");
        $oldValue = env($envKey);

        if ($oldValue === true) {
            $oldValue = 'true';
        }

        if ($oldValue === false) {
            $oldValue = 'false';
        }

        if ($envValue === true) {
            $envValue = 'true';
        }

        if ($envValue === false) {
            $envValue = 'false';
        }

        $str = str_replace("{$envKey}={$oldValue}", "{$envKey}={$envValue}", $str);
        //$str = str_replace("{$envKey}={$oldValue}", "{$envKey}={$envValue}\n", $str);

        $fp = fopen($envFile, 'w');
        fwrite($fp, $str);
        fclose($fp);
    }
}
