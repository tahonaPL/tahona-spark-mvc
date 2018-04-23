<?php
/**
 *
 *
 * Date: 15.07.14
 * Time: 03:21
 */

namespace Spark\Utils;

class MathUtils {

    public static function formatNumber($val, $decimal = 2) {
        return number_format((float)$val, $decimal, '.', '');
    }

    public static function div($a, $b) {
        Asserts::checkArgument(Objects::isNotNull($a) && Objects::isNotNull($b), 'Numbers cannot be empty');
        Asserts::checkArgument($b !== 0, 'Second variable cannot be null');
        return $a / $b;
    }

    public static function isNumeric($value) {
        return is_numeric($value);
    }

    public static function min($x1, $x2): int {
        return min([$x1, $x2]);
    }

    public static function sum(array $values) {
        return array_sum($values);
    }
} 