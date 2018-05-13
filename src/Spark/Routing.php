<?php

namespace Spark;

use Spark\Common\Collection\FluentIterables;
use Spark\Common\IllegalStateException;
use Spark\Common\Optional;
use Spark\Core\Routing\Exception\RouteNotFoundException;
use Spark\Core\Routing\RoutingDefinition;
use Spark\Core\Routing\Exception\RoutingException;
use Spark\Http\Request;
use Spark\Http\Utils\RequestUtils;
use Spark\Core\Routing\RequestData;
use Spark\Routing\RoutingUtils;
use Spark\Utils\Asserts;
use Spark\Utils\Collections;

use Spark\Utils\Functions;
use Spark\Utils\Objects;
use Spark\Utils\Predicates;
use Spark\Utils\StringFunctions;
use Spark\Utils\StringUtils;
use Spark\Utils\UrlUtils;

class Routing {

    const CONTROLLER_NAME      = "controller";
    const METHOD_NAME          = "method";
    const REQUEST_METHODS_NAME = "requestMethods";
    const REQUEST_HEADERS_NAME = "requestHeaders";

    const ROLES  = "roles";
    const PARAMS = "params";
    private $routing = array();
    private $parametrizedRouting = array();
    private $definitions;

    public function __construct($routing) {
        $this->definitions = $routing;

        //Check is key->definiton map or definitions array
        if (isset($routing[0])) {
            $tmpRouting = array();

            Collections::builder($routing)
                ->map(function ($e) use (&$tmpRouting) {
                    $tmpRouting = array_replace($tmpRouting, $e);
                });

        } else {
            $tmpRouting = $routing;
            $this->routing = $tmpRouting;
        }

        $this->addAll($tmpRouting);
    }

    /**
     * @param string $registeredHostPath
     * @return RequestData
     */
    public function createRequest($registeredHostPath = ""):RequestData {
        $urlPath = $this->getPath();

        $routeDefinition = $this->getDefinition($urlPath);

        $request = new RequestData();
        $request->setRouteDefinition($routeDefinition);
        $request->setHostPath($registeredHostPath);
        $request->setMethodName($routeDefinition->getActionMethod());
        $request->setControllerClassName($routeDefinition->getControllerClassName());

        $urlParams = $this->extractUrlParameters($urlPath, $routeDefinition);
        $request->setUrlParams($urlParams);

        $this->fillModuleData($request, $routeDefinition->getControllerClassName());

        return $request;
    }

    /**
     * Returns array as follow ($route, $controllerName, $methodName)
     *
     * @param $urlPath
     * @return RoutingDefinition
     * @throws IllegalStateException
     * @throws \Exception
     */
    private function getDefinition($urlPath) {

        if (isset($this->routing[$urlPath])) {
            $routesDefinitions = $this->routing[$urlPath];

            $routeDefinition = RoutingUtils::findRouteDefinition($routesDefinitions, false);
            if (!$routeDefinition->isPresent()) {
                $routeDefinition = RoutingUtils::findRouteDefinition($routesDefinitions);
            }

            return $routeDefinition->orElseThrow(new RouteNotFoundException(RequestUtils::getMethod(), $urlPath));

        } else {

            $ctx = $this;

            $routeDefinition = Collections::builder($this->parametrizedRouting)
                ->flatMap(function ($def) {
                    return $def;
                })
                ->findFirst(function ($definition) use ($urlPath, $ctx) {
                    if (RoutingUtils::hasExpressionParams($definition->getPath(), $urlPath, $definition->getParams())) {
                        $optional = RoutingUtils::findRouteDefinition(array($definition));
                        return $optional->orElse(null);
                    }
                    return null;
                });

            /** @var RoutingDefinition $dev */
            $dev = $routeDefinition->orElseThrow(new RouteNotFoundException(RequestUtils::getMethod(), $urlPath));

            $definitionsWithSamePath = $this->parametrizedRouting[$dev->getPath()];
            $size = Collections::size($definitionsWithSamePath);
            if ($size > 1) {
                $dev = RoutingUtils::findRouteDefinition($definitionsWithSamePath, false)->orElse($dev);
            }

            return $dev;
        }
    }

    /**
     * @param $route
     * @param $urlPath
     * @return bool
     */
    private function checkAllPathElements($route, $urlPath) {
        $keys = RoutingUtils::getParametrizedUrlKeys($route);
        return RoutingUtils::hasExpressionParams($route, $urlPath, $keys);
    }

