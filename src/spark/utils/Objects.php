<?php
declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: primosz67
 * Date: 21.12.14
 * Time: 23:46
 */


namespace spark\utils;


use ArrayAccess;
use spark\utils\Asserts;
use tahona\shop\client\domain\Client;
use Traversable;

class Objects {

    public static function isNotNull($obj) :bool {
        return isset($obj) && !is_null($obj);
    }

    public static function isNull($obj) :bool {
        return is_null($obj) || !isset($obj);
    }

    public static function isArray($obj) :bool {
        return is_array($obj) || ($obj instanceof Traversable);
    }

    public static function isString($obj) :bool {
        return is_string($obj);
    }

    public static function invokeMethod($obj, $methodName, $args = array()) {
        return call_user_func_array(array($obj, $methodName), $args);
    }

    public static function invokeGetMethod($obj, $propertyName) {
        $getMethod = $propertyName;
        if (!StringUtils::startsWith($propertyName, "get")) {
            $getMethod = "get" . ucfirst($propertyName);
        }
        return $obj->$getMethod();
    }

    public static function hasMethod($obj, $methodName) {
        return method_exists($obj, $methodName);
    }

    public static function hasProperty($obj, $propertyName) {
        return property_exists($obj, $propertyName);
    }

    public static function getSimpleClassName($obj) {
        Asserts::notNull($obj);
        return StringUtils::join('', array_slice(StringUtils::split(get_class($obj), '\\'), -1));
    }

    public static function getClassName($obj) {
        return get_class($obj);
    }

    public static function getClassNames($obj) {
        $parents = class_parents($obj);
        $implements = class_implements($obj);
        $className = self::getClassName($obj);

        return Collections::builder()
            ->add($className)
            ->addAll($parents)
            ->addAll($implements)
            ->getList();
    }

    public static function isPrimitive($obj) :bool {
        return is_scalar($obj);
    }

    public static function equals($a, $b) :bool {
        return $a === $b;
    }


} 