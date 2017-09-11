<?php
/**
 * Created by PhpStorm.
 * User: primosz67
 * Date: 09.10.14
 * Time: 08:02
 */

namespace Spark\Utils;


use Spark\Common\IllegalArgumentException;
use Spark\Common\IllegalStateException;
use Spark\Utils\Objects;

class Asserts {

    /**
     *
     * @param $object
     * @param string $message
     * @throws IllegalArgumentException
     * @return object
     */
    public static function notNull($object, $message = "Object cannot be null or undefined.") {
        if (Objects::isNull($object)) {
            throw new IllegalArgumentException($message);
        }
        return $object;
    }

    /**
     * @deprecated
     * @param $object
     * @param string $message
     * @throws \Spark\Common\IllegalArgumentException
     */
    public static function isArray($object, $message = "Object is not an array") {
        if (is_null($object) || !is_array($object)) {
            throw new IllegalArgumentException($message);
        }
    }

    /**
     * @param $object
     * @param string $message
     * @throws \Spark\Common\IllegalArgumentException
     */
    public static function checkArray($object, $message = "Object is not an array") {
        if (is_null($object) || !is_array($object)) {
            throw new IllegalArgumentException($message);
        }
    }

    public static function checkArgument($bool, $message = "Invalid argument") {
        if (!$bool) {
            throw new IllegalArgumentException($message);
        }
    }

    public static function checkState($bool, $message = 'Invaid state (should be "true")') {
        if (!$bool) {
            throw new IllegalStateException($message);
        }
    }
}