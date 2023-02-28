<?php
/*
* Version.php - Model file
*
* This file is part of the Version component.
*-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\Version\Models;

use App\Yantrana\Base\BaseModel;
use App\Yantrana\Components\Article\Models\ArticleModel;

class VersionModel extends BaseModel 
{ 
    /**
     * @var  string $table - The database table used by the model.
     */
    protected $table = "doc_versions";

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
        'projects__id' => 'integer',
        'status' => 'integer'
    ];


    /**
    * Get all primary articles
    */
    public function articles()
    {
        return  $this->hasMany(ArticleModel::class, 'doc_versions__id', '_id')->orderBy('list_order');
    }

    /**
    * Get all primary articles
    */
    public function activeArticles()
    {
        return  $this->hasMany(ArticleModel::class, 'doc_versions__id', '_id')->with('contents');
    }
}