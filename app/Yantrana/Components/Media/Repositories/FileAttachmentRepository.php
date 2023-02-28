<?php
/*
* FileAttachmentRepository.php - Repository file
*
* This file is part of the Media component.
*-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\Media\Repositories;

use App\Yantrana\Base\BaseRepository;

use App\Yantrana\Components\Media\Models\FileAttachmentModel;
use App\Yantrana\Components\Media\Interfaces\FileAttachmentRepositoryInterface;

class FileAttachmentRepository extends BaseRepository implements FileAttachmentRepositoryInterface
{

    /**
      * Fetch the record of FileAttachment
      *
      * @param    int || string $idOrUid
      *
      * @return    eloquent collection object
      *---------------------------------------------------------------- */

    public function fetch($idOrUid)
    {
        if (is_numeric($idOrUid)) {
            return FileAttachmentModel::where('_id', $idOrUid)->first();
        }

        return FileAttachmentModel::where('_uid', $idOrUid)->first();
    }


    /**
      * Store service msg attachment
      * @param array $inputData
      * @return  eloquent collection object
      *---------------------------------------------------------------- */

    public function storeAttachment($inputData)
    {
        $fileAttachmentModel = new FileAttachmentModel;

        if ($fileAttachmentModel->prepareAndInsert($inputData)) {
            return true;
        }

        return false;
    }

    /**
      * Delete Attachment
      * @param array $inputData
      * @return  eloquent collection object
      *---------------------------------------------------------------- */
    public function deleteAttachment($attachment)
    {
        if ($attachment->delete()) {
            return true;
        }

        return false;
    }

    /**
      * Delete Attachments
      * @param array $inputData
      * @return  eloquent collection object
      *---------------------------------------------------------------- */
    public function deleteAttachments($issueId)
    {
        return FileAttachmentModel::where('issues__id', $issueId)->delete();
    }

    /**
      * Delete Attachments
      * @param array $inputData
      * @return  eloquent collection object
      *---------------------------------------------------------------- */
    public function deleteRequirementAttachments($requirementId)
    {
        return FileAttachmentModel::where('requirements__id', $requirementId)->delete();
    }

    /**
      * Fetch Attachment by issue id
      * @param array $inputData
      * @return  eloquent collection object
      *---------------------------------------------------------------- */
    public function fetchAttachmentByIssueId($issueId)
    {
        return FileAttachmentModel::where('issues__id', $issueId)->get();
    }
}
