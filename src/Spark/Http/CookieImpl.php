<?php
/**
 *
 * 
 * Date: 2018-03-17
 * Time: 14:12
 */

namespace Spark\Http;

use Spark\Http\Utils\CookieUtils;

class CookieImpl implements Cookie {

    public function set($key, $value, $expire=0): Cookie {
        CookieUtils::setCookie($key, $value, $expire);
        return $this;
    }

    public function setAll(array $array): Cookie {
        foreach ($array as $key => $value) {
            $this->set($key, $value);
        }
        return $this;
    }

    public function getParams(): array {
        return CookieUtils::getAllCookies();
    }

    public function has($key): bool {
        return CookieUtils::hasCookie($key);
    }

    public function get($key) {
        return CookieUtils::getCookieValue($key);
    }

    public function remove($key): Cookie {
        CookieUtils::removeCookie($key);
        return $this;
    }
}