<?php


namespace Spark\Core\Filler;


use Spark\Http\Session;
use Spark\Http\Utils\RequestUtils;

class SessionFiller implements Filler {

    public function getValue($name, $type) {
        if ($name === "session" || $type === Session::class) {
            return RequestUtils::getSession();
        }
        return null;
    }

    public function getOrder() {
        return 101;
    }
}