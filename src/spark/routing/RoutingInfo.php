<?php
/**
 * Created by PhpStorm.
 * User: primosz67
 * Date: 01.01.16
 * Time: 11:11
 */

namespace spark\routing;


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
        $definitions = $this->routing->getDefinitions();

        $routing = Collections::builder()
            ->addAll($definitions)
            ->flatMap(function ($x) {
                return $x;
            }, true)
            ->get();

        $result = array();
        foreach ($routing as $route => $def) {
            $arr = array("path" => $route);
            Collections::addAllOrReplace($arr, $def);
            $result[] = $arr;
        }

        return $result;
    }


} 