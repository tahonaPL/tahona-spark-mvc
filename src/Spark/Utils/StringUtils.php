<?php
declare(strict_types=1);

namespace Spark\Utils;


final class StringUtils {

    const SPACE = ' ';
    const EMPTY = '';


    private function __construct() {
    }

    public static function startsWith($text, $prefix) {
        return strpos($text, $prefix) === 0;
    }

    public static function contains($text, $search, $ignoreCase = false): bool {
        if ($ignoreCase) {
            $text = strtolower($text);
            $search = strtolower($search);
        }

        $pos = strpos($text, $search);
        return $pos > 0 || $pos === 0;
    }

    public static function substring(string $string = null, int $indexFrom, int $length = null) {
        if (Objects::isNotNull($string)) {
            $substr = substr($string, $indexFrom, $length);
            return Objects::isString($substr) ? $substr : null;
        }
        return null;
    }

    public static function isNotBlank(string $text = null): bool {
        return (bool)preg_match('/\S/', (string)$text);
    }

    public static function isBlank(string $text = null): bool {
        return Objects::isNull($text) ||
            !(bool)(preg_match('/\S/', (string)$text));
    }

    public static function equalsIgnoreCase($string1, $string2): bool {
        return self::compareIgnoreCase($string1, $string2) === 0;
    }

    public static function compareIgnoreCase($string1, $string2) {
        return strcmp(strtolower(trim($string1)), strtolower(trim($string2)));
    }

    public static function escapeSpecialChar($string): string {
        return preg_replace('/[^A-Za-z0-9\-]/', '', $string);
    }

    public static function replace($text, $searchText, $replacement) {
        return str_replace($searchText, $replacement, $text);
    }

    public static function replaceWithRegExp($text, $regExp, $replacement) {
        return preg_replace($regExp, $replacement, $text);
    }

    /**
     * Equal
     *
     * @param $string1
     * @param $string2
     * @return bool
     */
    public static function equals($string1, $string2): bool {
        return $string1 === $string2;
    }

    public static function join($joiner, $stringArray = array(), $missEmpty = false): string {
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
    public static function split(string $string, $delimiter = null) {
        if (Objects::isNull($delimiter)) {
            return str_split($string);
        }
        return explode($delimiter, $string);
    }


    public static function trim(string $string = null) {
        if (Objects::isNotNull($string)) {
            return trim($string);
        }
        return null;
    }

    public static function wordWrap($value, $breakCharacter, $length) {
        return wordwrap($value, $length, $breakCharacter, true);
    }


    public static function capitalize($string) {
        return self::betterUcFirst(self::trim($string));
    }


    private static function betterUcFirst($in) {
        $x = explode(" ", $in);
        $x[0] = mb_convert_case($x[0], MB_CASE_TITLE, "UTF-8");
        return implode(" ", $x);

    }


    public static function length($value) {
        return strlen($value);
    }

    public static function lowerCase($str) {
        return strtolower($str);
    }

    public static function upperCase($str) {
        return strtoupper($str);
    }

}