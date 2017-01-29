<?php
/**
 * Created by PhpStorm.
 * User: primosz67
 * Date: 29.07.14
 * Time: 22:40
 */

namespace spark\utils;


use spark\utils\Asserts;
use spark\upload\FileObject;

class FileUtils {

    public static function createFolderIfNotExist($dir) {
        $directoryPath = self::getAbsolutePath($dir);

        $isDirExist = self::isExist($directoryPath);
        if (false == $isDirExist) {
            mkdir($directoryPath);
        }
    }

    /**
     * Is Directory Exist
     * @deprecated
     * @param $directoryPath
     * @return bool
     */
    public static function isExist($directoryPath) {
        return file_exists($directoryPath) && is_dir($directoryPath);
    }
    public static function isFileExist($filePath) {
        return file_exists($filePath) ;
    }

    public static function moveFileToDir(FileObject $file, $directoryPath) {
        $directoryPath = self::getAbsolutePath($directoryPath);

        $newFilePath = $directoryPath."/".$file->getFileName();
        $success = copy($file->getFilePath(), $newFilePath);

        if ($success) {
            unlink($file->getFilePath());
            $file->setFilePath($newFilePath);
        } else {
            throw new MoveFileException("cant move file to :".$directoryPath." check write permission.");
        }
    }

    public static function moveFile(FileObject $file, $fullPath) {
        $newFilePath = self::getAbsolutePath($fullPath);

        //bug with get absolute file path.
        if (false == self::isExist($newFilePath)) {
            $newFilePath = $fullPath;
        }

        $success = copy($file->getFilePath(), $newFilePath);

        if ($success) {
            unlink($file->getFilePath());
            $file->setFilePath($newFilePath);
        } else {
            throw new MoveFileException("cant move file to :".$fullPath." check write permission.");
        }
    }

    public static function getAbsolutePath($dir) {
        if (strpos($dir, "/") === 0) {

            $rootAbsolutePath = realpath(__ROOT__);

            if (strpos($rootAbsolutePath, "\\") > 0) {
                //windows
                return $rootAbsolutePath.$dir;

            } else {
                //linux
                return realpath($rootAbsolutePath.$dir);
            }

        } else {
            return $dir;
        }
    }

    public static function getExtension(FileObject $fileObject) {
        if (strpos($fileObject->getFileName(), ".") > 0) {
            $exploded = explode(".", $fileObject->getFileName());
            return $exploded[1];
        } else {
            return "";
        }
    }

    public static function getFileList($path) {
        Asserts::checkArgument(is_dir($path));
        $scandir = scandir($path);
        $scandir = array_diff($scandir, array('.', '..'));
        $result = array();

        foreach ($scandir as $file) {
            if (is_file($path."/".$file)) {
                $result[] = $file;
            }
        }

        return $result;
    }

    /**
     * @param $path
     */
    public static function getFileContent($path) {
        return file_get_contents($path);
    }
}