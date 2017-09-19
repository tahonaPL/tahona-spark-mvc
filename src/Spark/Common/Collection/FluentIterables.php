<?php
/**
 *
 *
 * Date: 24.03.15
 * Time: 08:02
 */

namespace Spark\Common\Collection;


use Spark\Common\Optional;
use Spark\Utils\Asserts;
use Spark\Utils\Collections;
use Spark\Utils\Objects;

class FluentIterables {

    /**
     * @var array
     */
    private $collection = array();

    private function __construct(array $collection) {
        $this->collection = $collection;
    }

    public static function of(array $arr): FluentIterables {
        return new FluentIterables($arr);
    }

    /**
     * @param array $collection
     * @return FluentIterables
     */
    public function addAll(array $collection): FluentIterables {
        if (Collections::isNotEmpty($collection)) {
            $firstKey = Collections::getKeys($collection)[0];
            if ($this->hasKeyValue($firstKey)) {
                Collections::addAllOrReplace($this->collection, $collection);
            } else {
                Collections::addAll($this->collection, $collection);
            }
        }
        return self::of($this->collection);
    }

    /**
     * @param $firstKey
     * @return bool
     */
    private function hasKeyValue($firstKey): bool {
        return $firstKey !== 0;
    }

    /**
     * @param callable $keyFunction
     * @return FluentIterables
     */
    public function convertToMap(\Closure $keyFunction): FluentIterables {
        return self::of(Collections::convertToMap($this->collection, $keyFunction));
    }

    /**
     * @param callable $mapFunction
     * @return FluentIterables
     */
    public function filter(\Closure $mapFunction): FluentIterables {
        return self::of(Collections::filter($this->collection, $mapFunction));
    }

    public function get(): array {
        return $this->collection;
    }

    /**
     * @param callable $func
     * @return FluentIterables
     */
    public function map(\Closure $func): FluentIterables {
        return self::of(Collections::map($this->collection, $func));
    }

    /**
     * @param callable $func
     * @return bool
     */
    public function anyMatch(\Closure $func): bool {
        return Collections::anyMatch($this->collection, $func);
    }

    /**
     * @param callable $func
     * @return bool
     */
    public function noneMatch(\Closure $func): bool {
        return Collections::noneMatch($this->collection, $func);
    }

    /**
     * if function result is collection
     * @param callable $func
     * @return FluentIterables
     */
    public function flatMap(\Closure $func, $mergeKeys = false): FluentIterables {
        return self::of(Collections::flatMap($this->collection, $func, $mergeKeys));
    }

    /**
     * @param callable $func
     * @return FluentIterables
     */
    public function groupBy(\Closure $func): FluentIterables {
        return self::of(Collections::groupBy($this->collection, $func));
    }

    /**
     * @param callable $func
     * @return FluentIterables
     */
    public function sort(\Closure $func): FluentIterables {
        Collections::sortFunc($this->collection, $func);
        return self::of($this->collection);
    }

    public function findFirst(\Closure $func = null): Optional {
        if (Objects::isNull($func)) {
            return Collections::findFirst($this->collection, function ($obj) {
                return Objects::isNotNull($obj);
            });
        }

        return Collections::findFirst($this->collection, $func);
    }

    /**
     * @param callable $func
     * @return FluentIterables
     */
    public function each(\Closure $func) {
        foreach ($this->collection as $element) {
            $func($element);
        }
        return $this;
    }

    public function insert($index, $element): FluentIterables {
        return self::of(Collections::insert($this->collection, $index, $element));
    }

    /**
     * @param $element
     * @return FluentIterables
     */
    public function add($element): FluentIterables {
        $array = $this->collection;
        $array[] = $element;

        return self::of($array);;
    }

    /**
     * Return array with reorganize indexes. keys: 0, 1, 2 ...
     * @return array
     */
    public function getList(): array {
        $collection = array();
        foreach ($this->collection as $v) {
            $collection[] = $v;
        }
        return $collection;
    }

    public function entries(): FluentIterables {
        return self::of($this->toEntries($this->collection));
    }

    private function toEntries(array $collection): array {
        $entries = [];
        foreach ($collection as $k => $v) {
            $entries[] = new Entry($k, $v);
        }
        return $entries;
    }


}