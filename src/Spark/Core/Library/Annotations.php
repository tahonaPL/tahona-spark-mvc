<?php
/**
 * Created by PhpStorm.
 * User: primosz67
 * Date: 03.02.17
 * Time: 21:58
 */

namespace Spark\Core\Library;


use Spark\Common\Optional;
use Spark\Core\Annotation\Cache;
use Spark\Utils\Collections;
use Spark\Utils\Functions;
use Spark\Utils\Objects;
use Spark\Utils\ReflectionUtils;

class Annotations {

    const SCOPE           = "spark\\core\\annotation\\Scope";
    const INJECT          = "spark\\core\\annotation\\Inject";
    const OVERRIDE_INJECT = "spark\\core\\annotation\\OverrideInject";
    const PROFILE         = "spark\\core\\annotation\\Profile";
    const BEAN            = "spark\\core\\annotation\\Bean";
    const CONTROLLER      = "spark\\core\\annotation\\Controller";
    const REST_CONTROLLER = "spark\\core\\annotation\\RestController";
    const CACHE           = "spark\\core\\annotation\\Cache";

    public static function getScopeByClass($className) {
        return Optional::ofNullable(ReflectionUtils::getClassAnnotation($className, self::SCOPE))
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
        return Collections::builder(ReflectionUtils::getClassAnnotation($className, self::OVERRIDE_INJECT))
            ->convertToMap(Functions::field("oldName"))
            ->get();
    }

    public static function hasCacheAnnotations($class) {
        $cacheDefinition = [];
        ReflectionUtils::handleMethodAnnotation($class, Annotations::CACHE, function ($class, $reflectionProperty, $annotation) use (&$cacheDefinition) {
            /** @var Cache $annotation */
            /** @var \ReflectionMethod $reflectionProperty */
            $cacheDefinition[$reflectionProperty->getName()] = $annotation;
        });
        return Collections::isNotEmpty($cacheDefinition);
    }
}