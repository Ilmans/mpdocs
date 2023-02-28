<?php
/*
* UserChangePasswordRequest.php - Request file
*
* This file is part of the User component.
*-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\User\Requests;

use App\Yantrana\Base\BaseRequest;

class UserChangePasswordRequest extends BaseRequest
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
     * Get the validation rules that apply to the admin change password of user.
     *
     * @return bool
     *-----------------------------------------------------------------------*/
    public function rules()
    {
        return  [
            'new_password' => 'required|min:6|max:255',
            'new_password_confirmation' => 'required|min:6|max:255|same:new_password',
        ];
    }
}
