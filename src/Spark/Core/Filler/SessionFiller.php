<?php


namespace Spark\Core\Filler;


use Spark\Http\Session;
use Spark\Http\Session\SessionProvider;

class SessionFiller implements Filler {

    /**
     * @var SessionProvider
     */
    private $sessionProvider;

    public function getValue($name, $type) {
        if ($name === "session" || $type === Session::class) {
            return $this->sessionProvider->getOrCreateSession();
        }
        return null;
    }

    public function getOrder() {
        return 101;
    }
}