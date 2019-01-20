<?php

namespace Spark\Core\Filler;

use Spark\Core\Annotation\Inject;
use Spark\Http\Cookie;
use Spark\Http\CookieImpl;
use Spark\Http\Request;
use Spark\Http\RequestProvider;

class CookieFiller implements Filler {

    private $cookie;

    public function __construct() {
        $this->cookie = new CookieImpl();
    }

    public function getValue($name, $type) {
        if ($name === "cookie" || $type === Cookie::class) {
            return $this->cookie;
        }
        return null;
    }

    public function getOrder() {
        return 103;
    }
}