<?php
/*
* UserLoginRequest.php - Request file
*
* This file is part of the User component.
*-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\User\Requests;

use App\Yantrana\Base\BaseRequest;
use App\Yantrana\Components\User\UserEngine;

class UserLoginRequest extends BaseRequest
{
    /**
     * Secure form.
     *------------------------------------------------------------------------ */
    protected $securedForm = true;

    /**
     * Constructor.
     *
     * @param UserEngine $userEngine - User Engine
     *-----------------------------------------------------------------------*/
    public function __construct(UserEngine $userEngine)
    {
        $this->userEngine = $userEngine;
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     *-----------------------------------------------------------------------*/
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the user login request.
     *
     * @return bool
     *-----------------------------------------------------------------------*/
    public function rules()
    {
        if ($this->userEngine->showCaptcha() && getConfigurationSettings('enable_login_attempt') == true && configItem('recaptcha.site_key')) {
            $rules = [
	            'emailOrUsername' => 'required',
	            'password' 	      => 'required|min:6',
	            "recaptcha" 		=> "required|recaptcha",
	        ];
        } else {
        	$rules = [
	            'emailOrUsername' => 'required',
	            'password' 	      => 'required|min:6',
	        ];
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'recaptcha.recaptcha' => 'The :attribute is not valid.',
        ];
    }
}
