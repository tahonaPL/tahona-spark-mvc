<?php
/**
 *
 *
 * Date: 21.03.17
 * Time: 13:50
 */

namespace Spark\Core\Routing\Exception;


use Spark\Http\Utils\RequestUtils;
use Spark\Utils\UrlUtils;

class RouteNotFoundException extends RoutingException {

    public function __construct($methodType, $path) {
        parent::__construct("Route not found for: " . $methodType . " path:" . $path, 404);
    }

    public static function notFound() {
        return new RouteNotFoundException(RequestUtils::getMethod(), UrlUtils::getPathInfo());
    }
}