<?php
/**
 *
 *
 * Date: 05.07.15
 * Time: 11:35
 */

namespace Spark\Utils;


class Conditionals {

    public static function executeIfTrue($bool, \Closure $closure) {
        if ($bool) {
            $closure();
        }
    }

    public static function throwIfInstance(\Exception $ex, $name) {
        //TODO
    }

} 