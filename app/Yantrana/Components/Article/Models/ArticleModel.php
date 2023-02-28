<?php
/*
* Article.php - Model file
*
* This file is part of the Article component.
*-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\Article\Models;

use App\Yantrana\Base\BaseModel;
use App\Yantrana\Components\Article\Models\ArticleContentModel;

class ArticleModel extends BaseModel 
{ 
    /**
     * @var  string $table - The database table used by the model.
     */
    protected $table = "articles";

    /**
     * Does it has has Entity Ownership ID
     *
     * @var bool
     *----------------------------------------------------------------------- */
    protected $hasEoId = true;   

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['published_at', '__data'];

    /**
     * @var  array $casts - The attributes that should be casted to native types.
     */
    protected $casts = [
        '__data' => 'array',
        'list_order' => 'integer',
        'previous_articles__id' => 'integer'
    ];

    /**
    * Get all primary articles
    */
    public function contents()
    {
        return  $this->hasMany(ArticleContentModel::class, 'articles__id', '_id');
    }

    /**
    * Get all primary articles
    */
    public function content()
    {
        return  $this->hasOne(ArticleContentModel::class, 'articles__id', '_id');
    }

    /**
    * Get all primary articles
    */
    public function subArticles()
    {
        return $this->hasMany(self::class, 'previous_articles__id', '_id')->with('contents');
    }

    /**
    * Get the parent category.
    */
    public function childrenArticle()
    {
        return $this->hasMany(self::class, 'previous_articles__id')->leftJoin('article_contents', 'articles._id', '=', 'article_contents.articles__id')
                    ->select(
                        __nestedKeyValues([
                            'article_contents' => [
                                'title'
                            ],
                            'articles' => [
                                '_id',
                                'published_at',
                                'previous_articles__id',
                                'slug'
                            ]
                        ])
                    );
    }

    /**
    * Get the parent category.
    */
    public function children()
    {
        return $this->childrenArticle()->with('children');
    }
}