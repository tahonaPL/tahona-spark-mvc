<?php
/**
 * Created by PhpStorm.
 * User: primosz67
 * Date: 03.04.16
 * Time: 22:04
 */

namespace Spark\Http\Utils;

use Spark\Http\Utils\RequestUtils;
use Spark\Utils\Collections;
use Spark\Utils\Objects;

class CookieUtils {

    public static function hasCookie($key) {
        return Objects::isNotNull($_COOKIE) && Collections::hasKey($_COOKIE, $key);
    }

    public static function removeCookie($key) {
        if (Collections::hasKey($_COOKIE, $key)) {
            unset($_COOKIE[$key]);
            setcookie($key, null, -1, '/');
            return true;
        }
        return false;
    }

    public static function getCookieValue($key){
        return self::hasCookie($key) ? $_COOKIE[$key] : null;
    }

}