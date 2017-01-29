<?php
/**
 * Created by PhpStorm.
 * User: primosz67
 * Date: 24.03.15
 * Time: 08:06
 */

namespace spark\common;


use spark\utils\Asserts;
use spark\utils\Functions;
use spark\utils\Objects;
use spark\utils\StringUtils;

class Optional {

    private $obj;

    function __construct($obj) {
        $this->obj = $obj;
    }

    /**
     * @return Optional
     */
    public static function of($obj) {
        Asserts::notNull($obj);
        return new Optional($obj);
    }

    /**
     * @return Optional
     */
    public static function ofNullable($obj) {
        return new Optional($obj);
    }

    public function get() {
        Asserts::notNull($this->obj, "Cannot invoke get on NULL Optional reference");
        return $this->obj;
    }

    public function getOrNull() {
        return $this->obj;
    }

    /**
     * @param $obj
     * @return mixed
     */
    public function orElse($obj) {
        return $this->getOrElse($obj);
    }

    /**
     * @deprecated
     * @param $obj
     * @return mixed
     */
    public function getOrElse($obj) {
        if ($this->isPresent()) {
            return $this->obj;
        } else {
            return $obj;
        }
    }

    /**
     * @return bool
     */
    public function isPresent() {
        return Objects::isNotNull($this->obj);
    }

    public function orElseGet(\Closure $func) {
        if ($this->isPresent()) {
            return $this->obj;
        } else {
            return $func();
        }
    }

    public function mapProperty($propertyName) {
        Asserts::checkState(StringUtils::isNotBlank($propertyName), "Property cannot be null");
        return $this->map(Functions::invokeGetMethod($propertyName));
    }

    /**
     * @param callable $func
     * @return $this|Optional
     */
    public function map(\Closure $func) {
        if ($this->isPresent()) {
            return new Optional($func($this->obj));
        }
        return self::absent();
    }

    /**
     * @return Optional
     */
    public static function absent() {
        return new Optional(null);
    }

    /**
     * @param callable $pred
     * @return $this|Optional
     */
    public function  filter(\Closure $pred) {
        if ($this->isPresent() && $pred($this->obj)) {
            return $this;
        }
        return self::absent();
    }

    public function orElseThrow(\Exception $ex) {
        if ($this->isPresent()) {
            return $this->obj;
        }
        throw $ex;
    }

    public function ifPresent(\Closure $voidFunc) {
        if ($this->isPresent()) {
            $voidFunc($this->obj);
        }
    }

}