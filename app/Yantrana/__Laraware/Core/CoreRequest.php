<?php

namespace App\Yantrana\__Laraware\Core;

/*
 * Core Request - 1.1.9 - 04 JUL 2018
 * 
 * core request for Laravel applications
 *
 *
 * Dependencies:
 * 
 * Laravel     5.0 +     - http://laravel.com
 * 
 *
 *--------------------------------------------------------------------------- */

use Illuminate\Validation\Factory as ValidatorFactory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use YesSecurity;

abstract class CoreRequest extends FormRequest
{
    /**
     * Set if you need form request secured.
     *------------------------------------------------------------------------ */
    protected $securedForm = false;

    /**
     * Unsecured/Unencrypted form fields.
     *------------------------------------------------------------------------ */
    protected $unsecuredFields = [];

    /**
     * perform sanitization on input or not.
     *------------------------------------------------------------------------ */
    protected $sanitization = true;

    /**
     * Loosely sanitize fields.
     *------------------------------------------------------------------------ */
    protected $looseSanitizationFields = [];

    /**
     * Default Santization allowed tags.
     *------------------------------------------------------------------------ */
    protected $defaultSanitizationAllowedTags = '<p><br><br/><img><img/><ul><ol>
                    <li><strong><a><small><blockquote><em><h1><h2><h3><h4><h5>
                    <hr><hr/><address><dd><table><td><tr><th><thead><tbody><dl>
                    <dt><div><span>';


    /**
     * Call before validation process
     * @example uses 
     protected function processBefore()
    {
        // call existing if any
        parent::processBefore();
        // write your input manipulation like $inputData = $this->all();        
        // replace input array
        $this->replace($inputData);
    }
     * @return void
     *------------------------------------------------------------------------ */
    protected function processBefore()
    {
    }

    /**
     * Modify validator.
     *
     * @param  $factory 
     *
     * @return array
     *------------------------------------------------------------------------ */
    public function validator(ValidatorFactory $factory)
    {
        // normalize/decrypt form fields
        if ($this->securedForm === true) {
            $this->normalizeEncryptedInput();
        }

        // sanitization
        if ($this->sanitization === true) {
            $this->sanitizeInputs($this->input());
        }

        $this->processBefore();

        return $factory->make($this->input(), $this->rules(), $this->messages());
    }

    /**
     * normalize/decrypt form fields.
     *------------------------------------------------------------------------ */
    protected function normalizeEncryptedInput()
    {
        $allInputs = $this->input();
        $nullItemValueCounts = 0;

        foreach ($allInputs as $key => $value) {

            $decryptedKey = YesSecurity::decryptRSA($key);
            $encryptedKey = null;

            if ($decryptedKey) {
                $encryptedKey = $key;
                $key = $decryptedKey;
            }

            if (
                in_array($key, $this->unsecuredFields) === false
                and is_array($value) === false
            ) {

                $allInputs[$key] = YesSecurity::decryptLongRSA($value);

                unset($allInputs[$encryptedKey]);
            }

            if ($allInputs[$key] === 'true') {
                $allInputs[$key] = true;
            }

            if ($allInputs[$key] === 'false') {
                $allInputs[$key] = false;
            }

            // if found null
            if ($allInputs[$key] === null) {
                ++$nullItemValueCounts;
            }
        }

        // check if decryption fails & update acordingly
        if (count($allInputs) === $nullItemValueCounts) {
            $message = __('Invalid Request Inputs ... !!');

            if ($this->ajax()) { // if its an ajax request send response

                echo json_encode(__response([
                    'message' => $message,
                ], 20));
                // finish execution
                die();
            } else { // if not an ajax request finish execution
                die($message);
            }
        }

        unset($allInputs[YesSecurity::getFormSecurityID()]);

        $this->replace($allInputs);
    }

    /**
     * Sanitize Inputs.
     *------------------------------------------------------------------------ */
    protected function sanitizeInputs(array $inputs)
    {
        foreach ($inputs as $key => $value) {
            if (is_array($value)) {
                $this->sanitizeInputs($value);
            } elseif (is_string($value)) {

                // check if some tags are allowed
                // if yes concat with defaults
                if (array_key_exists($key, $this->looseSanitizationFields)) {
                    if (is_string($this->looseSanitizationFields[$key])) {
                        // strip tags except default & required allowed
                        $inputs[$key] = strip_tags($value, $this->defaultSanitizationAllowedTags
                            . $this->looseSanitizationFields[$key]);
                    } elseif ($this->looseSanitizationFields[$key] === true) {
                        $inputs[$key] = $value;
                    }
                } else {

                    // strip all tags
                    $inputs[$key] = strip_tags($value);
                }
            }
        }

        // update inputs
        $this->replace($inputs);
    }
}
