<?php
/**
 * Created by PhpStorm.
 * User: primosz67
 * Date: 01.01.16
 * Time: 11:11
 */

namespace spark\routing;


use spark\core\routing\RoutingDefinition;
use spark\Routing;
use spark\utils\Collections;

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