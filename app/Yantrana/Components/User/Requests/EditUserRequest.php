<?php
/*
* EditUserRequest.php - Request file
*
* This file is part of the User component.
*-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\User\Requests;

use Illuminate\Validation\Rule;
use App\Yantrana\Base\BaseRequest;

class EditUserRequest extends BaseRequest
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
        $userId = $this->route('userId');

        return [
            'first_name'            => 'required|min:2|max:45',
            'last_name'             => 'required|min:2|max:45',
            'role'                  => 'sometimes|required',
            'username'              => 'required|min:2|max:45|'.Rule::unique('users')->ignore($userId, '_id')
        ];
    }
}
