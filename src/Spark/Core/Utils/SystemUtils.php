<?php

namespace Spark\Core\Utils;

use Spark\Utils\Collections;
use Spark\Utils\StringUtils;

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


    /**
     * @return string|null
     */
    public static function getProfile() {
        return self::getParam("profile");
    }
}