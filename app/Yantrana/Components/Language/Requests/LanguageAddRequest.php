<?php            
/*
* LanguageListRequest.php - Request file
*
* This file is part of the Language component.
*-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\Language\Requests;

use App\Yantrana\Base\BaseRequest;

class LanguageAddRequest extends BaseRequest 
{   
    /**
      * Set if you need form request secured.
      *------------------------------------------------------------------------ */
    protected $securedForm = true;

    /**
     * Unsecured/Unencrypted form fields.
     *------------------------------------------------------------------------ */
    protected $unsecuredFields = [
        'name',
        'code'
    ];
    
        
   /**
     * Loosely sanitize fields.
     *------------------------------------------------------------------------ */
    protected $looseSanitizationFields = [
                    'name' => '', 
                    '_id' => '',
              ];
    
    /**
      * Authorization for request.
      *
      * @return  bool
      *-----------------------------------------------------------------------*/

    public function authorize()
    {
       return true; 
    }
    
    /**
      * Validation rules.
      *
      * @return  bool
      *-----------------------------------------------------------------------*/

    public function rules()
    {   
        return [
            "name" => "required",  
            "code" => "required|unique:languages,_id|alpha_num",
            "is_rtl" => "required",
        ];
    }

    public function messages() {
    	return [
            "code.unique" => "language code already taken",
        ];
    }
}