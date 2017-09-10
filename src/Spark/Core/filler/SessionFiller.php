<?php


namespace Spark\Core\filler;


use Spark\Http\Session;
use Spark\Http\utils\RequestUtils;

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