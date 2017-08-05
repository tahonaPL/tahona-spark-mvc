<?php
/**
 * Created by PhpStorm.
 * User: primosz67
 * Date: 02.02.17
 * Time: 18:40
 */

namespace spark\utils;


class StringFunctions {

    /**
     *
     * Uppercase
     * @return \Closure
     */
    public static function upper() {
        return function ($x) {
            return strtoupper($x);
        };

    }

    public static function replace($search, $replacement) {
        return function ($x) use ($search, $replacement) {
            return StringUtils::replace($x, $search, $replacement);
        };
    }

    public static function split($splitter) {
        return function ($x) use ($splitter) {
            return StringUtils::split($x, $splitter);
        };
    }

    public static function equals($value) {
        return function ($x) use ($value) {
            return StringUtils::equals($x, $value);
        };
    }

    public static function join($joiner) {
        return function ($x) use ($joiner) {
            return StringUtils::join($joiner, $x);
        };
    }

    public static function trim() {
        return function ($x) {
            return StringUtils::trim($x);
        };
    }

    public static function lowercase() {
        return function ($x) {
            return StringUtils::lowerCase($x);
        };
    }

    public static function escapeSpecialChars() {
        return function ($v) {
            return StringUtils::escapeSpecialChar($v);
        };
    }

    public static function substring($indexFrom, $length) {
        return function ($v) use ($indexFrom, $length) {
            return StringUtils::substring($v, $indexFrom, $length);
        };
    }


    public static function capitalize() {
        return function ($s) {
            return StringUtils::capitalize($s);
        };
    }
}