<?php            
/*
* LanguageListRequest.php - Request file
*
* This file is part of the Language component.
*-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\Language\Requests;

use App\Yantrana\Base\BaseRequest;

class LanguageEditRequest extends BaseRequest 
{   
    /**
      * Set if you need form request secured.
      *------------------------------------------------------------------------ */
    protected $securedForm = true;

    /**
     * Unsecured/Unencrypted form fields.
     *------------------------------------------------------------------------ */
    protected $unsecuredFields = ['name'];
    
        
   /**
     * Loosely sanitize fields.
     *------------------------------------------------------------------------ */
    protected $looseSanitizationFields = [];
    
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
            "is_rtl" => "required",
        ];
    }
}