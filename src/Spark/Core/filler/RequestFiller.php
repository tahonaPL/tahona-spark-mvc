<?php

namespace Spark\Core\filler;

use Spark\Core\annotation\Inject;
use Spark\Http\Request;
use Spark\Http\RequestProvider;

class RequestFiller implements Filler {

    /**
     * @Inject
     * @var RequestProvider
     */
    private $requestProvider;


    public function getValue($name, $type) {
        if ($name === "request" || $type === Request::class) {
            return $this->requestProvider->getRequest();
        }
        return null;
    }

    public function getOrder() {
        return 100;
    }
}