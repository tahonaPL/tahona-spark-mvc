<?php
/**
 * Created by PhpStorm.
 * User: primosz67
 * Date: 21.12.14
 * Time: 23:46
 */

namespace spark\utils;


use spark\utils\Asserts;
use tahona\shop\client\domain\Client;

class Objects {

    public static function isNotNull($obj) {
        return isset($obj) && false === is_null($obj);
    }

    public static function isNull($obj) {
        return is_null($obj) || false === isset($obj);
    }

    public static function isArray($obj) {
        return is_array($obj);
    }

    public static function isString($obj) {
        return is_string($obj);
    }

    public static function invokeMethod($obj, $propertyName) {
        $getMethod = $propertyName;
        return $obj->$getMethod();
    }

    public static function invokeGetMethod($obj, $propertyName) {
        $getMethod = $propertyName;
        if (false == StringUtils::startsWith($propertyName, "get")) {
            $getMethod = "get" . ucfirst($propertyName);
        }
        return $obj->$getMethod();
    }

    public static function hasMethod($obj, $methodName) {
        return method_exists($obj, $methodName);
    }

    public static function hasProperty($obj, $propertyName) {
        return property_exists($obj, $propertyName);
    }

    public static function getSimpleClassName($obj) {
        Asserts::notNull($obj);
        return StringUtils::join('', array_slice(StringUtils::split(get_class($obj), '\\'), -1));
    }

    public static function getClassName($obj) {
        return get_class($obj);
    }


} 