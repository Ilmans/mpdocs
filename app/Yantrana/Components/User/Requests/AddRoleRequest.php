<?php
/*
* AddRoleRequest.php - Request file
*
* This file is part of the User component.
*-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\User\Requests;

use App\Yantrana\Base\BaseRequest;
use Illuminate\Http\Request;

class AddRoleRequest extends BaseRequest
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
            'title' => 'required',
            'role_id' => 'required'
        ];
    }
}
