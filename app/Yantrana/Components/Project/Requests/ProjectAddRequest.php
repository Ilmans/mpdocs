<?php            
/*
* ProjectListRequest.php - Request file
*
* This file is part of the Project component.
*-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\Project\Requests;

use App\Yantrana\Base\BaseRequest;

class ProjectAddRequest extends BaseRequest 
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
     * Loosely sanitize fields.
     *------------------------------------------------------------------------ */
    protected $looseSanitizationFields = [
    	'short_description' => true, 
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
            "slug" => "required|slug|unique:projects,slug",
            "name" => "required|max:150|unique:projects,name",
            "short_description" => "nullable|min:10|max:500",   
            "type" => "required",
            "project_languages" => 'required',
            "primary_language" => "required"
        ];
    }
}