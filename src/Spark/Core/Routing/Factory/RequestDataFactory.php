<?php
/**
 * Date: 21.07.18
 * Time: 13:00
 */

namespace Spark\Core\Routing\Factory;


use Spark\Core\Annotation\Inject;
use Spark\Core\Annotation\PostConstruct;
use Spark\Core\Routing\RequestData;
use Spark\Http\Session\SessionProvider;

class RequestDataFactory {

    /**
     * @Inject()
     * @var SessionProvider
     */
    private $sessionProvider;

    public function createRequestData(): RequestData {
        return new RequestData($this->sessionProvider);
    }

}