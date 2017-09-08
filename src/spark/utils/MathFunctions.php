<?php


namespace spark\utils;


final class MathFunctions {


    private function __construct() {
    }

    public static function sum(\Closure $func) {
        return function ($objects = array()) use ($func) {
            $sum = 0;
            foreach ($objects as $obj) {
                $sum += $func($obj);
            }
            return $sum;
        };
    }

}