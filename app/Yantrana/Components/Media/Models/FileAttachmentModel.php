<?php
/*
* FileAttachment.php - Model file
*
* This file is part of the Media component.
*-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\Media\Models;

use App\Yantrana\Base\BaseModel;

class FileAttachmentModel extends BaseModel
{
    /**
     * @var  string $table - The database table used by the model.
     */
    protected $table = "file_attachments";

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
        "issues__id"	=> "integer",
        "requirements__id"   => "integer",
        "_id" 			=> "integer"
    ];

    /**
     * @var  array $fillable - The attributes that are mass assignable.
     */
    protected $fillable = [];
}
