<?php
/*
* MediaRequest.php - Request file
*
* This file is part of the Media component.
*-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\Media\Requests;

use App\Yantrana\Base\BaseRequest;

class MediaRequest extends BaseRequest
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
     * Get the validation rules that apply to the add author client post request.
     *
     * @return bool
     *-----------------------------------------------------------------------*/
    public function rules()
    {
        return [];
    }
}
