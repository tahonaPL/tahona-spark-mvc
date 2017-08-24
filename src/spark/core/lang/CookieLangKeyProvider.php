<?php


namespace spark\core\lang;


use spark\http\utils\CookieUtils;
use spark\utils\Asserts;
use spark\utils\Objects;

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