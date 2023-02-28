<?php            
/*
* VersionListRequest.php - Request file
*
* This file is part of the Version component.
*-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\Version\Requests;

use App\Yantrana\Base\BaseRequest;
use Illuminate\Validation\Rule;

class VersionAddRequest extends BaseRequest 
{   
    /**
      * Set if you need form request secured.
      *------------------------------------------------------------------------ */
    protected $securedForm = false;

    /**
     * Unsecured/Unencrypted form fields.
     *------------------------------------------------------------------------ */
    protected $unsecuredFields = [
        'version'
    ];
    
        
   /**
     * Loosely sanitize fields.
     *------------------------------------------------------------------------ */
    protected $looseSanitizationFields = [
        'version' => '' 
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
        $projectIdOrUid = request()->input('projects__id');
        
        return [
            "slug" => [
                "required", 
                "slug",
                Rule::unique('doc_versions')->where(function ($query) use($projectIdOrUid) {
                    return $query->where('projects__id', $projectIdOrUid);
                })
            ],
            'version' => [
                'required',
                'max:10',
                Rule::unique('doc_versions')->where(function ($query) use($projectIdOrUid) {
                    return $query->where('projects__id', $projectIdOrUid);
                }),
            ],
            'status' => 'required'
        ];
    }
}