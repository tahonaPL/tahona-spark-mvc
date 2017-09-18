<?php
/**
 * Created by PhpStorm.
 * User: primosz67
 * Date: 02.02.17
 * Time: 18:40
 */

namespace Spark\Utils;


class StringPredicates {

    public static function equals($value) {
        return function ($x) use ($value) {
            return StringUtils::equals($x, $value);
        };
    }

    public static function notBlank() {
        return function ($x) {
            return StringUtils::isNotBlank($x);
        };
    }

    public static function blank() {
        return function ($x) {
            return StringUtils::isBlank($x);
        };
    }
}