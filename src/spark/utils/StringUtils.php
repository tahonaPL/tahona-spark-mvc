<?php

namespace spark\utils;


class StringUtils {

    const SPACE = ' ';

    public static function startsWith($text, $prefix) {
        return strpos($text, $prefix) === 0;
    }

    public static function contains($text, $search, $ignoreCase = false) {
        if ($ignoreCase) {
            $text = strtolower($text);
            $search = strtolower($search);
        }

        return strpos($text, $search) >= 0;
    }


    public static function subString(string $string, int $indexFrom, int $length = null) :bool {
        return substr($string, $indexFrom, $length);
    }

    public static function isNotBlank($text) :bool {
        return preg_match('/\S/', $text);
    }

    public static function isBlank($text) :bool {
        return !self::isNotBlank($text);
    }

    /**
     * Equal
     *
     * @param $string1
     * @param $string2
     * @return bool
     */
    public static function equalsIgnoreCase($string1, $string2) {
        return self::compareIgnoreCase($string1, $string2) === 0;
    }

    public static function compareIgnoreCase($string1, $string2) {
        return strcmp(strtolower(trim($string1)), strtolower(trim($string2)));
    }

    public static function escapeSpecialChar($string) {
        return preg_replace('/[^A-Za-z0-9\-]/', '', $string);
    }

    public static function replace($text, $searchText, $replacement) {
        return str_replace($searchText, $replacement, $text);
    }

    /**
     * Equal
     *
     * @param $string1
     * @param $string2
     * @return bool
     */
    public static function equals($string1, $string2):bool {
        return $string1 === $string2;
    }

    public static function join($joiner, $stringArray = array(), $missEmpty = false) :string {
        if ($missEmpty) {
            $stringArray = array_filter($stringArray);
        }
        return implode($joiner, $stringArray);
    }

    /**
     * @param $string
     * @param $delimiter
     * @return array
     */
    public static function split($string, $delimiter = null) {
        if (Objects::isNull($delimiter)) {
            return str_split($string);
        }
        return explode($delimiter, $string);
    }

    /**
     * @param $search
     * @param $replace
     * @return callable
     */
    public static function mapReplace($search, $replace) {
        return function ($el) use ($search, $replace) {
            return StringUtils::replace($el, $search, $replace);
        };
    }

    public static function mapTrim() {
        return function ($string) {
            return StringUtils::trim($string);
        };
    }

    public static function trim($string) {
        return trim($string);
    }

    public static function mapEscapeSpecialChar() {
        return function ($v) {
            return StringUtils::escapeSpecialChar($v);
        };
    }

    public static function wordWrap($value, $breakCharacter, $length) {
        return wordwrap($value, $length, $breakCharacter, true);
    }

    public static function mapSubstring($indexFrom, $length) {
        return function ($v) use ($indexFrom, $length) {
            return self::subString($v, $indexFrom, $length);
        };
    }

    public static function capitalize($string) {
        return self::betterUcFirst(self::trim($string));
    }

    public static function mapCapitalize() {
        return function ($s) {
            return self::capitalize($s);
        };
    }

    private static function betterUcFirst($in) {
        $x = explode(" ", $in);
        $x[0] = mb_convert_case($x[0], MB_CASE_TITLE, "UTF-8");
        $out = implode(" ", $x);
        return $out;

    }

    public static function predEquals($string) {
        return function ($x) use ($string) {
            return StringUtils::equals($x, $string);
        };
    }

    public static function length($value) {
        return strlen($value);
    }

    public static function lowerCase($str) {
        return strtolower($str);
    }

}