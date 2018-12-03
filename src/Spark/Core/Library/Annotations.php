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
use Spark\Cache\Annotation\Cache;
use Spark\Utils\Collections;
use Spark\Utils\Functions;
use Spark\Utils\Objects;
use Spark\Utils\ReflectionUtils;

class Annotations {

    public const CONTROLLER                = "Spark\\Core\\Annotation\\Controller";
    public const REST_CONTROLLER           = "Spark\\Core\\Annotation\\RestController";
    public const SERVICE                   = "Spark\\Core\\Annotation\\Service";
    public const REPOSITORY                = "Spark\\Core\\Annotation\\Repository";
    public const CONFIGURATION             = "Spark\\Core\\Annotation\\Configuration";
    public const COMPONENT                 = "Spark\\Core\\Annotation\\Component";
    public const SCOPE                     = "Spark\\Core\\Annotation\\Scope";
    public const INJECT                    = "Spark\\Core\\Annotation\\Inject";
    public const OVERRIDE_INJECT           = "Spark\\Core\\Annotation\\OverrideInject";
    public const PROFILE                   = "Spark\\Core\\Annotation\\Profile";
    public const BEAN                      = "Spark\\Core\\Annotation\\Bean";
    public const DEBUG                     = "Spark\\Core\\Annotation\\Debug";
    public const ENABLE_APCU_BEAN_CACHE    = "Spark\\Core\\Annotation\\EnableApcuBeanCache";
    public const PATH                      = "Spark\\Core\\Annotation\\Path";
    public const SMARTY_VIEW_CONFIGURATION = "Spark\\Core\\Annotation\\SmartyViewConfiguration";
    public const POST_CONSTRUCT            = "Spark\\Core\\Annotation\\PostConstruct";

    public static function getScopeByClass($className) {
        return Optional::ofNullable(ReflectionUtils::getClassAnnotation($className, self::SCOPE))
            ->map(Functions::field('value'))
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
            ->convertToMap(Functions::field('oldName'))
            ->get();
    }
}