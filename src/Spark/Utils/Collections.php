<?php
/**
 *
 *
 * Date: 22.07.14
 * Time: 19:53
 */

namespace Spark\Utils;

use Spark\Common\Collection\FluentIterables;
use Spark\Common\Optional;
use Spark\Utils\Asserts;

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

    public static function size(array $collection) {
        return count($collection);
    }

    public static function isNotEmpty(?array $collection): bool {
        return false === self::isEmpty($collection);
    }

    /**
     * PHP 5 compatibility
     * @param $collection
     * @return bool
     */
    public static function isEmpty(?array $collection): bool {
        $isNull = Objects::isNull($collection);
        if ($isNull) {
            return true;
        }

        $count = count($collection);
        return $count === 0;
    }

    public static function exist(array $collection, string $key): bool {
        return in_array($key, $collection);
    }

    public static function insert(array $collection, $index, $element): array {
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
        if (Objects::isNotNull($collection)) {
            return true === array_key_exists($key, $collection);
        }
        return false;
    }

    public static function removeByIndex(&$collection = array(), $index) {
        Asserts::checkState(Objects::isArray($collection));

        $i = 0;
        $toRemoveKey = null;

        foreach ($collection as $k => $v) {
            $toRemoveKey = $k;
            if ($i >= $index) {
                break;
            }
            $i++;
        }

        if (isset($collection[$toRemoveKey])) {
            $var = $collection[$toRemoveKey];
            unset($collection[$toRemoveKey]);
            return $var;
        }
        return null;
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
     *
     * @param array $array
     * @return FluentIterables
     * @deprecated use Stream
     */
    public static function builder($array = array()) {
        return self::stream($array);
    }

    /**
     * @param array $array
     * @return FluentIterables
     */
    public static function stream($array = array()) {
        Asserts::checkArgument(Objects::isArray($array));
        return FluentIterables::of($array);
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
            $collection[$k] = $el;
        }
    }

    public static function addAll(&$collection, $addElements = array()) {
        foreach ($addElements as $el) {
            $collection[] = $el;
        }
    }

    /**
     * @param array $array
     * @param $key
     * @return array|null
     */
    public static function getValue(array $array = array(), $key) {
        return self::getValueOrDefault($array, $key);
    }

    public static function getValueOrDefault(array $array = array(), $key, $default = null) {
        if (Collections::hasKey($array, $key)) {
            return $array[$key];
        }

        return $default;
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

    public static function contains($element, array $array): bool {
        return in_array($element, $array, true);
    }

    public static function setKeys(&$collection = array(), $keys = array(), $value) {
        foreach ($keys as $key) {
            $collection[$key] = $value;
        }
        return $collection;
    }

    public static function setValues(&$collection, $values) {
        $index = 0;
        foreach ($collection as $key => $value) {
            $collection[$key] = $values[$index];
            $index++;
        }
        return $collection;
    }

    public static function containsAny($array1 = array(), $array2 = array()) {
        return Collections::anyMatch($array1, function ($ann) use ($array2) {
            return Collections::contains($ann, $array2);
        });
    }

    public static function removeByKey(&$array, $key) {
        if (Collections::hasKey($array, $key)) {
            unset($array[$key]);
        }
    }

    public static function removeValue(&$array, $value) {
        $k = array_search($value, $array, true);
        Collections::removeByKey($observerList, $k);
        return $observerList;
    }

    public static function asArray($array = array()) {
        if (Objects::isArray($array)) {
            return $array;
        }
        return array($array);

    }

    public static function containsAll($valuesToSearch, $all) {
        $array_intersect = array_intersect($valuesToSearch, $all);
        return count($array_intersect) === count($valuesToSearch);

    }

    public static function merge($array1 = array(), $array2 = array()) {
        return array_merge($array1, $array2);
    }

    public static function isIn($value, $array = array()) {
        return array_search($value, $array);
    }

    public static function range($first, $last, $step = 1) {
        return range($first, $last, $step);
    }

    public static function subList($collection, $fromIndex, $toIndex) {
        return array_slice($collection, $fromIndex, $toIndex);
    }

    /**
     * @param array $array
     * @return Optional
     */
    public static function first($array = array()): Optional {
        return self::findFirst($array, Predicates::notNull());
    }

    public static function partition($array = array(), $size) {
        return array_chunk($array, $size, true);
    }

    public static function reverse($array = []): array {
        return array_reverse($array, true);
    }
}