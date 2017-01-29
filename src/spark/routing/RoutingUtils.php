<?php
/**
 * Created by PhpStorm.
 * User: primosz67
 * Date: 19.01.15
 * Time: 21:07
 */

namespace spark\routing;


use spark\utils\Collections;
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

        $roteSize = count($exRoute) - $paramsCount;

        if (false == $hasPathParams) {
            return false;
        }

        $isPathElementsCountEqual = (count($exRoute) === count($exUrlPath));

        if ($isPathElementsCountEqual) {
            for ($i = 0; $i < count($exRoute); $i++) {
                $routeExpressionKey = $exRoute[$i];
                $urlElement = $exUrlPath[$i];

                if (Collections::exist($routeDefinitionParams, $urlElement)) {
                    $paramsCount--;
                } else if (StringUtils::equalsIgnoreCase($urlElement, $routeExpressionKey)) {
                    $roteSize--;
                } else if (false == StringUtils::equalsIgnoreCase($urlElement, $routeExpressionKey)) {
                    $routeExpressionKey = self::clearRouteParamExpression($routeExpressionKey);

                    if (Collections::exist($routeDefinitionParams, $routeExpressionKey)) {
                        $paramsCount--;
                    }
                } else if (false == StringUtils::equals($routeExpressionKey, $urlElement)) {
                    return false;
                }
            }

            return $paramsCount === 0 && $roteSize === 0;
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
} 