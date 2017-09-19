<?php
/**
 *
 *
 * Date: 03.02.17
 * Time: 21:58
 */

namespace Spark\Core\Library;


use Spark\Common\Exception\NotImplementedException;
use Spark\Common\Optional;
use Spark\Core\Annotation\Cache;
use Spark\Utils\Collections;
use Spark\Utils\Functions;
use Spark\Utils\Objects;
use Spark\Utils\ReflectionUtils;

class Annotations {

    const CONTROLLER                = "Spark\\Core\\Annotation\\Controller";
    const REST_CONTROLLER           = "Spark\\Core\\Annotation\\RestController";
    const SERVICE                   = "Spark\\Core\\Annotation\\Service";
    const REPOSITORY                = "Spark\\Core\\Annotation\\Repository";
    const CONFIGURATION             = "Spark\\Core\\Annotation\\Configuration";
    const COMPONENT                 = "Spark\\Core\\Annotation\\Component";
    const SCOPE                     = "Spark\\Core\\Annotation\\Scope";
    const INJECT                    = "Spark\\Core\\Annotation\\Inject";
    const OVERRIDE_INJECT           = "Spark\\Core\\Annotation\\OverrideInject";
    const PROFILE                   = "Spark\\Core\\Annotation\\Profile";
    const BEAN                      = "Spark\\Core\\Annotation\\Bean";
    const CACHE                     = "Spark\\Core\\Annotation\\Cache";
    const DEBUG                     = "Spark\\Core\\Annotation\\Debug";
    const ENABLE_APCU_BEAN_CACHE    = "Spark\\Core\\Annotation\\EnableApcuBeanCache";
    const PATH                      = "Spark\\Core\\Annotation\\Path";
    const SMARTY_VIEW_CONFIGURATION = "Spark\\Core\\Annotation\\SmartyViewConfiguration";
    const POST_CONSTRUCT            = "Spark\\Core\\Annotation\\PostConstruct";

    public static function getScopeByClass($className) {
        return Optional::ofNullable(ReflectionUtils::getClassAnnotation($className, self::SCOPE))
            ->map(Functions::field("value"))
            ->getOrNull();
    }

    public static function getScopeByMethod($className, $method) {
//        return Optional::ofNullable(ReflectionUtils::getMethodAnnotation($className, $method, self::SCOPE))
//            ->map(Functions::field("value"))
//            ->getOrNull();
        throw  new NotImplementedException();
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
        ReflectionUtils::handleMethodAnnotation($class, Annotations::CACHE, function ($class, $reflectionProperty, $Annotation) use (&$cacheDefinition) {
            /** @var Cache $Annotation */
            /** @var \ReflectionMethod $reflectionProperty */
            $cacheDefinition[$reflectionProperty->getName()] = $Annotation;
        });
        return Collections::isNotEmpty($cacheDefinition);
    }
}