<?php
/*
* Language.php - Model file
*
* This file is part of the Language component.
*-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\Language\Models;

use App\Yantrana\Base\BaseModel;

class LanguageModel extends BaseModel 
{ 
    /**
     * @var  string $table - The database table used by the model.
     */
    protected $table = "languages";

    /**
     * Does it has has Entity Ownership ID
     *
     * @var bool
     *----------------------------------------------------------------------- */
    protected $hasEoId = false;   

    /**
     * The generate UID or not.
     *
     * @var string
     *----------------------------------------------------------------------- */
    protected $isGenerateUID = false;

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The custom primary key.
     *
     * @var string
     *----------------------------------------------------------------------- */
    
    protected $primaryKey = '_id';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;
    
    /**
     * @var  array $casts - The attributes that should be casted to native types.
     */
    protected $casts = [
    	'_id' => 'string',
    ];

    /**
     * @var  array $fillable - The attributes that are mass assignable.
     */
    protected $fillable = [
		'name',
		'_id',
		'status',
		'is_rtl',
    ];

}