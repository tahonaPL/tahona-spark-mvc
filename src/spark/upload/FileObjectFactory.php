<?php
/**
 * Created by PhpStorm.
 * User: primosz67
 * Date: 29.07.14
 * Time: 21:42
 */

namespace spark\upload;


class FileObjectFactory {

    /**
     * @param $fileData
     * @return FileObject
     */
    public static function create($fileData) {
        $fileObject = new FileObject();
        $fileObject->setFileName(FileConstants::getName($fileData));
        $fileObject->setExtension(FileConstants::getExtension($fileData));
        $fileObject->setContentType(FileConstants::getContentType($fileData));
        $fileObject->setFilePath(FileConstants::getTmpPath($fileData));
        $fileObject->setSize(FileConstants::getSize($fileData));
        return $fileObject;
    }

} 