<?php
/**
 * Created by PhpStorm.
 * User: crownclown67
 * Date: 04.06.17
 * Time: 11:15
 */

namespace spark\utils;


use spark\loader\ClassLoaderRegister;

class JsonUtils {

    public static function toJson($object) {
        return json_encode(self::toArray($object), JSON_UNESCAPED_SLASHES);
    }

    /**
     * @param $object
     * @return array
     */
    private static function toArray($object) {

        $array = [];
        if (is_array($object)) {
            foreach ($object as $k => $v) {
                $array[$k] = self::toArray($v);
            }
            return $array;
        } else if (is_object($object)) {
            $reflectionClass = new \ReflectionClass($object);
            $properties = $reflectionClass->getProperties();


            foreach ($properties as $property) {
                $property->setAccessible(true);
                $value = $property->getValue($object);
                $array[$property->getName()] = self::toArray($value);
            }
            return $array;

        } else {
            return $object;
        }
    }

    public static function decode($json) {
        return json_decode($json, true);
    }
}