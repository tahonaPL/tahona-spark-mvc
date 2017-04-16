<?php
/**
 * Created by PhpStorm.
 * User: primosz67
 * Date: 03.02.17
 * Time: 21:58
 */

namespace spark\core\library;


use spark\common\Optional;
use spark\utils\Collections;
use spark\utils\Functions;
use spark\utils\Objects;
use spark\utils\ReflectionUtils;

class Annotations {

    const SCOPE = "spark\\core\\annotation\\Scope";
    const INJECT = "spark\\core\\annotation\\Inject";
    const OVERRIDE_INJECT = "spark\\core\\annotation\\OverrideInject";

    public static function getScopeByClass($className) {
        return Optional::ofNullable(ReflectionUtils::getClassAnnotations($className, self::SCOPE))
            ->map(Functions::field("value"))
            ->getOrNull();
    }

    public static function getScopeByMethod($className, $method) {
        return Optional::ofNullable(ReflectionUtils::getMethodAnnotation($className, $method, self::SCOPE))
            ->map(Functions::field("value"))
            ->getOrNull();
    }

    /**
     * @param $className
     * @return array
     */
    public static function getOverrideInjections($className) {
        return Collections::builder(ReflectionUtils::getClassAnnotations($className, self::OVERRIDE_INJECT))
            ->convertToMap(Functions::field("oldName"))
            ->get();
    }
}