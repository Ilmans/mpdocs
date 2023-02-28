<?php            
/*
* ProjectListRequest.php - Request file
*
* This file is part of the Project component.
*-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\Project\Requests;

use App\Yantrana\Base\BaseRequest;
use Illuminate\Validation\Rule;

class ProjectEditRequest extends BaseRequest 
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
    	$projectIdOrUid = $this->route('projectIdOrUid');

        return [
            "slug" => [
                "required", "slug",
                Rule::unique('projects', 'slug')->ignore($projectIdOrUid, '_uid') 
            ],
            "name" => [ 
                'required', 'max:150', Rule::unique('projects', 'name')->ignore($projectIdOrUid, '_uid') 
            ],
            "short_description" => "nullable|min:10|max:500",  
            "type" => "required"
        ];
    }
}