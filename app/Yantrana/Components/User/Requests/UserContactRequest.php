<?php
/*
* UserContactRequest.php - Request file
*
* This file is part of the User component.
*-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\User\Requests;

use App\Yantrana\Base\BaseRequest;

class UserContactRequest extends BaseRequest
{
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
     * Get the validation rules that apply to the user register request.
     *
     * @return bool
     *-----------------------------------------------------------------------*/
    public function rules()
    {

        return [
			'fullname'	=> 'required|min:2',
            'email'		=> 'required|email',
            'subject'	=> 'required|min:6|max:255',
            'message'	=> 'required|min:6|max:500'
        ];
    }
 
}
