<?php
/**
 * Created by PhpStorm.
 * User: primosz67
 * Date: 19.01.15
 * Time: 21:07
 */

namespace spark\routing;


use spark\common\Optional;
use spark\core\routing\RoutingDefinition;
use spark\http\HttpRequestMethod;
use spark\http\utils\RequestUtils;
use spark\utils\Collections;
use spark\utils\Predicates;
use spark\utils\StringFunctions;
use spark\utils\StringUtils;
use tahona\Routing;

class RoutingUtils {


    public static function hasExpression($route) {
        return StringUtils::contains($route, "}") && StringUtils::contains($route, "{");
    }

    public static function hasExpressionParams($route, $urlPath, $routeDefinitionParams = array()) {
        if (StringUtils::isBlank($route) || StringUtils::isBlank($urlPath)) {
            return false;
        }

        $exRoute = explode("/", $route);
        $exUrlPath = explode("/", $urlPath);

        $paramsCount = count($routeDefinitionParams);
        $hasPathParams = $paramsCount > 0;

        $routeStaticElementsCount = count($exRoute) - $paramsCount;

        if (false == $hasPathParams) {
            return false;
        }

        $isPathElementsCountEqual = (count($exRoute) === count($exUrlPath));

        if ($isPathElementsCountEqual) {
            for ($i = 0; $i < count($exRoute); $i++) {
                $routeExpressionKey = $exRoute[$i];
                $urlElement = $exUrlPath[$i];

                if (StringUtils::equalsIgnoreCase($urlElement, $routeExpressionKey)) {
                    $routeStaticElementsCount--;
                } else if (false == StringUtils::equalsIgnoreCase($urlElement, $routeExpressionKey)) {
                    $routeExpressionKey = self::clearRouteParamExpression($routeExpressionKey);

                    if (Collections::exist($routeDefinitionParams, $routeExpressionKey)) {
                        $paramsCount--;
                    }
                } else if (false == StringUtils::equals($routeExpressionKey, $urlElement)) {
                    return false;
                }
            }

            return $paramsCount === 0 && $routeStaticElementsCount === 0;
        }

        return false;
    }

    public static function clearRouteParamExpression($routeElement) {
        $routeElement = StringUtils::replace($routeElement, "{", "");
        $routeElement = StringUtils::replace($routeElement, "}", "");
        return $routeElement;
    }

    public static function validate($arr = array()) {
        $error = array();
        if (Collections::hasKey($arr, "path") && $arr["path"] !== "") {
            $pathErrors = array();
            if (!StringUtils::startsWith($arr["path"], "/")) {
                $pathErrors[] = "routing.error.wrong.path";
            }

            if (StringUtils::contains($arr["path"], "{")) {

                if (!Collections::hasKey($arr, Routing::PARAMS) || Collections::isEmpty($arr[Routing::PARAMS])) {
                    $pathErrors[] = "routing.error.path.missing.param";
                }
            }

            if (Collections::isNotEmpty($pathErrors)) {
                $error["path"] = $pathErrors;
            }
        }

        return $error;
    }

    public static function generateKey(RoutingDefinition $r) {
        return $r->getPath();
    }

    /**
     *
     * @param array $routes
     * @return Optional
     */
    public static function findRouteDefinition($routes = array()) {
        $requestMethod = RequestUtils::getMethod();
        $headers = RequestUtils::getHeaders();

        /** @var RoutingDefinition $item */
        $routeWithNoMethod = null;

        foreach ($routes as $item) {
            $requestMethods = $item->getRequestMethods();

            if (Collections::contains($requestMethod, $requestMethods)) {
                $hasHeaders = Collections::isEmpty($item->getRequestHeaders());
                if ($hasHeaders || Collections::containsAll($item->getRequestHeaders(), Collections::getKeys($headers))) {
                    return Optional::of($item);
                }
            }

            if (Collections::isEmpty($requestMethods)) {
                $routeWithNoMethod = $item;
            }
        }

        return Optional::ofNullable($routeWithNoMethod);
    }

    public static function getParametrizedUrlKeys($parametrizedPath) {
        return Optional::of($parametrizedPath)
            ->map(StringFunctions::replace("\\", "/"))
            ->map(StringFunctions::split("/"))
            ->toFluentIterable()
            ->filter(Predicates::notEmpty())
            ->filter(function ($x) {
                return RoutingUtils::hasExpression($x);
            })
            ->map(function ($x) {
                return RoutingUtils::clearRouteParamExpression($x);
            })
            ->get();

    }

} 