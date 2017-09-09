<?php
/**
 * Created by PhpStorm.
 * User: crownclown67
 * Date: 07.09.17
 * Time: 09:30
 */

namespace spark\core\annotation\handler;


use spark\utils\Collections;
use spark\utils\Objects;
use spark\utils\Predicates;
use spark\utils\StringUtils;

class AnnotationHandlerHelper {

    public static function getAnnotation($annotations, $defined) {
        return Collections::builder($annotations)
            ->filter(Predicates::compute(self::getClassName(), Predicates::contains($defined)))
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