<?php
/**
 * Created by PhpStorm.
 * User: primosz67
 * Date: 08.07.14
 * Time: 21:37
 */

namespace spark\core\routing;


class RoutingException extends \Exception {
    const BAD_REQUEST_METHOD_TYPE = "Bad request method type.";


    public static function badMethodType() {
        return new RoutingException(RoutingException::BAD_REQUEST_METHOD_TYPE);
    }


} 