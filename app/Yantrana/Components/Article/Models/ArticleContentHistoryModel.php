<?php
/*
* ArticleContentHistory.php - Model file
*
* This file is part of the Article component.
*-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\Article\Models;

use App\Yantrana\Base\BaseModel;

class ArticleContentHistoryModel extends BaseModel 
{ 
    /**
     * @var  string $table - The database table used by the model.
     */
    protected $table = "article_content_history";
    
    /**
     * @var  array $casts - The attributes that should be casted to native types.
     */
    protected $casts = [
        'article_contents__id' => 'integer',
        'user_authorities__id' => 'integer',
        'status'               => 'integer',
        '__data'               => 'array'
    ];

    /**
     * Let the system knows Text columns treated as JSON
     *
     * @var array
     *----------------------------------------------------------------------- */
    protected $jsonColumns = [
        '__data' => [
            'pre_article_content' => 'array'
        ]
    ];
}