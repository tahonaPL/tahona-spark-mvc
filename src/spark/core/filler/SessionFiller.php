<?php


namespace spark\core\filler;


use spark\http\Session;
use spark\http\utils\RequestUtils;

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