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
        if (empty($a) || empty($b)) {
            return 0;
        }

        return $a / $b;
    }

    public static function isNumeric($value) {
        return is_numeric($value);
    }

    public static function min($x1, $x2): int {
        return min([$x1, $x2]);
    }
} 