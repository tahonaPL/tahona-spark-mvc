<?php
/**
 * Created by PhpStorm.
 * User: primosz67
 * Date: 01.01.16
 * Time: 11:11
 */

namespace Spark\Routing;


use Spark\Core\Routing\RoutingDefinition;
use Spark\Routing;
use Spark\Utils\Collections;

class RoutingInfo {

    const NAME = "routingInfo";
    /**
     * @var Routing
     */
    private $routing;

    function __construct(Routing $routing) {
        $this->routing = $routing;
    }

    public function getRoutingDefinitions() {
        return $this->routing->getDefinitions();
    }


} 