<?php
/*
* UserLoginRequest.php - Request file
*
* This file is part of the User component.
*-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\User\Requests;

use App\Yantrana\Base\BaseRequest;

class UserForgotPasswordRequest extends BaseRequest
{
    /**
     * Secure form.
     *------------------------------------------------------------------------ */
    protected $securedForm = true;

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
     * Get the validation rules that apply to the user forgot password request.
     *
     * @return bool
     *-----------------------------------------------------------------------*/
    public function rules()
    {
        return  [
            'usernameOrEmail' => 'required',
        ];
    }
}
