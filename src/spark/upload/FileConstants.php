<?php
/**
 * Created by PhpStorm.
 * User: primosz67
 * Date: 29.07.14
 * Time: 21:39
 */

namespace spark\upload;


use spark\utils\Collections;
use spark\utils\StringUtils;

class FileConstants {

    const TYPE_FIELD = "type";
    const CONTENT_TYPE_FIELD = "contentType";
    const NAME = "name";
    const TMP_NAME_FIELD = "tmp_name";
    const ERROR_FIELD = "error";
    const SIZE = "size";

    public static function getName($files) {
        $field = self::NAME;
        return self::getField($files, $field);
    }

    /**
     * @param $files
     * @param $field
     * @return string
     */
    private static function getField($files, $field) {
        if (empty($files[$field])) {
            return "";
        } else {
            return $files[$field];
        }
    }

    public static function getContentType($fileData) {
        $field = self::getField($fileData, self::CONTENT_TYPE_FIELD);
        if (false == empty($field)) {
            return self::getField($fileData, self::CONTENT_TYPE_FIELD);
        } else {
            return self::getField($fileData, self::TYPE_FIELD);
        }
    }

    public static function getSize($fileData) {
        return self::getField($fileData, self::SIZE);
    }

    public static function getTmpPath($fileData) {
        return self::getField($fileData, self::TMP_NAME_FIELD);
    }

    public static function getExtension($fileData) {
        $name = self::getName($fileData);
        $nameParts = StringUtils::split($name, ".");
        $size = Collections::size($nameParts);
        if ($size > 1) {
            return $nameParts[$size - 1];
        }
        return null;
    }
}