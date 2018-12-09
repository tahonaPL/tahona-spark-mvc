<?php
/**
 *
 *
 * Date: 28.03.15
 * Time: 01:12
 */

namespace Spark\Utils;


class Functions {

    /**
     * @param $key
     * @return callable
     */
    public static function getArrayValue($key) {
        return function ($collection) use ($key) {
            if (Collections::hasKey($collection, $key)) {
                return $collection[$key];
            }
            return null;
        };
    }

    /**
     * @param $propertyName
     * @return callable
     */
    public static function invokeGetMethod($propertyName): \Closure {
        return function ($obj) use ($propertyName) {
            return Objects::invokeGetMethod($obj, $propertyName);
        };
    }

    public static function invokeMethod($propertyName): \Closure {
        return function ($obj) use ($propertyName) {
            return Objects::invokeMethod($obj, $propertyName);
        };
    }


    /**
     * Synaptic sugar
     * @param $propertyName
     * @return callable
     */
    public static function get($propertyName): \Closure {
        return self::invokeGetMethod($propertyName);
    }

    public static function field($field): \Closure {
        return function ($obj) use ($field) {
            if (isset($obj->$field)) {
                return $obj->$field;
            }
            return null;
        };
    }

    public static function count(): \Closure {
        return function ($obj) {
            if (Objects::isArray($obj)) {
                return Collections::size($obj);
            }
            return 0;
        };
    }

    public static function getSameObject(): \Closure {
        return function ($o) {
            return $o;
        };
    }

    public static function getClassName(): \Closure {
        return function ($x) {
            return Objects::getClassName($x);
        };
    }

    public static function hasClassName($fullClassName): \Closure {
        return function ($x) use ($fullClassName) {
            return StringUtils::equals(Objects::getClassName($x), $fullClassName);
        };
    }

    /**
     * @return \Closure
     */
    public static function none(): \Closure {
        return function ($x) {
            //do nothing
            return null;
        };
    }

    public static function splObjectHash(): \Closure {
        return function ($x) {
            return spl_object_hash($x);
        };
    }

    public static function executeOn(object $obj, string $method): \Closure {
        return function ($x) use ($obj, $method) {
            return Objects::invokeMethod($obj, $method, [$x]);
        };
    }

}