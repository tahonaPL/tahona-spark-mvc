<?php

namespace spark\utils;

use spark\utils\DateUtils;
use spark\utils\BooleanUtils;
use spark\utils\Objects;

class ValidatorUtils {

    public static function isArrayValid($array, $fieldsArray) {
        foreach ($fieldsArray as $field) {
            if (false == isset($array[$field]) || is_null($array[$field])) {
                return false;
            }
        }
        return true;
    }

    public static function isMailValid($mail) {
        return strpos($mail, "@") > 0;
    }

    public static function checkLength($text, $min, $max = null) {
        $length = strlen($text);
        return $length >= $min && ($max == null || $length <= $max);
    }

    public static function isZipCodeValid($x) {
        return BooleanUtils::isTrue(preg_match("/^(\d{2}-\d{3})$/", $x) === 1);

    }

    public static function isDate($date) {
        return Objects::isNotNull($date) && $date instanceof \DateTime;
    }

}
