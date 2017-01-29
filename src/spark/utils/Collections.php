<?php
/**
 * Created by PhpStorm.
 * User: primosz67
 * Date: 22.07.14
 * Time: 19:53
 */

namespace spark\utils;


use spark\common\collection\FluentIterables;
use spark\common\Optional;
use spark\utils\Asserts;

final class Collections {


    private function __construct() {
    }

    public static function sort(&$collections, Comparator $comparator) {
        if (false == empty($collections)) {
            usort($collections, array($comparator, 'compare'));
        }
    }

    /**
     *
     * function(left, right) return -1,0,1;
     *
     * return strcmp($object1->getName(), $object2->getName()) < 0;
     *
     * @param $collections
     * @param null $function
     */
    public static function sortFunc(&$collections, \Closure $function = null) {
        if (Objects::isNotNull($function)) {
            usort($collections, $function);
        }
    }

    public static function size($collection) {
        Asserts::notNull($collection);
        return count($collection);
    }

    public static function isNotEmpty($collection) {
        return false == self::isEmpty($collection);
    }

    /**
     * PHP 5 compatibility
     * @param $collection
     * @return bool
     */
    public static function isEmpty($collection) {
        $isNull = Objects::isNull($collection);
        if ($isNull) {
            return true;
        }

        $count = count($collection);
        return $count === 0;
    }

    public static function exist($collection, $key) {
        return in_array($key, $collection);
    }

    public static function add($collection, $index, $element) {
        $result = array();
        foreach ($collection as $v) {
            if (Collections::size($result) === $index) {
                $result[] = $element;
            }
            $result[] = $v;
        }

        //add as last element
        if (Collections::size($collection) <= $index) {
            $result[] = $element;
        }

        return $result;
    }

    public static function addAllAndGroup(&$collection, $addElements) {
        $result = Collections::map($collection, function ($el) {
            return array($el);
        });

        foreach ($addElements as $k => $el) {
            $key = "" . $k;
            if (false === Collections::hasKey($result, $key)) {
                $result[$key] = array();
            }
            $result[$key][] = $el;
        }
        $collection = $result;
        return $result;
    }

    /**
     * @param $array
     * @param callable $func
     * @return array
     */
    public static function map($array = array(), \Closure $func = null) {

        $resultArray = array();
        foreach ($array as $k => $el) {
            $resultArray[$k] = $func($el);
        }
        return $resultArray;
    }

    public static function hasKey($collection = array(), $key) {
        return array_key_exists($key, $collection);
    }

    public static function removeByIndex(&$collection, $index) {
        return array_slice($collection, 1, $index);
    }

    /**
     * @deprecated
     * @param array $collection
     * @param $key
     * @return bool
     */
    public static function hasElement($collection = array(), $key) {
        return self::hasKey($collection, $key);
    }

    public static function getKeys($array) {
        Asserts::notNull($array);
        return array_keys($array);
    }

    public static function convertToMap($array, \Closure $keyFunc) {
        $resultMap = array();
        foreach ($array as $v) {
            $resultMap[$keyFunc($v)] = $v;
        }
        return $resultMap;
    }

    public static function removeAllByKeys(&$map, $keysToRemove = array()) {
        foreach ($keysToRemove as $key) {
            if (Collections::hasKey($map, $key)) {
                unset($map[$key]);
            }
        }
    }

    /**
     * @param array $array
     * @return FluentIterables
     */
    public static function builder($array = array()) {
        return new FluentIterables($array);
    }

    /**
     * @param array $array
     * @param callable $func
     * @return array
     */
    public static function groupBy($array = array(), \Closure $func) {
        $result = array();
        foreach ($array as $obj) {
            $key = $func($obj);
            if (false === Collections::hasKey($result, $key)) {
                $result[$key] = array();
            }

            $result[$key][] = $obj;
        }

        return $result;
    }

    /**
     * @param array $array
     * @param callable $func
     * @return array
     */
    public static function flatMap($array = array(), \Closure $func, $mergeKeys = false) {
        $map = Collections::map($array, $func);
        return Collections::flat($map, $mergeKeys); // flat if in map are arrays
    }

    /**
     * @param $array
     */
    public static function flat($array = array(), $mergeKeys = false) {
        $result = array();
        foreach ($array as $subArray) {
            if (Objects::isArray($subArray)) {
                if ($mergeKeys) {
                    Collections::addAllOrReplace($result, $subArray);
                } else {
                    Collections::addAll($result, $subArray);
                }
            } else {
                $result[] = $subArray;
            }
        }
        return $result;
    }

    public static function addAllOrReplace(&$collection, $addElements = array()) {
        foreach ($addElements as $k => $el) {
            $collection["" . $k] = $el;
        }
    }

    public static function addAll(&$collection, $addElements = array()) {
        foreach ($addElements as $el) {
            $collection[] = $el;
        }
    }

    public static function getValue($array = array(), $key) {
       return self::getValueOrDefault($array, $key, array());
    }

    public static function getValueOrDefault($array = array(), $key, $default = null) {
        if (Collections::hasKey($array, $key)) {
            return $array[$key];
        } else {
            return $default;
        }
    }

    public static function noneMatch($collection = array(), \Closure $func) {
        return false === self::anyMatch($collection, $func);
    }

    /**
     * @param array $collection
     * @param callable $func
     * @return bool
     */
    public static function anyMatch($collection = array(), \Closure $func) {
        self::filter($collection, $func);
        foreach ($collection as $val) {
            if ($func($val)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param $array
     * @param callable $func - return boolean
     * @return array
     */
    public static function filter($array, \Closure $func) {
        $resultArray = array();
        foreach ($array as $k => $el) {
            if ($func($el)) {
                $resultArray[$k] = $el;
            }
        }
        return $resultArray;
    }

    /**
     * @param $collection
     * @param callable $func
     * @return Optional
     */
    public static function findFirst($collection = array(), \Closure $func) {
        foreach ($collection as $v) {
            if ($func($v)) {
                return Optional::of($v);
            }
        }
        return Optional::absent();
    }

    /**
     * @param $getCode
     * @param $ticketCodes boolean
     */
    public static function contains($code, $ticketCodes = array()) {
        return in_array($code, $ticketCodes);
    }

    public static function setKeys(&$collection = array(), $keys = array(), $value) {
        foreach ($keys as $key) {
            $collection[$key] = $value;
        }
        return $collection;
    }

    public static function setValues(&$collection, $values) {
        $index = 0;
        foreach($collection as $key=>$value) {
            $collection[$key] = $values[$index];
            $index++;
        }
        return $collection;
    }

}