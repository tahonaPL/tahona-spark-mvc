<?php
/**
 *
 * 
 * Date: 07.09.17
 * Time: 09:30
 */

namespace Spark\Core\Annotation\Handler;


use Spark\Utils\Collections;
use Spark\Utils\Objects;
use Spark\Utils\Predicates;
use Spark\Utils\StringUtils;

class AnnotationHandlerHelper {

    public static function getAnnotation($annotations, $defined) {
        return Collections::builder($annotations)
            ->filter(Predicates::compute(self::getClassName(), Predicates::in($defined)))
            ->findFirst();
    }

    private static function getClassName() {
        return function ($x) {
            return Objects::getClassName($x);
        };
    }

    public static function getBeanName($annotation, $className) {
        $isOk = Objects::isNotNull($annotation) && StringUtils::isNotBlank($annotation->name);
        $array = StringUtils::split($className, "\\");
        return $isOk ? $annotation->name : lcfirst(end($array));
    }
}