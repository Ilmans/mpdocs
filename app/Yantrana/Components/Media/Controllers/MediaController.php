<?php
/*
* MediaController.php - Controller file
*
* This file is part of the Media component.
*-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\Media\Controllers;

use App\Yantrana\Support\CommonPostRequest as Request;
use App\Yantrana\Base\BaseController;
use App\Yantrana\Components\Media\MediaEngine;
use Exception;

class MediaController extends BaseController
{
    /**
     * @var MediaEngine - Media Engine
     */
    protected $mediaEngine;

    /**
     * Constructor.
     *
     * @param MediaEngine $mediaEngine - Media Engine
     *-----------------------------------------------------------------------*/
    public function __construct(MediaEngine $mediaEngine)
    {
        $this->mediaEngine = $mediaEngine;
    }

    /**
     * Upload image files by user.
     *
     * @param object Request $request
     *
     * @return json object
     *---------------------------------------------------------------- */
    public function upload(Request $request)
    {
        $processReaction = $this->mediaEngine->processUpload($request->all());

        return __processResponse($processReaction, [], [], true);
    }

    /**
     * Upload image files by user.
     *
     * @param object Request $request
     *
     * @return json object
     *---------------------------------------------------------------- */
    public function uploadProjectFiles(Request $request)
    {
        $processReaction = $this->mediaEngine->processProjectFilesUpload($request->all(), 10);

        return __processResponse($processReaction, [], [], true);
    }

    /**
     * Upload overall all files
     *
     * @param object Request $request
     *
     * @return json object
     *---------------------------------------------------------------- */
    public function uploadLogo(Request $request)
    {
        $processReaction = $this->mediaEngine
                                ->processUpload(
                                    $request->all(),
                                    10 // logo // Favicon
                                );

        return __processResponse($processReaction, [], [], true);
    }

    /**
     * Upload overall all files
     *
     * @param object Request $request
     *
     * @return json object
     *---------------------------------------------------------------- */
    public function uploadUserProfile(Request $request)
    {
        $processReaction = $this->mediaEngine
                                ->processUpload(
                                    $request->all(),
                                    1
                                );

        return __processResponse($processReaction, [], [], true);
    }

    /**
     * Handle uploaded images files request.
     *
     * @return json object
     *---------------------------------------------------------------- */
    public function readFiles()
    {
        $processReaction = $this->mediaEngine->prepareUploadedFiles();

        return __processResponse($processReaction, [
            ], $processReaction['data']);
    }

    /**
     * Handle uploaded images files request.
     *
     * @return json object
     *---------------------------------------------------------------- */
    public function readLogoFiles()
    {
        $processReaction = $this->mediaEngine
                                ->prepareUploadedFiles(configItem('media.extensions.7'));
        
        return __processResponse($processReaction, [
            ], $processReaction['data']);
    }

    /**
     * Handle uploaded images files request.
     *
     * @return json object
     *---------------------------------------------------------------- */
    public function readProjectFiles()
    {
        $processReaction = $this->mediaEngine->prepareUploadedFiles(configItem('media.extensions.11'));

        return __processResponse($processReaction, [
            ], $processReaction['data']);
    }

    /**
     * Handle uploaded images files request.
     *
     * @return json object
     *---------------------------------------------------------------- */
    public function readUserProfileFiles()
    {
        $processReaction = $this->mediaEngine
                                ->prepareUploadedFiles(configItem('media.extensions.1'));
        
        return __processResponse($processReaction, [
            ], $processReaction['data']);
    }

    /**
     * Handle uploaded images files request.
     *
     * @return json object
     *---------------------------------------------------------------- */
    public function readFaviconFiles()
    {
        $processReaction = $this->mediaEngine
                                ->prepareUploadedFiles(configItem('media.extensions.9'));
        
        return __processResponse($processReaction, [
            ], $processReaction['data']);
    }

    /**
     * Handle user temp media delete request.
     *
     * @param string $fileName
     *
     * @return json object
     *---------------------------------------------------------------- */
    public function delete($fileName, Request $request)
    {
        $processReaction = $this->mediaEngine
                                ->processDeleteTempFile($fileName);

        return __processResponse($processReaction, [
                1 => __tr('File deleted.'),
                2 => __tr('File not deleted.'),
                3 => __tr('File does not exist.'),
            ]);
    }

    /**
     * Handle delete multiple user temp media.
     *
     * @param object Request $request
     *
     * @return json object
     *---------------------------------------------------------------- */
    public function multipleDeleteFiles(Request $request)
    {
        $processReaction = $this->mediaEngine
                                ->processDeleteMultipleTempFiles(
                                    $request->input('uploadedFiles')
                                );

        return __processResponse($processReaction, [
                1 => __tr('Files deleted.'),
                2 => __tr('Files not deleted.'),
            ]);
    }

    /**
     * Handle select one or multiple user temp media.
     *
     * @param object Request $request
     *
     * @return json object
     *---------------------------------------------------------------- */
    public function selectFiles(Request $request)
    {
        $processReaction = $this->mediaEngine
                                ->processSelectTempFiles(
                                    $request->input('uploadedFiles')
                                );

        return __processResponse($processReaction, [], [], true);
    }
}
