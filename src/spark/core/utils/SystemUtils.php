<?php

namespace spark\core\utils;

use spark\utils\Collections;
use spark\utils\StringUtils;

class SystemUtils {

    public static function isCommandLineInterface() {
        return php_sapi_name() === 'cli';
    }

    public static function getParam($key) {
        $params = self::getParams();
        return Collections::getValue($params, $key);
    }

    /**
     * @return array
     */
    private static function getParams() {
        $args = $_SERVER['argv'];
        $params = array();
        foreach ($args as $k => $v) {
            $keyValue = StringUtils::split($v, "=");

            if (count($keyValue) > 1) {
                $k = $keyValue[0];
                $v = $keyValue[1];
            }
            $params[$k] = $v;
        }
        return $params;
    }

}