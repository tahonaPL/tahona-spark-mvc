<?php

namespace Spark\Utils;

class FilterUtils {

    public static function removeText($text, $textsToFilter) {
        $resultText = $text;
        foreach ($textsToFilter as $value) {
            $resultText = str_replace($value, "", $resultText);
        }
        return $resultText;
    }

    public static function filterVariable($param) {
        if (is_array($param)) {
            foreach ($param as $key => $value) {
                $param[$key] = self::filterVariable($value);
            }
            return $param;
        }

        if (is_object($param)) {
            return $param;
        }

        if (isset($param)) {
            $escapedString  = $param;
            return htmlspecialchars($escapedString);
        }

        return null;
    }

    /**
     * @param $param
     * @return string
     */
    private static function escapeMysql($param) {
        if (function_exists('mysql_real_escape_string')) {
            return mysql_real_escape_string($param);
        }
        return addslashes($param);
    }

}
