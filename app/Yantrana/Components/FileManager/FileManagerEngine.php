<?php

/*
* FileManagerEngine.php - Main component file
*
* This file is part of the FileManager component.
*-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\FileManager;

use File;
use Auth;
use ImageIntervention;
use App\Yantrana\Base\BaseEngine;
use Exception;

use App\Yantrana\Components\FileManager\Repositories\FileManagerRepository;
use App\Yantrana\Components\FileManager\Interfaces\FileManagerEngineInterface;

class FileManagerEngine extends BaseEngine implements FileManagerEngineInterface
{
    /**
     * @var FileManagerRepository $fileManagerRepository - FileManager Repository
     */
    protected $fileManagerRepository;

    /**
      * Constructor
      *
      * @param FileManagerRepository $fileManagerRepository - FileManager Repository
      *
      * @return void
      *-----------------------------------------------------------------------*/

    public function __construct(FileManagerRepository $fileManagerRepository)
    {
        $this->fileManagerRepository = $fileManagerRepository;
    }

    /**
      * get the directory path
      *
      * @return json object
      *---------------------------------------------------------------- */
    
    protected function getDirPath()
    {
        return base_path().'/'.config('__file_manager.files_dir');
    }

    /**
      * get the directory path
      *
      * @return json object
      *---------------------------------------------------------------- */
    
    protected function getThumbnailDirPath()
    {
        return base_path().'/'.config('__file_manager.thumbnails_dir');
    }

    /**
      * get the directory path
      *
      * @return json object
      *---------------------------------------------------------------- */
    
    protected function getDirUrl()
    {
        return url(config('__file_manager.files_url'));
    }

    /**
      * get the thumbnail directory media url
      *
      * @return json object
      *---------------------------------------------------------------- */
    
    protected function getThumbnailDirUrl()
    {
        return url(config('__file_manager.thumbnails_dir_url'));
    }

    /**
      * Process uploaded file
      *
      * @param array   $inputData
      *
      * @return array
      *---------------------------------------------------------------- */
    
    public function processUpload($inputData)
    {
        try {

            // Check if request by ckeditor upload dialog
            if (__ifIsset($inputData['upload-file'])) {
                $inputFile = $inputData['upload-file'];
            } else if (__ifIsset($inputData['upload'])) {
                $inputFile = $inputData['upload'];
            } else {
                $inputFile = $inputData['file'];
            }

            $dirUrl = $this->getDirUrl().'/';
            

            // Check if file empty and is in valid
            if (__isEmpty($inputFile) and !$inputFile->isValid()) {
                return $this->engineReaction(2, [
                        "uploaded"    => 0,
                        "error"       => [
                            'message' => __tr('File format is invalid.')
                        ]
                    ]);
            }

            $fileExtension  = strtolower($inputFile->getClientOriginalExtension());

            $filename       = $inputFile->getClientOriginalName();
            $fileBaseName   = str_slug(basename($filename, '.'.$fileExtension));
            $filename          = $fileBaseName.'.'.$fileExtension;

            $filesDirPath    = $this->getDirPath();

            $allowedExtention = configItem('media.extensions.1');

            if (!in_array($fileExtension, $allowedExtention)) {
                return $this->engineReaction(2, [
                        "uploaded"    => 0,
                        "error"       => [
                            'message' => __tr('File extension is invalid.')
                        ]
                    ]);
            }

            if (__ifIsset($inputData['currentDir'])) {
                $currentDir      = $inputData['currentDir'];

                $reuestedDirPath = $filesDirPath.$currentDir.'/';

                if (!File::isDirectory($reuestedDirPath)) {
                    return $this->engineReaction(2, [
                        "uploaded"    => 0,
                        "error"       => [
                            'message' => __tr('Invalid Folder.')
                        ]
                    ]);
                }

                $filesDirPath = $reuestedDirPath;

                $dirUrl = $dirUrl.$currentDir.'/';
            }

            // check current path directory not available then make directory
            if (!File::isDirectory($filesDirPath)) {
                File::makeDirectory($filesDirPath, $mode = 0777, true, true);
            }
               
            // if check this file alerady exist in directory then
            // make it unique file
            if (File::exists($filesDirPath.$filename)) {
                $filename = $fileBaseName."-".uniqid().".".$fileExtension;
            }

            // move the file from particular path
            if ($inputFile->move($filesDirPath, $filename)) {

                // Check if uploaded file is image and its extension available in supported
                // image extensions for thumbnail array
                /*
                if (getimagesize($filesDirPath.$filename) != false
                    and
                    in_array($fileExtension, config('__file_manager.allowed_image_extensions_for_thumb'))) {
                    $width    = config('__file_manager.image_thumb_width');
                    $height   = config('__file_manager.image_thumb_height');

                    // open an image file
                    $thumbnail = ImageIntervention::make($filesDirPath.$filename);

                    // now you are able to resize the instance
                    $thumbnail->resize($width, $height, function ($constraint) {
                        $constraint->aspectRatio();
                        $constraint->upsize();
                    });

                    $thumbnailsDir    = $this->getThumbnailDirPath();

                    //Check if directive exist
                    if (!File::isDirectory($thumbnailsDir)) {
                        File::makeDirectory($thumbnailsDir, $mode = 0777, true, true);
                    }

                    // finally we save the image as a new image
                    $thumbnail->save($thumbnailsDir.'/'.$filename);
                }
				*/
                return $this->engineReaction(1, [
                        "uploaded"    => 1,
                        "fileName"    => $filename,
                        "url"         => $dirUrl.$filename
                    ]);
            }

            return $this->engineReaction(2, [
                    "uploaded"    => 1,
                    "fileName"    => $filename,
                    "error"       => [
                        'message' => 'Uploaded File Not Moved'
                    ]
                ]
            );
        }

        //catch exception
        catch (Exception $e) {
            return $this->engineReaction(2, [
                "uploaded"    => 0,
                "error"       => [
                    'message' =>  $e->getMessage()
                ]
            ]);
        }
    }

    /**
      * Prepare uploaded files
      *
      * @return array
      *---------------------------------------------------------------- */
    
    public function prepareFiles($inputData)
    {
        $currentDir = '';
        $files        = $currentFolderData = [];

        $currentDirTree    = [];

        $filesDirPath    = $this->getDirPath();
        $mediaUrl        = $this->getDirUrl();

        if (__ifIsset($inputData['currentDir'])) {
            $currentDir      = $inputData['currentDir'];

            $reuestedDirPath = $filesDirPath.$currentDir.'/';

            if (!File::isDirectory($reuestedDirPath)) {
                return $this->engineReaction(2, null, __tr('Invalid Folder.'));
            }

            $mediaUrl = $mediaUrl.'/'.$currentDir;

            $currentDirPathInfo = pathinfo($currentDir);

            $currentDirStucture = array_reverse(explode("/", $currentDir));

            if (!__isEmpty($currentDirStucture)) {
                $doneLoopIds = [];

                $availableIds = $currentDirStucture;

                foreach ($currentDirStucture as $dirName) {
                    if (!__isEmpty($dirName)) {
                        $array = [];

                        foreach ($availableIds as $id) {
                            if (!in_array($id, $doneLoopIds)) {
                                $array[] = $id;
                            }
                        }

                        $currentDirTree[] = [
                            'name'    => $dirName,
                            'path'    => trim(str_replace($filesDirPath, '', $filesDirPath.implode("/", array_reverse($array))))
                        ];

                        $doneLoopIds[] = $dirName;
                    }
                }

                $currentDirTree[] = [
                    'name'    => 'Home',
                    'path'    => ''
                ];
            }

            $currentFolderData = [
                'name'                    => $currentDirPathInfo['filename'],
                'directoryPath'            => $currentDir,
                'parentDirectoryPath'    => trim(str_replace($filesDirPath, '', dirname($reuestedDirPath).'/')),
                'currentDirTree'        => array_reverse($currentDirTree)
            ];
        } else {
            $reuestedDirPath = $filesDirPath;

            $currentDirTree[] = [
                'name'    => 'Home',
                'path'    => ''
            ];

            $currentFolderData = [
                'currentDirTree' => $currentDirTree
            ];
        }

        $thumbnailDir 		= $this->getThumbnailDirPath();		// get the thumbnails directory path
        $thumbnailDirUrl 	= $this->getThumbnailDirUrl();		// get the thumbnails directory url

        $fileColletion = glob($reuestedDirPath.'*', GLOB_BRACE);

        if (!__isEmpty($fileColletion)) {
            foreach ($fileColletion as $file) {
                $fileData    = [];
                $pathInfo    = pathinfo($file);

                // Check if current file item is file or directory
                if (File::isDirectory($file)) {
                    if ($pathInfo['filename'] != 'thumbnails') {
                        $fileData    = [
                            'isDirectory'    	=> true,
                            'name'            	=> $pathInfo['filename'],
                            'shortName'        	=> str_limit($pathInfo['filename'], 12),
                            'relativePath'    	=> trim(str_replace($filesDirPath, "", $file)),
                            'previewUrl'    	=> url(config('__file_manager.folder_icon_path')),
                            'fileUrl'    	=> url(config('__file_manager.files_url'))
                        ];
                    }
                } else {

                    // Check if file is valid
                    if (__ifIsset($pathInfo['extension'])) {
                        $extension = strtolower($pathInfo['extension']);

                        $filename = $pathInfo['filename'].'.'.$extension;

                        $isImageFile = false;
                        $previewUrl  = '';
                        $url  		= '';

                        if (__ifIsset($inputData['type']) and $inputData['type'] == 'images') {

                            // Check if file is image
                            if (getimagesize($file) != false) {
                                $isImageFile = true;

                                // Check if image thumb available or not in thumbnails directory
                                if (File::exists($thumbnailDir.$filename)) {
                                    $previewUrl = $thumbnailDirUrl.'/'.$filename;
                                } else {
                                    $previewUrl = $mediaUrl.'/'.$filename;
                                }

                                $fileData = [
                                    'name'            	=> $filename,
                                    'extension'        	=> $extension,
                                    'baseName'        	=> $pathInfo['filename'],
                                    'shortName'        	=> str_limit($filename, 12),
                                    'previewUrl'    	=> $previewUrl,
                                    'isImageFile'    	=> $isImageFile,
                                    'relativePath'    	=> trim(str_replace($filesDirPath, "", $file)),
                                    'url'				=> $mediaUrl.'/'.$filename
                                ];
                            }
                        } else {


                            // Check if file is image
                            if (getimagesize($file) != false) {
                                $isImageFile = true;

                                // Check if image thumb available or not in thumbnails directory
                                if (File::exists($thumbnailDir.$filename)) {
                                    $previewUrl = $thumbnailDirUrl.'/'.$filename;
                                } else {
                                    $previewUrl = $mediaUrl.'/'.$filename;
                                }

                                $url  		= $mediaUrl.'/'.$filename;
                            } else {
                                $previewUrl  = url(config('__file_manager.other_file_icon_path'));
                            }

                            $fileData = [
                                'name'            	=> $filename,
                                'extension'        	=> $extension,
                                'baseName'        	=> $pathInfo['filename'],
                                'shortName'        	=> str_limit($filename, 12),
                                'previewUrl'    	=> $previewUrl,
                                'isImageFile'    	=> $isImageFile,
                                'relativePath'    	=> trim(str_replace($filesDirPath, "", $file)),
                                'url'				=> $url
                            ];
                        }
                    }
                }

                if (!__isEmpty($fileData)) {
                    $files[] = $fileData;
                }
            }
        }

        return $this->engineReaction(1, [
                'files'            => $files,
                'currentDir'        => $currentDir,
                'currentFolderData'    => $currentFolderData
            ]);
    }

    /**
      * Process delete file
      *
      * @param array   $inputData
      *
      * @return array
      *---------------------------------------------------------------- */
    
    public function processDeleteFile($inputData)
    {
        // Check if file empty
        if (!__ifIsset($inputData['filename'])) {
            return $this->engineReaction(2, null, __tr('File Format Is Invalid.'));
        }

        $fileDirPath    = $this->getDirPath().$inputData['filename'];

        // if check this file not exist
        if (!File::exists($fileDirPath)) {
            return $this->engineReaction(2, null, __tr('Requested File Not Found.'));
        }

        // Check if file is image
        if (getimagesize($fileDirPath) != false) {
            $pathinfo           =  pathinfo($fileDirPath);
            $imageThumbPath    = $this->getThumbnailDirPath().'/'.$pathinfo['basename'];

            if (File::exists($imageThumbPath)) {
                File::delete($imageThumbPath);
            }
        }

        // if check file deleted
        if (File::delete($fileDirPath)) {
            return $this->engineReaction(1, null, __tr('File Deleted Duccessfully.'));
        }

        return $this->engineReaction(2, null, __tr('Error In File Delete Process.'));
    }

    /**
      * Process download file
      *
      * @param array   $inputData
      *
      * @return array
      *---------------------------------------------------------------- */
    
    public function processDownloadFile($inputData)
    {
        // Check if file empty
        if (!__ifIsset($inputData['filename'])) {
            return $this->engineReaction(2, null, __tr('File Format Is Invalid.'));
        }

        $fileDirPath    = $this->getDirPath().$inputData['filename'];

        // if check this file not exist
        if (!File::exists($fileDirPath)) {
            return $this->engineReaction(2, null, __tr('Requested File Does Not Found.'));
        }

        return $this->engineReaction(1, ['filename' => $fileDirPath]);
    }

    /**
      * Process add new folder request
      *
      * @param array   $inputData
      *
      * @return array
      *---------------------------------------------------------------- */
    
    public function processAddFolder($inputData)
    {
        $filesDirPath    = $this->getDirPath();

        $newFolderName  = $inputData['name'];

        // Check if entered folder name is reserved folder names or not
        if (in_array(strtolower($newFolderName), config('__file_manager.reserved_folder_names'))) {
            return $this->engineReaction(2, null, __tr('Entered Folder Name Is Reserved.'));
        }

        if (__ifIsset($inputData['currentDir'])) {
            $currentDir      = $inputData['currentDir'];

            $reuestedDirPath = $filesDirPath.$currentDir.'/';

            if (!File::isDirectory($reuestedDirPath)) {
                return $this->engineReaction(2, null, __tr('Invalid Folder.'));
            }

            $filesDirPath = $reuestedDirPath;
        } else {
            $reuestedDirPath = $filesDirPath;
        }

        $newFolderPath = $filesDirPath.$newFolderName;

        if (File::isDirectory($newFolderPath)) {
            return $this->engineReaction(2, null, __tr('__folderName__ Folder Already Exist In This Directory Level.', ['__folderName__' => $newFolderName]));
        }

        // if check new directory created
        if (File::makeDirectory($newFolderPath, $mode = 0777, true, true)) {
            return $this->engineReaction(1, null, __tr('Folder Added Successfully.'));
        }

        return $this->engineReaction(2, null, __tr('Error In Folder Creation Process.'));
    }

    /**
      * Process delete file
      *
      * @param array   $inputData
      *
      * @return array
      *---------------------------------------------------------------- */
    
    public function processDeleteFolder($inputData)
    {
        // Check if file empty
        if (!__ifIsset($inputData['folderName'])) {
            return $this->engineReaction(2, null, __tr('Invalid Folder.'));
        }

        $fileDirPath    = $this->getDirPath().$inputData['folderName'];

        // if check this is directory or not
        if (!File::isDirectory($fileDirPath)) {
            return $this->engineReaction(2, null, __tr('Invalid Folder.'));
        }

        // if check file deleted
        if (File::deleteDirectory($fileDirPath)) {
            return $this->engineReaction(1, null, __tr('Folder Deleted Successfully.'));
        }

        return $this->engineReaction(2, null, __tr('Error In Folder Delete Process.'));
    }

    /**
      * Process rename folder request
      *
      * @param array   $inputData
      *
      * @return array
      *---------------------------------------------------------------- */
    
    public function processRenameFolder($inputData)
    {
        $filesDirPath        = $this->getDirPath();
        $existingName    = $inputData['existing_name'];
        $newFolderName      = $inputData['name'];
        $folderRelativePath = $inputData['folder_relative_path'];

        // if check this is directory or not
        if (!File::isDirectory($filesDirPath.$folderRelativePath)) {
            return $this->engineReaction(2, null, __tr('Folder Not Found.'));
        }

        // Check if entered folder name is reserved folder names or not
        if (in_array(strtolower($newFolderName), config('__file_manager.reserved_folder_names'))) {
            return $this->engineReaction(2, null, __tr('Entered Folder Name Is Reserved.'));
        }

        if (__ifIsset($inputData['currentDir'])) {
            $currentDir      = $inputData['currentDir'];

            $reuestedDirPath = $filesDirPath.$currentDir.'/';

            if (!File::isDirectory($reuestedDirPath)) {
                return $this->engineReaction(2, null, __tr('Invalid Folder.'));
            }

            $filesDirPath = $reuestedDirPath;
        } else {
            $reuestedDirPath = $filesDirPath;
        }

        $newFolderPath = $filesDirPath.$newFolderName;

        if (File::isDirectory($newFolderPath)) {
            return $this->engineReaction(2, null, __tr('__folderName__ Folder Already Exist In This Directory Level.', ['__folderName__' => $newFolderName]));
        }

        // if check new directory created
        if (rename($filesDirPath.$existingName, $newFolderPath)) {
            return $this->engineReaction(1, null, __tr('Folder Renamed Successfully.'));
        }

        return $this->engineReaction(2, null, __tr('Error While Renaming Folder.'));
    }

    /**
      * Process rename folder request
      *
      * @param array   $inputData
      *
      * @return array
      *---------------------------------------------------------------- */
    
    public function processRenameFile($inputData)
    {
        $filesDirPath        = $this->getDirPath();

        $existingName    = trim($inputData['existing_name']);
        $newFileName          = trim($inputData['name']);
        $fileRelativePath    = trim($inputData['file_relative_path']);

        // if check this is file or not
        if (!File::exists($filesDirPath.$fileRelativePath)) {
            return $this->engineReaction(2, null, __tr('File Not Found.'));
        }

        if (__ifIsset($inputData['currentDir'])) {
            $currentDir      = $inputData['currentDir'];

            $reuestedDirPath = $filesDirPath.$currentDir.'/';

            if (!File::isDirectory($reuestedDirPath)) {
                return $this->engineReaction(2, null, __tr('Invalid File.'));
            }

            $filesDirPath = $reuestedDirPath;
        } else {
            $reuestedDirPath = $filesDirPath;
        }

        $filePathInfo = pathinfo($filesDirPath.$fileRelativePath);

        $newFilePath = $filesDirPath.$newFileName.'.'.$filePathInfo['extension'];

        if (File::isDirectory($newFilePath)) {
            return $this->engineReaction(2, null, __tr('__filename__ File Already Exist In This Directory Level.', ['__filename__' => $newFileName]));
        }

        // Check if file is image
        if (getimagesize($filesDirPath.$existingName) != false) {
            $pathinfo           =  pathinfo($filesDirPath.$existingName);
            $imageThumbPath    = $this->getThumbnailDirPath().'/'.$pathinfo['basename'];

            if (File::exists($imageThumbPath)) {
                rename($imageThumbPath, $this->getThumbnailDirPath().'/'.$newFileName);
            }
        }

        // if check new directory created
        if (rename($filesDirPath.$existingName, $newFilePath)) {
            return $this->engineReaction(1, null, __tr('File Renamed successfully.'));
        }

        return $this->engineReaction(2, null, __tr('Error While Renaming File.'));
    }
}
