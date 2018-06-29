<?php
/**
 *
 *
 * Date: 02.02.17
 * Time: 18:40
 */

namespace Spark\Utils;


use function foo\func;

class StringFunctions {

    /**
     *
     * Uppercase
     * @return \Closure
     */
    public static function upper(): callable {
        return function ($x) {
            return strtoupper($x);
        };
    }

    public static function replace($search, $replacement): callable {
        return function ($x) use ($search, $replacement) {
            return StringUtils::replace($x, $search, $replacement);
        };
    }

    public static function replaceWithRegExp($regExp, $replacement): callable {
        return function ($x) use ($regExp, $replacement) {
            return StringUtils::replaceWithRegExp($x, $regExp, $replacement);
        };
    }

    public static function split($splitter): callable {
        return function ($x) use ($splitter) {
            return StringUtils::split($x, $splitter);
        };
    }

    public static function join($joiner): callable {
        return function ($x) use ($joiner) {
            return StringUtils::join($joiner, $x);
        };
    }

    public static function trim(): callable {
        return function ($x) {
            return StringUtils::trim($x);
        };
    }

    public static function lowercase(): callable {
        return function ($x) {
            return StringUtils::lowerCase($x);
        };
    }

    public static function escapeSpecialChars(): callable {
        return function ($v) {
            return StringUtils::escapeSpecialChar($v);
        };
    }

    public static function substring($indexFrom, $length): callable {
        return function ($v) use ($indexFrom, $length) {
            return StringUtils::substring($v, $indexFrom, $length);
        };
    }


    public static function capitalize(): callable {
        return function ($s) {
            return StringUtils::capitalize($s);
        };
    }

    public static function concat(string $string): callable {
        return function ($s) use ($string) {
            return $s . $string;
        };
    }
}