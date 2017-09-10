<?php
/**
 * Created by PhpStorm.
 * User: primosz67
 * Date: 29.07.14
 * Time: 22:40
 */

namespace Spark\Utils;


use Spark\Common\IllegalStateException;
use Spark\Common\Optional;
use Spark\Utils\Asserts;
use Spark\Upload\FileObject;

final class FileUtils {


    private function __construct() {
    }

    public static function createFolderIfNotExist($dir) {
        $directoryPath = self::getAbsolutePath($dir);

        $isDirExist = self::isDir($directoryPath);
        if (!$isDirExist) {
            mkdir($directoryPath);
        }
    }


    public static function exist($filePath):bool {
        return file_exists($filePath);
    }

    public static function moveFileToDir(FileObject $file, $directoryPath) {
        $directoryPath = self::getAbsolutePath($directoryPath);

        $newFilePath = $directoryPath . "/" . $file->getFileName();
        $success = copy($file->getFilePath(), $newFilePath);

        if ($success) {
            unlink($file->getFilePath());
            $file->setFilePath($newFilePath);
        } else {
            throw new MoveFileException("cant move file to :" . $directoryPath . " check write permission.");
        }
    }

    public static function moveFile(FileObject $file, $fullPath) {
        $newFilePath = self::getAbsolutePath($fullPath);

        //bug with get absolute file path.
        if (false == self::isDir($newFilePath)) {
            $newFilePath = $fullPath;
        }

        $success = copy($file->getFilePath(), $newFilePath);

        if ($success) {
            unlink($file->getFilePath());
            $file->setFilePath($newFilePath);
        } else {
            throw new IllegalStateException("cant move file to :" . $fullPath . " check write permission.");
        }
    }

    public static function getAbsolutePath($dir) {
        if (strpos($dir, "/") === 0) {

            $rootAbsolutePath = realpath(__ROOT__);

            if (strpos($rootAbsolutePath, "\\") > 0) {
                //windows
                return $rootAbsolutePath . $dir;

            } else {
                //linux
                return realpath($rootAbsolutePath . $dir);
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

    public static function getFilesInPath($path) {
        Asserts::checkArgument(is_dir($path), "Path should be point to directory ");

        $scandir = scandir($path);
        $scandir = array_diff($scandir, array('.', '..'));
        $result = array();

        foreach ($scandir as $file) {
            $result[] = $file;
        }
        return $result;
    }

    public static function getFileList($path) {
        return Collections::builder(self::getFilesInPath($path))
            ->filter(function ($file) use ($path) {
                return is_file($path . "/" . $file);
            })->get();
    }

    public static function getDirList($path, $exclude = array()) {
        return Collections::builder(self::getFilesInPath($path))
            ->filter(function ($file) use ($path, $exclude) {
                return is_dir($path . "/" . $file) && !Collections::isIn($file, $exclude);
            })->get();
    }


    /**
     * @param $path
     */
    public static function getFileContent($path) {
        return file_get_contents($path);
    }

    public static function getAllClassesInPath($dir) {

        $result = array();

        $fileNames = FileUtils::getFilesInPath($dir);

        foreach ($fileNames as $fileName) {
            $filePath = $dir . "/" . $fileName;
            if (is_dir($filePath)) {
                $subFiles = Collections::builder(self::getAllClassesInPath($filePath))
                    ->map(function ($x) use ($fileName) {
                        return self::toClassName($fileName . "/" . $x);
                    })->get();

                Collections::addAll($result, $subFiles);

            } else if (StringUtils::contains($fileName, ".php")) {
                $result[] = self::toClassName($fileName);
            }
        }

        return $result;
    }

    /**
     * @param $fileName
     * @return mixed
     */
    private static function toClassName($fileName) {
        return StringUtils::replace(StringUtils::replace($fileName, '/', '\\'), ".php", "");
    }

    public static function isDir($dir):bool {
        return is_dir($dir);
    }


    public static function isFile($filePath):bool {
        return is_file($filePath);
    }


}