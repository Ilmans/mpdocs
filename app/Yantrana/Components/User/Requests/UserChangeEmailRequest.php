<?php
/*
* UserChangeEmailRequest.php - Request file
*
* This file is part of the User component.
*-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\User\Requests;

use App\Yantrana\Base\BaseRequest;
use Illuminate\Http\Request;

class UserChangeEmailRequest extends BaseRequest
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
     * Get the validation rules that apply to the user change email request.
     *
     * @return bool
     *-----------------------------------------------------------------------*/
    public function rules()
    {
        $rules =  [
            'new_email'  => 'required|email|unique_email'
        ];

        if (!__isEmpty(Request::get('current_password'))) {
            $rules['current_password'] = 'required|min:6';
        }
       
        return $rules;
    }
}
