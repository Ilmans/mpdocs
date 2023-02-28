<?php
/*
* UserLoginRequest.php - Request file
*
* This file is part of the User component.
*-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\User\Requests;

use App\Yantrana\Base\BaseRequest;
use Illuminate\Http\Request;

class UserUpdatePasswordRequest extends BaseRequest
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
     * Get the validation rules that apply to the user update password request.
     *
     * @return bool
     *-----------------------------------------------------------------------*/
    public function rules()
    {
        $rules = [
            'new_password' => 'required|min:6|max:30|different:current_password',
            'new_password_confirmation' => 'required|min:6|max:30|same:new_password',
        ];

        if (!__isEmpty(Request::get('current_password'))) {
            $rules['current_password'] = 'required|min:6|max:30';
        }

        return $rules;
    }
}
