<?php
/**
 *
 *
 * Date: 19.01.15
 * Time: 21:07
 */

namespace Spark\Routing;

use Spark\Common\Optional;
use Spark\Core\Routing\RoutingDefinition;
use Spark\Http\HttpRequestMethod;
use Spark\Http\Utils\RequestUtils;
use Spark\Utils\Collections;
use Spark\Utils\Predicates;
use Spark\Utils\StringFunctions;
use Spark\Utils\StringPredicates;
use Spark\Utils\StringUtils;

class RoutingUtils {

    public static function hasExpression($route) {
        return StringUtils::contains($route, '}') && StringUtils::contains($route, '{');
    }

    public static function hasExpressionParams($route, $urlPath, $routeDefinitionParams = array()): bool {
        if (StringUtils::isBlank($route) || StringUtils::isBlank($urlPath)) {
            return false;
        }

        $routeParts = explode('/', $route);
        $urlPathParts = explode('/', $urlPath);

        $paramsCount = count($routeDefinitionParams);
        $hasPathParams = $paramsCount > 0;

        $definitionRouteElementsCount = count($routeParts);
        $routeStaticElementsCount = $definitionRouteElementsCount - $paramsCount;

        if (!$hasPathParams) {
            return false;
        }

        $hasEndDefinedParam = self::hasEndDefinedParam($routeDefinitionParams);

        $isPathElementsCountEqual = ($definitionRouteElementsCount === count($urlPathParts));

        if ($isPathElementsCountEqual || $hasEndDefinedParam) {
            for ($i = 0; $i < $definitionRouteElementsCount; $i++) {
                $routeExpressionKey = $routeParts[$i];
                $urlElement = $urlPathParts[$i];

                if (StringUtils::equalsIgnoreCase($urlElement, $routeExpressionKey)) {
                    $routeStaticElementsCount--;
                } else if (!StringUtils::equalsIgnoreCase($urlElement, $routeExpressionKey)) {
                    $routeExpressionKey = self::clearRouteParamExpression($routeExpressionKey);

                    if (Collections::exist($routeDefinitionParams, $routeExpressionKey)) {
                        $paramsCount--;
                    }
                } else if (!StringUtils::equals($routeExpressionKey, $urlElement)) {
                    return false;
                }
            }

            return $paramsCount === 0 && $routeStaticElementsCount === 0;
        }

        return false;
    }

    public static function clearRouteParamExpression($routeElement) {
        $routeElement = StringUtils::replace($routeElement, '{', '');
        $routeElement = StringUtils::replace($routeElement, '}', '');
        return $routeElement;
    }

    public static function validate(RoutingDefinition $arr) {
        $error = array();
        if (StringUtils::isNotBlank($arr->getPath())) {
            $pathErrors = array();

            if (!StringUtils::startsWith($arr->getPath(), '/')) {
                $pathErrors[] = 'routing.error.wrong.path';
            }

            if (StringUtils::contains($arr->getPath(), '{')) {
                if (Collections::isEmpty($arr->getParams())) {
                    $pathErrors[] = 'routing.error.path.missing.param';
                }
            }

            if (Collections::isNotEmpty($pathErrors)) {
                $error['path'] = $pathErrors;
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
    public static function findRouteDefinition($routes = array(), $ignoreEmptyRequestMethod = true) {
        $requestMethod = RequestUtils::getMethod();
        $headers = RequestUtils::getHeaders();

        /** @var RoutingDefinition $item */

        foreach ($routes as $item) {
            $requestMethods = $item->getRequestMethods();

            if (Collections::contains($requestMethod, $requestMethods)) {
                $hasHeaders = Collections::isEmpty($item->getRequestHeaders());
                if ($hasHeaders || Collections::containsAll($item->getRequestHeaders(), Collections::getKeys($headers))) {
                    return Optional::of($item);
                }
            }

            if ($ignoreEmptyRequestMethod && Collections::isEmpty($requestMethods)) {
                return Optional::of($item);
            }
        }

        return Optional::absent();
    }

    /**
     * Element has:
     *  index - place of Param in url path
     *  key -  key of param
     *
     * @param $parametrizedPath
     * @return array
     */
    public static function getParametrizedUrlKeys($parametrizedPath) {
        $val = Optional::of($parametrizedPath)->map(StringFunctions::replace("\\", '/'))
            ->map(StringFunctions::split('/'))
            ->orElse(array());

        return Collections::stream($val)->filter(StringPredicates::notBlank())
            ->filter(function ($x) {
                return RoutingUtils::hasExpression($x);
            })
            ->map(function ($x) {
                return RoutingUtils::clearRouteParamExpression($x);
            })
            ->get();
    }

    public static function fillParametrizedPath($path, $params = array()) {
        if (Collections::isEmpty($params)) {
            return $path;
        }

        $newPath = $path;
        foreach ($params as $key => $value) {
            $newPath = StringUtils::replace($newPath, '{' . $key . '}', $value);
        }

        return $newPath;
    }

    private static function hasEndDefinedParam(array $routeDefinitionParams): bool {
        $count = \count($routeDefinitionParams);
        if ($count > 0) {
            $routeDefParamSchema = end($routeDefinitionParams);
            return StringUtils::contains($routeDefParamSchema, '...');
        }
        return false;
    }
} 