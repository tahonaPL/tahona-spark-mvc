<?php
/**
 *
 *
 * Date: 13.07.14
 * Time: 19:31
 */

namespace Spark\Utils;


use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Spark\Common\Optional;
use Spark\Core\Annotation\Bean;
use Spark\Utils\Collections;
use Spark\Utils\Objects;
use Spark\Utils\Reflection\MethodAnnotation;
use Spark\Utils\Reflection\PropertyAnnotation;

class ReflectionUtils {


    private static $ANNOTATION_READER;

    public static function setValue(&$bean, $property, &$value) {
        $className = get_class($bean);

        $reflectionProperty = new \ReflectionProperty($className, $property);
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($bean, $value);
    }


    public static function handlePropertyAnnotation(&$bean, $annotationName, \Closure $handler) {
        Asserts::notNull($bean);

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

        $observersWaitingToInject = array();

        /** @var $properties \ReflectionProperty */
        foreach ($properties as $reflectionProperty) {
            $annotation = $annotationReader->getPropertyAnnotation($reflectionProperty, $annotationName);

            if (false == is_null($annotation)) {
                $observer = $handler($bean, $reflectionProperty, $annotation);
                if (Objects::isNotNull($observer)) {
                    $observersWaitingToInject[$observer->getId()] = $observer;
                }
            }
        }

        return $observersWaitingToInject;
    }


    /**
     * @param $bean
     * @param $annotationName
     * @return PropertyAnnotation
     */
    public static function getPropertyAnnotations(&$bean, $annotationName) {
        Asserts::notNull($bean);

        $annotationReader = self::getReaderInstance();
        $classNames = Objects::getClassNames($bean);

        $properties = Collections::stream($classNames)
            ->flatMap(function ($className) {
                $classRef = new \ReflectionClass($className);
                return $classRef->getProperties();
            })
            ->get();

        /** @var $properties \ReflectionProperty */
        return Collections::stream($properties)
            ->map(function ($reflectionProperty) use ($annotationName, $annotationReader) {
                $annotation = $annotationReader->getPropertyAnnotation($reflectionProperty, $annotationName);
                return new PropertyAnnotation($reflectionProperty, $annotation);
            })
            ->filter(Predicates::compute(Functions::get(PropertyAnnotation::D_ANNOTATION), Predicates::notNull()))
            ->get();
    }

    /**
     * @return AnnotationReader
     */
    public static function getReaderInstance() {
        if (false == isset(self::$ANNOTATION_READER)) {
            //RegisterAutoLoader for annotations
            AnnotationRegistry::registerLoader("class_exists");
            self::$ANNOTATION_READER = new AnnotationReader();
        }
        return self::$ANNOTATION_READER;
    }

    public static function handleMethodAnnotation($bean, $annotationName, \Closure $handler) {
        Asserts::notNull($bean);

        $annotationReader = self::getReaderInstance();


        if (Objects::isString($bean)) {
            $cls = new \ReflectionClass($bean);
            $reflectionMethods = $cls->getMethods();
            $cls = $cls->getParentClass();

        } else {
            $reflectionObject = new \ReflectionObject($bean);
            $reflectionMethods = $reflectionObject->getMethods();
            $cls = $reflectionObject->getParentClass();
        }

        $fluentIterables = Collections::builder()
            ->addAll($reflectionMethods);

        while ($cls != null) {
            $fluentIterables->addAll($cls->getMethods());
            $cls = $cls->getParentClass();
        }
        $methods = $fluentIterables->get();

        /** @var $reflectionMethod \ReflectionMethod */
        foreach ($methods as $reflectionMethod) {
            $annotation = $annotationReader->getMethodAnnotation($reflectionMethod, $annotationName);

            if (!is_null($annotation)) {
                $handler($bean, $reflectionMethod, $annotation);
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

        if ($reflectionObject->hasProperty($field)) {
            $reflectionProperty = $reflectionObject->getProperty($field);
            return $annotationReader->getPropertyAnnotation($reflectionProperty, $annotationName);
        }
        return null;

    }

    /**
     *
     * @param $fullClassName
     * @param $annotationName
     * @return array Annotations
     */
    public static function getClassAnnotation($fullClassName, $annotationName) {
        return Collections::builder(self::getClassAnnotations($fullClassName))
            ->filter(Functions::hasClassName($annotationName))
            ->get();
    }

    /**
     * @param $fullClassName
     * @return array
     */
    public static function getClassAnnotations($fullClassName) {
        $annotationReader = self::getReaderInstance();
        $reflectionObject = new \ReflectionClass($fullClassName);
        return $annotationReader->getClassAnnotations($reflectionObject);
    }

    public static function hasConstructParameters(\ReflectionClass $reflectionClass): bool {
        return Optional::of($reflectionClass)
            ->map(Functions::get("constructor"))
            ->map(Functions::get("numberOfParameters"))
            ->filter(function ($x) {
                return $x > 0;
            })
            ->isPresent();
    }

}