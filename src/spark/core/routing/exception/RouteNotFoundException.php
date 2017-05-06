<?php
/**
 * Created by PhpStorm.
 * User: primosz67
 * Date: 21.03.17
 * Time: 13:50
 */

namespace spark\core\routing\exception;




class RouteNotFoundException extends RoutingException {

    public function __construct($methodType, $path) {
        parent::__construct("Route not found for: " . $methodType . " path:" . $path, 404);
    }
}