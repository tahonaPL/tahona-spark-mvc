<?php
/**
 * Created by PhpStorm.
 * User: primosz67
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
    public static function invokeGetMethod($propertyName) {
        return function ($obj) use ($propertyName) {
            return Objects::invokeGetMethod($obj, $propertyName);
        };
    }

    /**
     * Synaptic sugar
     * @param $propertyName
     * @return callable
     */
    public static function get($propertyName) {
        return self::invokeGetMethod($propertyName);
    }

    public static function field($field) {
        return function ($obj) use ($field) {
            if (isset($obj->$field)) {
                return $obj->$field;
            }
            return null;
        };
    }

    public static function count() {
        return function ($obj) {
            if (Objects::isArray($obj)) {
                return Collections::size($obj);
            }
            return 0;
        };
    }

    public static function getSameObject() {
        return function ($o) {
            return $o;
        };
    }

    public static function getClassName() {
        return function ($x) {
            return Objects::getClassName($x);
        };
    }

    public static function hasClassName($fullClassName) {
        return function ($x) use ($fullClassName) {
            return StringUtils::equals(Objects::getClassName($x), $fullClassName);
        };
    }

    /**
     * @return \Closure
     */
    public static function none() {
        return function($x) {

        };
    }


}