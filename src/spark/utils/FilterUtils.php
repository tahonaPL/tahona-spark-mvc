<?php

namespace spark\utils;

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
        } else if (is_object($param)) {
            return $param;
        } else if (isset($param)) {
            $escapedString  = $param;
//            $escapedString = self::escapeMysql($param);
            return htmlspecialchars($escapedString);
        } else {
            return null;
        }
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