    private function extractUrlParameters($urlPath, RoutingDefinition $routeDefinition) {
        if (isset($this->routing[$urlPath])) {
            return array();

        } else {

            $pathResultValues = array();
            $urlElements = explode("/", $urlPath);

            $routingDefinitionParams = $routeDefinition->getParams();

            foreach ($urlElements as $index => $urlElement) {
                $hasValue = Collections::hasKey($routingDefinitionParams, $index);
                if ($hasValue) {
                    $key = $routingDefinitionParams[$index];
                    $pathResultValues[$key] = urldecode($urlValue);
                }
            }

            return $pathResultValues;
        }
    }

    private function fillModuleData(Request $request, $controllerName) {
        $controllerName = StringUtils::replace($controllerName, "\\controller", "");

        $splittedPath = StringUtils::split($controllerName, "\\");

        $module = $splittedPath;
        Collections::removeByIndex($module, 0);
        Collections::removeByIndex($module, count($module) - 1);
        $module = StringUtils::join("\\", $module);

        $request->setNamespace($splittedPath[0]);
        $request->setModuleName($module);
        $request->setControllerName(str_replace("Controller", "", end($splittedPath)));
    }

    /**
     * Hook to override
     * @return null
     */
    public function getBaseErrorPath() {
        return null;
    }

    /**
     * @return array
     */
    public function getDefinitions() {
        return Collections::builder()
            ->addAll($this->parametrizedRouting)
            ->addAll($this->routing)
            ->get();
    }

    public function addDefinition(RoutingDefinition $routingDefinition) {
        if (RoutingUtils::hasExpression($routingDefinition->getPath())) {
            $this->addToDefinition($routingDefinition, $this->parametrizedRouting);
        } else {
            $this->addToDefinition($routingDefinition, $this->routing);
        }
    }

    /**
     * @param $routing
     */
    public function addAll($routing = array()) {
        /** @var RoutingDefinition $def */
        foreach ($routing as $def) {
            Asserts::checkArgument(false === Collections::hasKey($this->routing, $def->getPath()), "Can't use same path in routing: " . $def->getPath());
            Asserts::checkArgument(false === Collections::hasKey($this->parametrizedRouting, $def->getPath()), "Can't use same path in routing: " . $def->getPath());

            $this->addDefinition($def);
        }
    }

    /**
     * @param RoutingDefinition $routingDefinition
     * @param array $routing
     */
    private function addToDefinition(RoutingDefinition $routingDefinition, &$routing = array()) {
        $this->definitions[] = $routingDefinition->getPath();

        if (!Collections::hasKey($routing, $routingDefinition->getPath())) {
            $routing[$routingDefinition->getPath()] = array();
        }
        $routing[$routingDefinition->getPath()][] = $routingDefinition;
    }


    public function getCurrentDefinition() {
        return $this->getDefinition($this->getPath());
    }

    /**
     *
     * @param $urlPath
     * @return array
     */
    private function getPath() {
        $urlPath = UrlUtils::getPathInfo();
        $urlPath = explode("?", $urlPath); //for params
        $urlPath = $urlPath[0];
        return $urlPath;
    }

    /**
     * FIXME: Very slow resolve all methods in pre compile
     *
     * @param $path
     * @param array $params
     * @return string|null
     */
    public function resolveRoute($path, array $params = array()):string {
        if (StringUtils::contains($path, "@")) {

            $route = StringUtils::split($path, "@");
            $controllerName = $route[0];
            $methodName = $route[1];

            if (Collections::size($route) > 2) {
                $paramsAsString = $route[2];
                $params = Collections::builder(StringUtils::split($paramsAsString, ","))
                    ->convertToMap(function ($x) {
                        $key = StringUtils::split($x, ":");
                        return $key[0];
                    })
                    ->map(function ($x) {
                        $key = StringUtils::split($x, ":");
                        return $key[1];
                    })->get();
            }

            if (StringUtils::isBlank($controllerName)) {
                $controllerName = $this->getCurrentDefinition()->getControllerClassName();
            }
            $paramsKeys = Collections::getKeys($params);

            return Collections::builder()
                ->addAll($this->getDefinitions())
                ->flatMap(Functions::getSameObject())
                ->findFirst(function ($d) use ($controllerName, $methodName, $paramsKeys) {
                    /* @var RoutingDefinition $d */
                    return StringUtils::contains($d->getControllerClassName(), $controllerName)
                    && StringUtils::contains($d->getActionMethod(), $methodName)
                    && Collections::size($paramsKeys) === Collections::size($d->getParams())
                    && Collections::containsAll($paramsKeys, $d->getParams());
                })
                ->map(function ($d) use ($params) {
                    $fillParametrizedPath = RoutingUtils::fillParametrizedPath($d->getPath(), $params);
//                    var_dump($d, $fillParametrizedPath, $params);exit;
                    return $fillParametrizedPath;
                })
                ->orElse(StringUtils::EMPTY);
        }

        return StringUtils::EMPTY;
    }
}
