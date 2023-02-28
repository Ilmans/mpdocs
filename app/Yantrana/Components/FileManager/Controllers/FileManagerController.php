<?php

/*
* FileManagerController.php - Controller file
*
* This file is part of the FileManager component.
*-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\FileManager\Controllers;

use Illuminate\Http\Request;
use App\Yantrana\Base\BaseController;
use App\Yantrana\Components\FileManager\FileManagerEngine;

class FileManagerController extends BaseController
{
    /**
     * @var FileManagerEngine $fileManagerEngine - FileManager Engine
     */
    protected $fileManagerEngine;

    /**
      * Constructor
      *
      * @param FileManagerEngine $fileManagerEngine - FileManager Engine
      *
      * @return void
      *-----------------------------------------------------------------------*/

    public function __construct(FileManagerEngine $fileManagerEngine)
    {
        $this->fileManagerEngine = $fileManagerEngine;
    }

    /**
      * Handle upload file request
      *
      * @return json object
      *---------------------------------------------------------------- */
    
    public function upload(Request $request)
    {
        $inputData    = $request->all();

        $processReaction = $this->fileManagerEngine->processUpload($inputData);

        // if ($processReaction['reaction_code'] === 1 and __ifIsset($inputData['upload-file'])) {
        //     $processReaction['data']['url'] = $this->useFile($processReaction['data']['url']);
        // }

        // return __processResponse($processReaction, [], [], true);
            // __dd($processReaction);
        if ($processReaction['reaction_code'] === 1 and __ifIsset($inputData['upload'])) {
           return json_encode($processReaction['data']);
       }

       return json_encode($processReaction['data']);
    }

    /**
      * Handle get uploaded common files
      *
      * @param object Request $request
      *
      * @return json object
      *---------------------------------------------------------------- */
    
    public function files(Request $request)
    {
        $processReaction = $this->fileManagerEngine->prepareFiles($request->all());

        return __processResponse($processReaction, [], [], true);
    }

    /**
      * Handle delete file request
      *
      * @param object Request $request
      *
      * @return json object
      *---------------------------------------------------------------- */
    
    public function deleteFile(Request $request)
    {
        $processReaction = $this->fileManagerEngine->processDeleteFile($request->all());

        return __processResponse($processReaction);
    }

    /**
      * Handle delete file request
      *
      * @param object Request $request
      *
      * @return json object
      *---------------------------------------------------------------- */
    
    public function downloadFile(Request $request)
    {
        $processReaction = $this->fileManagerEngine->processDownloadFile($request->all());

        if ($processReaction['reaction_code'] === 1) {
            return response()->download($processReaction['data']['filename']);
        }

        return __processResponse($processReaction);
    }

    /**
      * Handle add folder request
      *
      * @param object AddFolderRequest $request
      *
      * @return json object
      *---------------------------------------------------------------- */
    
    public function addFolder(AddFolderRequest $request)
    {
        $processReaction = $this->fileManagerEngine->processAddFolder($request->all());

        return __processResponse($processReaction);
    }

    /**
      * Handle delete folder request
      *
      * @param object Request $request
      *
      * @return json object
      *---------------------------------------------------------------- */
    
    public function deleteFolder(Request $request)
    {
        $processReaction = $this->fileManagerEngine->processDeleteFolder($request->all());

        return __processResponse($processReaction);
    }

    /**
      * Handle rename folder request
      *
      * @param object RenameFolderRequest $request
      *
      * @return json object
      *---------------------------------------------------------------- */
    
    public function renameFolder(RenameFolderRequest $request)
    {
        $processReaction = $this->fileManagerEngine->processRenameFolder($request->all());

        return __processResponse($processReaction);
    }

    /**
      * Handle rename file request
      *
      * @param object RenameFileRequest $request
      *
      * @return json object
      *---------------------------------------------------------------- */
    
    public function renameFile(RenameFileRequest $request)
    {
        $processReaction = $this->fileManagerEngine->processRenameFile($request->all());

        return __processResponse($processReaction);
    }
}
