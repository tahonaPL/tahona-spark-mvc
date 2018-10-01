<?php


namespace Spark\Utils;


use function foo\func;

final class MathFunctions {


    private function __construct() {
    }

    public static function sum(\Closure $func): \Closure {
        return function ($objects = array()) use ($func) {
            $sum = 0;
            foreach ($objects as $obj) {
                $sum += $func($obj);
            }
            return $sum;
        };
    }

    public static function mul($y): \Closure {
        return function ($x) use ($y) {
            return $x * $y;
        };
    }

}