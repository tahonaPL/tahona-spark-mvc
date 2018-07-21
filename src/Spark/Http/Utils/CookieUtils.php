<?php
/**
 *
 *
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

    public static function getCookieValue($key) {
        return self::hasCookie($key) ? $_COOKIE[$key] : null;
    }

    public static function setCookie(string $key, string $value, $expire = 0, $path = '/', $domain = '', $secure = false, $httponly = false): void {
        if ($expire> 0) {
            $expire += time();
        }
        setcookie($key, $value, $expire, $path, $domain, $secure, $httponly);
    }

    public static function getAllCookies(): array {
        return Objects::isNotNull($_COOKIE) ? $_COOKIE : array();
    }
}