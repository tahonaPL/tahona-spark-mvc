<?php

namespace Spark\Common;

use Spark\Utils\Asserts;
use Spark\Utils\Collections;
use Spark\Utils\Functions;
use Spark\Utils\Objects;
use Spark\Utils\StringUtils;

class Optional {

    private $obj;

    private function __construct($obj) {
        $this->obj = $obj;
    }

    public static function of($obj): Optional {
        Asserts::notNull($obj);
        return new Optional($obj);
    }

    public static function ofNullable($obj): Optional {
        return new Optional($obj);
    }

    /**
     * @throws IllegalArgumentException
     * @return mixed
     */
    public function get() {
        Asserts::notNull($this->obj, 'Cannot invoke get on NULL Optional reference');
        return $this->obj;
    }

    public function getOrNull() {
        return $this->obj;
    }

    public function orElse($obj) {
        if ($this->isPresent()) {
            return $this->obj;
        }

        return $obj;
    }

    /**
     * @deprecated
     * @param $obj
     * @return mixed
     */
    public function getOrElse($obj) {
        return $this->orElse($obj);
    }

    public function isPresent(): bool {
        return Objects::isNotNull($this->obj);
    }

    public function orElseGet(\Closure $func) {
        if ($this->isPresent()) {
            return $this->obj;
        }
        return $func();
    }

    public function mapProperty($propertyName): Optional {
        Asserts::checkState(StringUtils::isNotBlank($propertyName), 'Property cannot be null');
        return $this->map(Functions::invokeGetMethod($propertyName));
    }

    public function map(\Closure $func): Optional {
        if ($this->isPresent()) {
            return new Optional($func($this->obj));
        }
        return self::absent();
    }

    public function flatMap(\Closure $func): Optional {
        if ($this->isPresent()) {
            return $func($this->obj);
        }
        return self::absent();
    }

    public static function absent(): Optional {
        return new Optional(null);
    }

    public function filter(\Closure $pred): Optional {
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

    public function ifPresent(\Closure $voidFunc): void {
        if ($this->isPresent()) {
            $voidFunc($this->obj);
        }
    }
}