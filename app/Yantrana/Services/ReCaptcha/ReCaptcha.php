<?php

namespace App\Yantrana\Services\ReCaptcha;

use GuzzleHttp\Client;

class ReCaptcha
{
    public function validate($attribute, $value, $parameters, $validator) {
    
        $client = new Client();
    
        $response = $client->post('https://www.google.com/recaptcha/api/siteverify', [
        	'form_params'=> [
                'secret' 	=> 	configItem('recaptcha.secret_key'),
                'response' 	=>	$value
            ]
        ]);
    
        $body = json_decode((string)$response->getBody());

        return $body->success;
    }

}

/*  ref: https://m.dotdev.co/google-recaptcha-integration-with-laravel-ad0f30b52d7d   */