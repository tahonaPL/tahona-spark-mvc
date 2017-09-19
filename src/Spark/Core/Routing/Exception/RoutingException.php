<?php
/**
 *
 *
 * Date: 08.07.14
 * Time: 21:37
 */

namespace Spark\Core\Routing\Exception;


class RoutingException extends \Exception {
    const BAD_REQUEST_METHOD_TYPE = "Bad request method type.";


    public static function badMethodType() {
        return new RoutingException(RoutingException::BAD_REQUEST_METHOD_TYPE);
    }


} 