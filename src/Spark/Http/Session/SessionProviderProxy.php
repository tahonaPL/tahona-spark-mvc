<?php
/**
 * Date: 21.07.18
 * Time: 16:51
 */

namespace Spark\Http\Session;


use Spark\Core\Annotation\Inject;
use Spark\Core\Provider\BeanProvider;
use Spark\Http\Session;

class SessionProviderProxy implements SessionProvider {

    /**
     * @Inject
     * @var SessionProvider
     */
    private $defaultSessionProvider;


    public function getOrCreateSession(): Session {
        return $this->getSessionProvider()->getOrCreateSession();
    }

    public function getSession(): Session {
        return $this->getSessionProvider()->getSession();
    }

    public function hasSession(): bool {
        return $this->getSessionProvider()->hasSession();
    }

    /**
     * @return mixed
     */
    private function getSessionProvider(): SessionProvider {
        return $this->defaultSessionProvider;
    }
}