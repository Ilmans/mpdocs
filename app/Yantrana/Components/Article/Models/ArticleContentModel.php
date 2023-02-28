<?php
/*
* ArticleContentModel.php - Model file
*
* This file is part of the Article component.
*-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\Article\Models;

use App\Yantrana\Base\BaseModel;

class ArticleContentModel extends BaseModel 
{ 
    /**
     * @var  string $table - The database table used by the model.
     */
    protected $table = "article_contents";

    /**
     * Does it has has Entity Ownership ID
     *
     * @var bool
     *----------------------------------------------------------------------- */
    protected $hasEoId = true;   
    

    
    /**
     * @var  array $casts - The attributes that should be casted to native types.
     */
    protected $casts = [
    ];

    /**
     * @var  array $fillable - The attributes that are mass assignable.
     */
    protected $fillable = [
    ];

	/**
	* Get the post that owns the comment.
	*/
	public function articleLanguages()
	{
			return $this->hasMany(self::class, '_id', 'articles__id')
					->join('languages', 'article_contents.languages__id', '=', 'languages._id')
					->select('article_contents._id', 'languages.name');
	}
}