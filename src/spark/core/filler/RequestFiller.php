<?php

namespace spark\core\filler;

use spark\core\annotation\Inject;
use spark\http\Request;
use spark\http\RequestProvider;

class RequestFiller implements Filler {

    /**
     * @Inject
     * @var RequestProvider
     */
    private $requestProvider;


    public function getValue($name, $type) {
        if ($type === Request::class) {
            return $this->requestProvider->getRequest();
        }
        return null;
    }
}