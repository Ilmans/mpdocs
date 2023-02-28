<?php            
/*
* ArticleListRequest.php - Request file
*
* This file is part of the Article component.
*-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\Article\Requests;

use App\Yantrana\Base\BaseRequest;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Input;

class ArticleAddRequest extends BaseRequest 
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
    	'description' => true, 
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
    	$requestType = $this->route('requestType');
        $inputData = $this->all();
 		$docVersionId = $inputData['doc_versions__id'];

        $validations = [
            "status"  => "required",
            "articles_content.*.title" => "max:500|required_if:articles_content.*.is_primary,1|required_with:articles_content.*.description",
            "articles_content.*.status" => "required_if:articles_content.*.is_primary,1",
            "articles_content.*.description" => "required_if:articles_content.*.is_primary,1|required_with:articles_content.*.title"
        ];

        $validations['slug'] = [
                                "required",
                                "slug",
                                Rule::unique('articles')->where(function ($query) use($docVersionId) {
                                    return $query->where('doc_versions__id', $docVersionId);
                                })
                            ];

        return $validations;
    }

    public function messages()
    {
    	$contents = request()->only('articles_content');
    	$messages = [];

	  	foreach ($contents['articles_content'] as $key => $content) {

 			$messages['articles_content.'.$key.'.title.max'] = "The title may not be greater than :max characters.";
 			$messages['articles_content.'.$key.'.description.max'] = "The description may not be greater than :max characters.";

 			$messages['articles_content.'.$key.'.title.required_if'] = "The title field is required.";
 			$messages['articles_content.'.$key.'.description.required_if'] = "The description field is required.";
 			$messages['articles_content.'.$key.'.status.required_if'] = "The status field is required.";

 			$messages['articles_content.'.$key.'.title.required_with'] = "The title field is required when description is present.";
 			$messages['articles_content.'.$key.'.description.required_with'] = "The description field is required when title is present.";
            $messages['articles_content.'.$key.'.title.unique_article_title'] = "The title field has already been taken.";
    	}

    	return $messages;
    } 
}