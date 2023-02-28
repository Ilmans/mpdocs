<?php
/*
* MediaEngine.php - Main component file
*
* This file is part of the Media component.
*-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\Media;

use App\Yantrana\Base\BaseEngine;
use Auth;
use File;
use App\Yantrana\Components\Media\Repositories\MediaRepository;
use App\Yantrana\Components\Media\Blueprints\MediaEngineBlueprint;
use ImageIntervention;
use VIPSoft\Unzip\Unzip;
use Carbon\Carbon;
use Exception;

class MediaEngine extends BaseEngine implements MediaEngineBlueprint
{
    /**
     * @var MediaRepository - Media Repository
     */
    protected $mediaRepository;

    /**
     * Constructor.
     *
     * @param MediaRepository $mediaRepository - Media Repository
     *-----------------------------------------------------------------------*/
    public function __construct(MediaRepository $mediaRepository)
    {
        $this->mediaRepository = $mediaRepository;
    }

    /**
     * Get file name.
     *
     * @return array
     *---------------------------------------------------------------- */
    protected function getFileName($fileName, $extension, $count)
    {
        return $fileName.'-'.$count.'.'.$extension;
    }


    /**
     * Check if proved media exist in user temp media.
     *
     * @param string $fileName
     *
     * @return bool
     *---------------------------------------------------------------- */
    public function isUserTempMedia($fileName)
    {
        return File::exists(mediaStorage('user_temp_uploads', ['{_uid}' => authUID() ]).'/'.$fileName);
    }

    /**
      * Upload all files
      *
      * @param array $input
      * @param number $allowedExtensions
      *
      * @return response array
      *-----------------------------------------------------------------------*/

    public function processUpload($input, $allowedExtensions = null)
    {
        $response = $this->mediaRepository->processTransaction(function () use ($input, $allowedExtensions) {

            // if request file not found it will throw error.
            if (!array_has($input, 'upload-file') && __isEmpty($input['upload-file'])) {
                return $this->mediaRepository->transactionResponse(2);
            }

            $uploadedFile = $input['upload-file'];

            // Check if file __isEmpty or is valid
            if (__isEmpty($uploadedFile) and !$uploadedFile->isValid()) {
                return $this->mediaRepository->transactionResponse(2);
            }

            $fileName        = $uploadedFile->getClientOriginalName();

            $fileExtension   = $uploadedFile->getClientOriginalExtension();

            // if allowed file is required like some extensions only
            if (!__isEmpty($allowedExtensions)) {
                $definedExtensions = configItem('media.extensions.'.$allowedExtensions);

                // not get valid extensions array
                if (__isEmpty($definedExtensions)) {
                    return $this->mediaRepository->transactionResponse(2);
                }

                // Check if uploaded file extension not match so it will deny
                if (in_array($fileExtension, $definedExtensions) === false) {
                    return $this->mediaRepository->transactionResponse(2, null, __tr('File Has An Invalid Extension, It Should Be __extensions__', [
                            '__extensions__' => implode(',', $definedExtensions)
                        ]));
                }
            }

            $fileBaseName    = str_slug(basename($fileName, '.'.$fileExtension));

            $path            = mediaStorage('user_temp_uploads', ['{_uid}' => authUID() ]);
            $imageUrl		 = mediaStorage('user_temp_uploads', ['{_uid}' => authUID() ], true);

            if (!File::isDirectory($path)) {
                File::makeDirectory($path, $mode = 0777, true, true);
            }

            // Get temp media file of login user
            $tempFiles = glob(mediaStorage('user_temp_uploads', ['{_uid}' => authUID() ]).'/'.'*', GLOB_BRACE);

            // make a file name with extension
            $fileName = $fileBaseName.'.'.$fileExtension;

            $count = 0;

            // Check if temp user file is exist then check unique temp file in media
            if (!__isEmpty($tempFiles)) {
                foreach ($tempFiles as $tempFile) {
                    $pathInfo = pathinfo($tempFile);

                    $fName = $pathInfo['filename'].'.'.$pathInfo['extension'];

                    $count++;

                    if ($fileName == $fName) {
                        $fileName = $this->getFileName(
                            $pathInfo['filename'],
                            $pathInfo['extension'],
                            $count
                        );
                    }
                }
            }

            if ($uploadedFile->move($path, $fileName)) {
                return $this->mediaRepository->transactionResponse(1, null, __tr('File Uploaded Successfully.'));
            }
        });

        return $this->engineReaction($response);
    }

    /**
     * Process store logo media.
     *
     * @param string $logoImageFile
     *
     * @return bool
     *---------------------------------------------------------------- */
    public function processLogoMedia($logoImageFile)
    {
        $sourcePath = mediaStorage('user_temp_uploads', ['{_uid}' => authUID()]).'/'.$logoImageFile;

        // If Logo Image File Not Exist Then Return False
        if (!File::exists($sourcePath)) {
            return false;
        }

        // Get path Info about selected logo
        $logoImageInfo = pathinfo($sourcePath);

        // Get extension of selected logo
        $fileExtension = $logoImageInfo['extension'];

        $definedExtensions = configItem('media.extensions.7');

        // Check if uploaded file extension not match so it will deny
        if (in_array($fileExtension, $definedExtensions) === false) {
            return false; // invalid extensions
        }

        // Get source and media path
        $logoMediaPath = mediaStorage('logo');

        //Check if logo media directory exist
        if (!File::isDirectory($logoMediaPath)) {
            File::makeDirectory($logoMediaPath, $mode = 0777, true, true);
        }

        // Default Logo Image File Name
        $logoName = configItem('logoName');

        // Set name for image
        $destinationPath = $logoMediaPath.'/'.$logoName;

        // File::cleanDirectory($logoMediaPath);

        // If Logo Image File Moved to Logo Media Storage Then Return Image File Name
        if (File::move($sourcePath, $destinationPath)) {
            return $logoName;
        }

        return false;
    }

   
    /**
     * Process store favicon media.
     *
     * @param string $logoImageFile
     *
     * @return bool
     *---------------------------------------------------------------- */
    public function processFaviconMedia($faviconImageFile)
    {
        $sourcePath = mediaStorage('user_temp_uploads', ['{_uid}' => authUID()]).'/'.$faviconImageFile;

        // If Logo Image File Not Exist Then Return False
        if (!File::exists($sourcePath)) {
            return false;
        }
        
        // Get path Info about selected logo
        $faviconImageInfo = pathinfo($sourcePath);

        // Get extension of selected logo
        $fileExtension = $faviconImageInfo['extension'];

        $definedExtensions = configItem('media.extensions.9');

        // Check if uploaded file extension not match so it will deny
        if (in_array($fileExtension, $definedExtensions) === false) {
            return false; // invalid extensions
        }

        // Get source and media path
        $faviconMediaPath = mediaStorage('favicon');

        //Check if logo media directory exist
        if (!File::isDirectory($faviconMediaPath)) {
            File::makeDirectory($faviconMediaPath, $mode = 0777, true, true);
        }

        // Default Logo Image File Name
        $faviconName = configItem('faviconName');

        // Set name for image
        $destinationPath = $faviconMediaPath.'/'.$faviconName;

        // If Logo Image File Moved to Logo Media Storage Then Return Image File Name
        if (File::move($sourcePath, $destinationPath)) {
            return $faviconName;
        }

        return false;
    }

    /**
     * Prepare uploaded images files form temp media storage and get details.
     *
     * @param array $requiredOnlyThis
     *
     * @return array
     *---------------------------------------------------------------- */
    public function prepareUploadedFiles($requiredOnlyThis = [])
    {
        if (!is_array($requiredOnlyThis)) {
            return $this->engineReaction(2, null, __tr('Only array accept here.'));
        }

        $userID = getUserID();
        $files = [];
        $tempFiles = glob(mediaStorage('user_temp_uploads', ['{_uid}' => authUID()]).'/'.'*', GLOB_BRACE);

        if (!__isEmpty($tempFiles)) {
            foreach ($tempFiles as $tempFile) {
                $mediaUrl = mediaUrl('user_temp_uploads', ['{_uid}' => authUID()]);

                $pathInfo = pathinfo($tempFile);


                if (!__isEmpty($requiredOnlyThis) and in_array($pathInfo['extension'], $requiredOnlyThis)) {
                    $imageName = $pathInfo['filename'].'.'.$pathInfo['extension'];
                    
                    $files[] = [
                        'name' => $imageName,
                        'path' => $mediaUrl.'/'.$imageName,
                        
                    ];
                } elseif (__isEmpty($requiredOnlyThis)) {
                    $imageName = $pathInfo['filename'].'.'.$pathInfo['extension'];

                    $files[] = [
                        'name' => $imageName,
                        'path' => $mediaUrl.'/'.$imageName
                    ];
                }
            }
        }

        return $this->engineReaction(1, ['files' => $files]);
    }


    /**
     * Delete user temp media file.
     *
     * @param string $fileName
     *
     * @return array
     *---------------------------------------------------------------- */
    public function processDeleteTempFile($fileName)
    {
        $path = mediaStorage('user_temp_uploads', ['{_uid}'    => getAuthUser()->_uid]).'/'.$fileName;

        // Check if file exist
        if (!File::exists($path)) {
            return $this->engineReaction(3);
        }

        // Check if file deleted
        if (File::delete($path)) {
            return $this->engineReaction(1);
        }

        return $this->engineReaction(2);
    }

    /**
     * Delete user temp media file.
     *
     * @return array
     *---------------------------------------------------------------- */
    public function processDeleteMultipleTempFiles($files)
    {
        // Check if file empty
        if (!__ifIsset($files)) {
            return $this->engineReaction(2);
        }

        $deletedFileCount = 0;
        $userTempMediaPath = mediaStorage('user_temp_uploads', ['{_uid}' => authUID() ]);

        foreach ($files as $file) {
            if ((isset($file['isSelected'])) and ($file['isSelected'] === true)) {
                $sourcePath = $userTempMediaPath.'/'.$file['name'];

                // Check if file exist
                if (File::exists($sourcePath) and File::delete($sourcePath)) {
                    ++$deletedFileCount;
                }
            }
        }

        if ($deletedFileCount > 0) {
            return $this->engineReaction(1);
        }

        return $this->engineReaction(2);
    }

    /**
     * Delete user temp media file.
     *
     * @return array
     *---------------------------------------------------------------- */
    public function processSelectTempFiles($files)
    {
        // Check if file empty
        if (!__ifIsset($files)) {
            return $this->engineReaction(2, null, __tr('Please Select Any One File.'));
        }

        $selectedFiles = [];
        $userTempMediaPath = mediaStorage('user_temp_uploads', ['{_uid}' => authUID() ]);
        
        foreach ($files as $file) {
            if ((isset($file['isSelected'])) and ($file['isSelected'] === true)) {
                $sourcePath = $userTempMediaPath.'/'.$file['name'];

                // Check if file exist
                if (File::exists($sourcePath)/* and File::delete($sourcePath)*/) {
                    //++$deletedFileCount;
                    $selectedFiles[] = [
                        'name'  => $file['name'],
                        'path'  => $file['path']
                    ];
                }
            }
        }

        if (!__isEmpty($selectedFiles)) {
            return $this->engineReaction(1, ['selectedFiles' => $selectedFiles], __tr('File Selected Successfully.'));
        }

        return $this->engineReaction(2, null, __tr('Something Went Wrong.'));
    }


    /**
     * Store User Profile Image Media.
     *
     * @param string $fileName
     *
     * @return bool
     *---------------------------------------------------------------- */

    public function storeUserProfile($fileName)
    {
        $userUid           = authUID();

        $profileMediaPath  = mediaStorage('user_photo', ['{_uid}'    => $userUid]);
        $sourcePath        = mediaStorage('user_temp_uploads', ['{_uid}'    => $userUid]).'/'.$fileName;


        //Check if item media directive exist
        if (!File::isDirectory($profileMediaPath)) {
            File::makeDirectory($profileMediaPath, $mode = 0777, true, true);
        }

        $destinationPath    = $profileMediaPath.'/'.$fileName;

        if (File::move($sourcePath, $destinationPath)) {
            $width    = 100;
            $height = 100;

            // open an image file
            $thumbnail = ImageIntervention::make($destinationPath);

            // now you are able to resize the instance
            $thumbnail->resize($width, $height, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });

            // finally we save the image as a new image
            $thumbnail->save($destinationPath);

            return $fileName;
        }

        return false;
    }

    /**
      * Upload Project related files
      *
      * @param array $input
      * @param number $allowedExtensions
      *
      * @return response array
      *-----------------------------------------------------------------------*/

    public function processProjectFilesUpload($input, $allowedExtensions = null)
    {
        $response = $this->mediaRepository->processTransaction(function () use ($input, $allowedExtensions) {

            // if request file not found it will throw error.
            if (!array_has($input, 'upload-file') && __isEmpty($input['upload-file'])) {
                return $this->mediaRepository->transactionResponse(2);
            }

            $uploadedFile = $input['upload-file'];

            $dimensions = getimagesize($uploadedFile);

            // Check if file __isEmpty or is valid
            if (__isEmpty($uploadedFile) and !$uploadedFile->isValid()) {
                return $this->mediaRepository->transactionResponse(2);
            }

            $fileName        = $uploadedFile->getClientOriginalName();

            $fileExtension   = $uploadedFile->getClientOriginalExtension();
            
            /*$configDimensions =  configItem('project.logo_dimensions');

            if ($dimensions['0'] > $configDimensions['height'] || $dimensions['1'] > $configDimensions['width']) {
            	return $this->mediaRepository->transactionResponse(2, null, 
            		__tr('Please upload an image with height: __height__ px and width: __width__ px', [
            			'__height__' => $configDimensions['height'],
            			'__width__' => $configDimensions['width'],
            		]));
            }*/

            // if allowed file is required like some extensions only
            if (!__isEmpty($allowedExtensions)) {
                $definedExtensions = configItem('media.extensions.'.$allowedExtensions);

                // not get valid extensions array
                if (__isEmpty($definedExtensions)) {
                    return $this->mediaRepository->transactionResponse(2);
                }

                // Check if uploaded file extension not match so it will deny
                if (in_array($fileExtension, $definedExtensions) === false) {
                    return $this->mediaRepository->transactionResponse(2, null, __tr('File Has An Invalid Extension, It Should Be __extensions__', [
                            '__extensions__' => implode(', ', $definedExtensions)
                        ]));
                }
            }

            $fileBaseName    = str_slug(basename($fileName, '.'.$fileExtension));

            $path            = mediaStorage('user_temp_uploads', ['{_uid}' => authUID() ]);
            $imageUrl		 = mediaStorage('user_temp_uploads', ['{_uid}' => authUID() ], true);

            if (!File::isDirectory($path)) {
                File::makeDirectory($path, $mode = 0777, true, true);
            }

            // Get temp media file of login user
            $tempFiles = glob(mediaStorage('user_temp_uploads', ['{_uid}' => authUID() ]).'/'.'*', GLOB_BRACE);

            // make a file name with extension
            $fileName = $fileBaseName.'.'.$fileExtension;

            $count = 0;

            // Check if temp user file is exist then check unique temp file in media
            if (!__isEmpty($tempFiles)) {
                foreach ($tempFiles as $tempFile) {
                    $pathInfo = pathinfo($tempFile);

                    $fName = $pathInfo['filename'].'.'.$pathInfo['extension'];

                    $count++;

                    if ($fileName == $fName) {
                        $fileName = $this->getFileName(
                            $pathInfo['filename'],
                            $pathInfo['extension'],
                            $count
                        );
                    }
                }
            }

            $fileDetails = [
            	'url' => ($imageUrl.'/'.$fileName),
            	'file_name' => $fileName,
            	'extension' => $fileExtension
            ];

            if ($uploadedFile->move($path, $fileName)) {
                return $this->mediaRepository->transactionResponse(1, [
                	'fileDetails' => $fileDetails
                ], __tr('File Uploaded Successfully.'));
            }
        });

        return $this->engineReaction($response);
    }

    /**
     * Store User Profile Image Media.
     *
     * @param string $fileName
     *
     * @return bool
     *---------------------------------------------------------------- */

    public function storeProjectLogo($fileName, $projectUid)
    {
        $userUid           = authUID();
        $sourcePath        = mediaStorage('user_temp_uploads', ['{_uid}'    => $userUid]).'/'.$fileName;
        $projectLogoMediaPath  = mediaStorage('project_uploads', ['{_uid}'    => $projectUid]);

        //Check if item media directive exist
        if (!File::isDirectory($projectLogoMediaPath)) {
            File::makeDirectory($projectLogoMediaPath, $mode = 0777, true, true);
        }

        // Get path Info about selected logo
        $imageInfo = pathinfo($sourcePath);

        // Get extension of selected logo
        $fileExtension = $imageInfo['extension'];

        // Default Logo Image File Name
        $logoName = configItem('project.logoName').'.'.$fileExtension;

        $destinationPath    = $projectLogoMediaPath.'/'.$logoName;

        if (File::move($sourcePath, $destinationPath)) {

            return $fileName;
        }

        return false;
    }

    /**
     * Process store favicon media.
     *
     * @param string $logoImageFile
     *
     * @return bool
     *---------------------------------------------------------------- */
    public function storeProjectFavicon($faviconImageFile, $projectUid)
    {
        $sourcePath = mediaStorage('user_temp_uploads', ['{_uid}' => authUID()]).'/'.$faviconImageFile;

        // If Logo Image File Not Exist Then Return False
        if (!File::exists($sourcePath)) {
            return false;
        }
        
        // Get path Info about selected logo
        $faviconImageInfo = pathinfo($sourcePath);

        // Get extension of selected logo
        $fileExtension = $faviconImageInfo['extension'];

        $definedExtensions = configItem('media.extensions.9');

        // Check if uploaded file extension not match so it will deny
        if (in_array($fileExtension, $definedExtensions) === false) {
            return false; // invalid extensions
        }

        // Get source and media path
        $faviconMediaPath = mediaStorage('project_uploads', ['{_uid}'    => $projectUid]);

        //Check if logo media directory exist
        if (!File::isDirectory($faviconMediaPath)) {
            File::makeDirectory($faviconMediaPath, $mode = 0777, true, true);
        }

        // Default Logo Image File Name
        $faviconName = configItem('faviconName');

        // Set name for image
        $destinationPath = $faviconMediaPath.'/'.$faviconName;

        // If Logo Image File Moved to Logo Media Storage Then Return Image File Name
        if (File::move($sourcePath, $destinationPath)) {
            return $faviconName;
        }

        return false;
    }

    /**
      * Delete Project Logo or Favicon
      *
      * @param number $mediaType
      * @param string $projectUid
      * @param string $fileName
      *
      * @return string
      *-----------------------------------------------------------------------*/

    public function deleteProjectLogoOrFavicon($projectUid, $fileName)
    {
        $mediaPath = mediaStorage('project_uploads', ['{_uid}'    => $projectUid]).'/'.$fileName;

        if (File::exists($mediaPath)) {
            if (File::delete($mediaPath)) {
                return true;
            }
        }

        return false;
    }

    /**
      * Check the file already exists in media storage
      *
      * @param string $messageUid
      * @param string $fileName
      *
      * @return string
      *-----------------------------------------------------------------------*/

    public function varifyFileExists($messageUid, $fileName)
    {
        $varificationPath = '';

        $varificationPath = mediaStorage('msg_attachments', [
                                    '{msg_uid}' => $messageUid
                                ]);

        if (!__isEmpty($varificationPath)
            and File::exists($varificationPath.'/'.$fileName)) {
            return uniqid().'-'.$fileName;
        }

        return $fileName;
    }
}
