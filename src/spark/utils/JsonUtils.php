<?php


namespace spark\utils;

class JsonUtils {

    public static function toJson($object) {
        return json_encode(self::prepareValue($object), JSON_UNESCAPED_SLASHES);
    }

    public static function decode($json) {
        return json_decode($json, true);
    }

    /**
     * @param $object
     * @return array|mixed
     */
    private static function prepareValue($object) {
        if (is_array($object)) {
            return self::parseArray($object);
        } else if (is_object($object)) {
            return self::convertObjectToArray($object);
        }
        return $object;
    }

    /**
     * @param $object
     * @return array
     */
    private static function convertObjectToArray($object) {
        $reflectionClass = new \ReflectionClass($object);
        $properties = $reflectionClass->getProperties();

        $resultArray = array();
        foreach ($properties as $property) {
            $property->setAccessible(true);
            $value = $property->getValue($object);
            $resultArray[$property->getName()] = self::prepareValue($value);
        }
        return $resultArray;
    }

    /**
     * @param $object
     * @return array
     */
    private static function parseArray($object) {
        $resultArray = array();
        foreach ($object as $k => $v) {
            $resultArray[$k] = self::prepareValue($v);
        }
        return $resultArray;
    }
}