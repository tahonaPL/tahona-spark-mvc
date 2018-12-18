<?php

namespace Spark;

use foo\bar;
use Spark\Common\Collection\FluentIterables;
use Spark\Common\IllegalStateException;
use Spark\Core\Routing\Exception\RouteNotFoundException;
use Spark\Core\Routing\RequestData;
use Spark\Core\Routing\RoutingDefinition;
use Spark\Http\Request;
use Spark\Http\Session\SessionProvider;
use Spark\Http\Utils\RequestUtils;
use Spark\Routing\RoutingUtils;
use Spark\Utils\Asserts;
use Spark\Utils\Collections;
use Spark\Utils\Functions;
use Spark\Utils\StringUtils;
use Spark\Utils\UrlUtils;

class Routing {

    public const CONTROLLER_NAME = 'controller';
    public const METHOD_NAME = 'method';
    public const REQUEST_METHODS_NAME = 'requestMethods';
    public const REQUEST_HEADERS_NAME = 'requestHeaders';

    public const ROLES = 'roles';
    public const PARAMS = 'params';

    private $routing;
    private $parametrizedRouting = array();

    private $definitions;
    private $sessionProvider;

    public function __construct(array $routing) {
        $this->definitions = $routing;
        $this->routing = $routing;
        $this->addAll($this->routing);
    }

    /**
     * @param string $registeredHostPath
     * @return RequestData
     */
    public function createRequest($registeredHostPath = ''): RequestData {
        $urlPath = $this->getPath();
        $routeDefinition = $this->getDefinition($urlPath);

        $request = new RequestData($this->sessionProvider);
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

            $routeDefinition = Collections::stream($this->parametrizedRouting)
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

            /** @var RoutingDefinition $definition */
            $definition = $routeDefinition->orElseThrow(new RouteNotFoundException(RequestUtils::getMethod(), $urlPath));

            $definitionsWithSamePath = $this->parametrizedRouting[$definition->getPath()];
            $size = Collections::size($definitionsWithSamePath);
            if ($size > 1) {
                $definition = RoutingUtils::findRouteDefinition($definitionsWithSamePath, false)->orElse($definition);
            }

            return $definition;
        }
    }

    public function setSessionProvider(SessionProvider $sessionProvider) {
        $this->sessionProvider = $sessionProvider;
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
        if ($this->isStaticRoute($urlPath)) {
            return array();
        }

        $definitionPath = $routeDefinition->getPath();
        $definitionElements = explode('/', $definitionPath);
        $definitionElementsCount = \count($definitionElements);

        $pathResultValues = array();
        $urlElements = explode('/', $urlPath, $definitionElementsCount);

        $routingDefinitionParams = $routeDefinition->getParams();

        foreach ($urlElements as $index => $urlValue) {
            $hasValue = Collections::hasKey($routingDefinitionParams, $index);

            if ($hasValue) {
                $key = $routingDefinitionParams[$index];
                $pathResultValues[$key] = urldecode($urlValue);
            }
        }

        return $pathResultValues;
    }

    private function fillModuleData(Request $request, $controllerName) {
        $controllerName = StringUtils::replace($controllerName, "\\controller", '');

        $splittedPath = StringUtils::split($controllerName, "\\");

        $module = $splittedPath;
        Collections::removeByIndex($module, 0);
        Collections::removeByIndex($module, count($module) - 1);
        $module = StringUtils::join("\\", $module);

        $request->setNamespace($splittedPath[0]);
        $request->setModuleName($module);
        $request->setControllerName(str_replace('Controller', '', end($splittedPath)));
    }

    /**
     * Hook to override
     * @return null
     */
    public function getBaseErrorPath() {
        return null;
    }

    public function getDefinitions() : FluentIterables{
        return Collections::stream()
            ->addAll($this->parametrizedRouting)
            ->addAll($this->routing);
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
        $urlPath = explode('?', $urlPath); //for params
        $urlPath = $urlPath[0];
        return $urlPath;
    }

    /**
     * FIXME: Very slow resolve all methods in pre compile
     */
    public function resolveRoute(string $path, array $params = array()): string {
        if (StringUtils::contains($path, '@')) {
            $route = StringUtils::split($path, '@');
            $controllerName = $route[0];
            $methodName = $route[1];

            if (Collections::size($route) > 2) {
                $paramsAsString = $route[2];
                $params = Collections::builder(StringUtils::split($paramsAsString, ','))
                    ->convertToMap(function ($x) {
                        $key = StringUtils::split($x, ':');
                        return $key[0];
                    })
                    ->map(function ($x) {
                        $key = StringUtils::split($x, ':');
                        return $key[1];
                    })->get();
            }

            if (StringUtils::isBlank($controllerName)) {
                $controllerName = $this->getCurrentDefinition()->getControllerClassName();
            }
            $paramsKeys = Collections::getKeys($params);

            return $this->getDefinitions()
                ->flatMap(Functions::getSameObject())
                ->findFirst(function ($d) use ($controllerName, $methodName, $paramsKeys) {
                    /* @var RoutingDefinition $d */
                    return StringUtils::contains($d->getControllerClassName(), $controllerName)
                        && StringUtils::contains($d->getActionMethod(), $methodName)
                        && Collections::size($paramsKeys) === Collections::size($d->getParams())
                        && Collections::containsAll($paramsKeys, $d->getParams());
                })
                ->map(function ($d) use ($params) {
                    return RoutingUtils::fillParametrizedPath($d->getPath(), $params);
                })
                ->orElse(StringUtils::EMPTY);
        }

        return $path;
    }

    /**
     * @param $urlPath
     * @return bool
     */
    private function isStaticRoute($urlPath): bool {
        return isset($this->routing[$urlPath]);
    }
}
