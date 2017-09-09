<?php
/**
 * Created by PhpStorm.
 * User: primosz67
 * Date: 24.03.15
 * Time: 08:02
 */

namespace spark\common\collection;


use spark\common\Optional;
use spark\utils\Asserts;
use spark\utils\Collections;
use spark\utils\Objects;

class FluentIterables {

    /**
     * @var array
     */
    private $collection = array();

    function __construct($collection = array()) {
        Asserts::notNull($collection);
        $this->collection = $collection;
    }

    /**
     * @param array $collection
     * @return FluentIterables
     */
    public function addAll($collection) {
        if (Collections::isNotEmpty($collection)) {
            $firstKey = Collections::getKeys($collection)[0];
            if ($this->hasKeyValue($firstKey)) {
                Collections::addAllOrReplace($this->collection, $collection);
            } else {
                Collections::addAll($this->collection, $collection);
            }
        }
        return Collections::builder($this->collection);
    }

    /**
     * @param $firstKey
     * @return bool
     */
    private function hasKeyValue($firstKey) {
        return $firstKey !== 0;
    }

    /**
     * @param callable $keyFunction
     * @return FluentIterables
     */
    public function convertToMap(\Closure $keyFunction) {
        return Collections::builder(Collections::convertToMap($this->collection, $keyFunction));
    }

    /**
     * @param callable $mapFunction
     * @return FluentIterables
     */
    public function filter(\Closure $mapFunction) {
        return Collections::builder(Collections::filter($this->collection, $mapFunction));
    }

    public function get() {
        return $this->collection;
    }

    /**
     * @param callable $func
     * @return FluentIterables
     */
    public function map(\Closure $func) {
        return Collections::builder(Collections::map($this->collection, $func));
    }

    /**
     * @param callable $func
     * @return bool
     */
    public function anyMatch(\Closure $func) {
        return Collections::anyMatch($this->collection, $func);
    }

    /**
     * @param callable $func
     * @return bool
     */
    public function noneMatch(\Closure $func) {
        return Collections::noneMatch($this->collection, $func);
    }

    /**
     * if function result is collection
     * @param callable $func
     * @return FluentIterables
     */
    public function flatMap(\Closure $func, $mergeKeys = false) {
        return Collections::builder(Collections::flatMap($this->collection, $func, $mergeKeys));
    }

    /**
     * @param callable $func
     * @return FluentIterables
     */
    public function groupBy(\Closure $func) {
        return Collections::builder(Collections::groupBy($this->collection, $func));
    }

    /**
     * @param callable $func
     * @return FluentIterables
     */
    public function sort(\Closure $func) {
        Collections::sortFunc($this->collection, $func);
        return Collections::builder($this->collection);
    }

    /**
     * @param callable $func
     * @return Optional
     */
    public function findFirst(\Closure $func = null) {
        if (Objects::isNull($func)) {
            return Collections::findFirst($this->collection, function($obj){
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

    public function insert($index, $element) {
        return Collections::builder(Collections::insert($this->collection, $index, $element));
    }

    /**
     * @param $element
     * @return FluentIterables
     */
    public function add($element) {
        $array = $this->collection;
        $array[] = $element;

        return Collections::builder($array);;
    }

    /**
     * Return array with reorganize indexes. keys: 0, 1, 2 ...
     * @return array
     */
    public function getList() {
        $collection = array();
        foreach($this->collection as $v){
            $collection[]=$v;
        }
        return $collection;
    }

    public function entries() {
        return Collections::builder($this->toEntries($this->collection));
    }

    private function toEntries($collection) {
        $entries = [];
        foreach($collection as $k=> $v) {
            $entries[] = new Entry($k, $v);
        }
        return $entries;
    }


}