<?php
/**
 *
 *
 * Date: 01.01.16
 * Time: 11:11
 */

namespace Spark\Routing;


use Spark\Common\Collection\FluentIterables;
use Spark\Core\Routing\RoutingDefinition;
use Spark\Routing;
use Spark\Utils\Collections;

class RoutingInfo {

    public const NAME = 'routingInfo';
    /**
     * @var Routing
     */
    private $routing;

    function __construct(Routing $routing) {
        $this->routing = $routing;
    }

    public function getRoutingDefinitions() : FluentIterables {
        return $this->routing->getDefinitions();
    }


} 