<?php
/**
 * Created by PhpStorm.
 * User: primosz67
 * Date: 21.03.17
 * Time: 13:50
 */

namespace spark\core\routing\exception;


use spark\http\utils\RequestUtils;
use spark\utils\UrlUtils;

class RouteNotFoundException extends RoutingException {

    public function __construct($methodType, $path) {
        parent::__construct("Route not found for: " . $methodType . " path:" . $path, 404);
    }

    public static function notFound() {
        return new RouteNotFoundException(RequestUtils::getMethod(), UrlUtils::getPathInfo());
    }
}