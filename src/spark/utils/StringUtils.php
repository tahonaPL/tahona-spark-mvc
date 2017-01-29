<?php
/**
 * Created by PhpStorm.
 * User: primosz67
 * Date: 27.11.14
 * Time: 00:27
 */

namespace spark\utils;


class StringUtils {


    const SPACE = " ";

    public static function startsWith($text, $prefix) {
        $strpos = strpos($text, $prefix);
        return $strpos === 0;
    }

    public static function contains($text, $search, $ignoreCase = false) {
        if ($ignoreCase) {
            $text = strtolower($text);
            $search = strtolower($search);
        }

        $strpos = strpos($text, $search);
        return $strpos !== false;
    }

    /**
     * @deprecated
     * @param $indexFrom
     * @param $indexTo
     * @param $string
     * @return string
     */
    public static function substr($indexFrom, $indexTo, $string) {
        return substr($string, $indexFrom, $indexTo);
    }


    public static function subString($string, $indexFrom, $length = null) {
        return substr($string, $indexFrom, $length);
    }

    /**
     * @deprecated
     * @param $string
     * @param $indexFrom
     * @param null $length
     * @return string
     */
    public static function sub($string, $indexFrom, $length = null) {
        return substr($string, $indexFrom, $length);
    }

    public static function isNotBlank($text) {
        return false === self::isBlank($text);
    }

    public static function isBlank($text) {
        return empty($text);
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

    /**
     * (PHP 4, PHP 5)<br/>
     * Binary safe string comparison
     * @link http://php.net/manual/en/function.strcmp.php
     * @param string $str1 <p>
     * The first string.
     * </p>
     * @param string $str2 <p>
     * The second string.
     * </p>
     * @return int &lt; 0 if <i>str1</i> is less than
     * <i>str2</i>; &gt; 0 if <i>str1</i>
     * is greater than <i>str2</i>, and 0 if they are
     * equal.
     */
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
    public static function equals($string1, $string2) {
        return $string1 === $string2;
    }

    public static function join($joiner, $stringArray = array(), $missEmpty = false) {
        if ($missEmpty) {
            $stringArray = array_filter($stringArray);

            return join($joiner, $stringArray);
        } else {
            return join($joiner, $stringArray);
        }
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

}