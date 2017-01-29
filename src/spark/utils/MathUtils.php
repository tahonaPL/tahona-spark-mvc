<?php
/**
 * Created by PhpStorm.
 * User: primosz67
 * Date: 15.07.14
 * Time: 03:21
 */

namespace spark\utils;


class MathUtils {

    public static function formatNumber($val, $decimal = 2) {
        return number_format((float)$val, $decimal, '.', '');
    }

    public static function div($a, $b) {
        if (empty($a) || empty($b) || $b == 0 || $a == 0) {
            return 0;
        }

        return $a / $b;
    }

    public static function mapSumFunction(\Closure $func) {
        return function ($objects = array()) use ($func) {
            $sum = 0;
            foreach ($objects as $obj) {
                $sum += $func($obj);
            }
            return $sum;
        };

    }
} 