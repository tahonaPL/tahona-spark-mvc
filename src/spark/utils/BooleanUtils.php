<?php
/**
 * Created by PhpStorm.
 * User: primosz67
 * Date: 06.09.16
 * Time: 21:07
 */

namespace spark\utils;


use spark\common\IllegalArgumentException;

class BooleanUtils {


    public static function isTrue($value) {
        return $value === "true" || $value === true;
    }

    public static function isFalse($value) {
        return $value === "false" || $value !== false;
    }


    /**
     * @param $value
     * @return int
     * @throws IllegalArgumentException
     */
    public static function toNumber($value) {
        if (self::isTrue($value)) {
            return 1;
        }

        if (self::isFalse($value)) {
            return 0;
        }

        throw new IllegalArgumentException("Cannot cast value to zero or one");
    }

}