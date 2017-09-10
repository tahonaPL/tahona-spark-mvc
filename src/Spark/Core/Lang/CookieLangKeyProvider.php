<?php


namespace Spark\Core\Lang;


use Spark\Http\Utils\CookieUtils;
use Spark\Utils\Asserts;
use Spark\Utils\Objects;

class CookieLangKeyProvider implements LangKeyProvider {

    public $cookieKey;

    public function __construct($cookieKey) {
        Asserts::checkState(Objects::isString($cookieKey), "Cookie key need to be a string.");
        $this->cookieKey = $cookieKey;
    }

    public function getLang() {
        return CookieUtils::getCookieValue($this->cookieKey);
    }
}