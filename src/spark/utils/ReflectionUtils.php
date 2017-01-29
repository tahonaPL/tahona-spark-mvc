<?php
/**
 * Created by PhpStorm.
 * User: primosz67
 * Date: 13.07.14
 * Time: 19:31
 */

namespace spark\utils;


use Doctrine\Common\Annotations\AnnotationReader;
use spark\utils\Collections;
use spark\utils\Objects;

class ReflectionUtils {

//    private static $injectionCache = array();

    public static function setValue(&$bean, $property, &$value) {
        $className = get_class($bean);
//
//        $key = $className."_".$property;

//        if (isset(self::$injectionCache[$key])) {
//            $reflectionProperty = self::$injectionCache[$key];
//        } else {
        $reflectionProperty = new \ReflectionProperty($className, $property);
        $reflectionProperty->setAccessible(true);

//            self::$injectionCache[$key] = $reflectionProperty;
//        }
        $reflectionProperty->setValue($bean, $value);
    }

    private static $reader;

    public static function handlePropertyAnnotation($bean, $annotationName, \Closure $handler) {
        $annotationReader = self::getReaderInstance();

        $reflectionObject = new \ReflectionObject($bean);
        $reflectionProperties = $reflectionObject->getProperties();

        $fluentIterables = Collections::builder()
            ->addAll($reflectionProperties);

        $cls = $reflectionObject->getParentClass();

        while ($cls != null) {
            $fluentIterables->addAll($cls->getProperties());
            $cls = $cls->getParentClass();
        }

        $properties = $fluentIterables->get();

        /** @var $properties \ReflectionProperty */
        foreach ($properties as $reflectionProperty) {
            $annotation = $annotationReader->getPropertyAnnotation($reflectionProperty, $annotationName);

            if (false == is_null($annotation)) {
                $handler($bean, $reflectionProperty, $annotation);
            }
        }
//        return property_exists($bean, $serviceName)
    }

    private static function getReaderInstance() {
        if (false == isset(self::$reader)) {
            self::$reader = new AnnotationReader();
        }
        return self::$reader;
    }

    public static function handleMethodAnnotation($bean, $annotationName, \Closure $handler) {
        $annotationReader = self::getReaderInstance();

        $reflectionObject = new \ReflectionObject($bean);
        $reflectionMethods = $reflectionObject->getMethods();

        $fluentIterables = Collections::builder()
            ->addAll($reflectionMethods);

        $cls = $reflectionObject->getParentClass();

        while ($cls != null) {
            $fluentIterables->addAll($cls->getMethods());
            $cls = $cls->getParentClass();
        }
        $methods = $fluentIterables->get();

        /** @var $methods \ReflectionMethod */
        foreach ($methods as $reflectionProperty) {
            $annotation = $annotationReader->getMethodAnnotation($reflectionProperty, $annotationName);

            if (false == is_null($annotation)) {
                $handler($bean, $reflectionProperty, $annotation);
            }
        }
    }

    /**
     * @param $bean
     * @param $field
     * @param $annotationName
     * @return \Doctrine\Common\Annotations\The|null|object
     */
    public static function getPropertyAnnotation($fullClassName, $field, $annotationName) {
        $annotationReader = self::getReaderInstance();
        $reflectionObject = new \ReflectionClass($fullClassName);
        $reflectionProperty = $reflectionObject->getProperty($field);

        return $annotationReader->getPropertyAnnotation($reflectionProperty, $annotationName);
    }

}