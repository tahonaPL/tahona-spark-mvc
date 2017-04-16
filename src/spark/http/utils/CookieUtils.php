<?php
/**
 * Created by PhpStorm.
 * User: primosz67
 * Date: 03.04.16
 * Time: 22:04
 */

namespace spark\http\utils;

use spark\http\utils\RequestUtils;
use spark\utils\Collections;
use spark\utils\Objects;

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

}