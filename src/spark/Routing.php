<?php

namespace spark;

use spark\common\IllegalStateException;
use spark\common\Optional;
use spark\core\routing\exception\RouteNotFoundException;
use spark\core\routing\RoutingDefinition;
use spark\core\routing\exception\RoutingException;
use spark\http\Request;
use spark\http\utils\RequestUtils;
use spark\routing\RoutingUtils;
use spark\utils\Asserts;
use spark\utils\Collections;
use spark\utils\Dev;
use spark\utils\Functions;
use spark\utils\Objects;
use spark\utils\Predicates;
use spark\utils\StringFunctions;
use spark\utils\StringUtils;
use spark\utils\UrlUtils;

class Routing {

    const CONTROLLER_NAME = "controller";
    const METHOD_NAME = "method";
    const REQUEST_METHODS_NAME = "requestMethods";
    const REQUEST_HEADERS_NAME = "requestHeaders";

    const ROLES = "roles";
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
     *
     * @param type $urlPath
     * @param type $nameSpace string or array
     * @param type $registeredHostPath
     * @return \spark\http\Request
     */
    public function createRequest($urlPath, $registeredHostPath = "") {
        $urlPath = $this->getPath();

        $request = new \spark\http\Request();
        $request->setHostPath($registeredHostPath);

        $routeDefinition = $this->getDefinition($urlPath);

        $request->setMethodName($routeDefinition->getActionMethod());
        $request->setControllerClassName($routeDefinition->getControllerClassName());
        $request->setSecurityRoles($this->getRoles($routeDefinition));
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

            $routeDefinition = RoutingUtils::findRouteDefinition($routesDefinitions);
            $routeDefinition->orElseThrow(new IllegalStateException("Route not found for: " . RequestUtils::getMethod() . " $urlPath " . RequestUtils::getMethod()));

            $routingDefinition = $this->checkRoute($routeDefinition);
            return $routingDefinition;

        } else {

            $ctx = $this;

            $routeDefinitions = Collections::builder($this->parametrizedRouting)
                ->flatMap(function($def){return $def;})
                ->filter(function ($definition) use ($urlPath, $ctx) {
                    /** @var RoutingDefinition $definition */
                    return RoutingUtils::hasExpressionParams($definition->getPath(), $urlPath, $definition->getParams());
                })
                ->get();

            $routeDefinition = RoutingUtils::findRouteDefinition($routeDefinitions);

            if ($routeDefinition->isPresent()) {
                return $this->checkRoute($routeDefinition);
            }

            throw new RouteNotFoundException(RequestUtils::getMethod(), $urlPath);
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

    private function getRoles(RoutingDefinition $routePath) {
        if (isset($routePath)) {
            return $routePath->getRoles();
        }
        return array();
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
                    $pathResultValues[$key] = $urlElement;
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
        return $this->definitions;
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
        foreach($routing as $def) {
            Asserts::checkArgument(false === Collections::hasKey($this->routing, $def->getPath()), "Can't use same path in routing: ".$def->getPath());
            Asserts::checkArgument(false === Collections::hasKey($this->parametrizedRouting, $def->getPath()), "Can't use same path in routing: ".$def->getPath());

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

    /**
     *
     * @param Optional $route
     * @return RoutingDefinition
     * @throws \Exception
     * @internal param $urlPath
     */
    private function checkRoute(Optional $route) {

        //backward compatibility: "com.some.AccController " change into com\some\AccController
        $route->map(Functions::invokeGetMethod(RoutingDefinition::D_CONTROLLER_CLASS_NAME))
            ->map(StringFunctions::replace(".", "\\"))
            ->orElseThrow(new RoutingException("Nod defined controller class in rout"));

        $route->mapProperty(RoutingDefinition::D_ACTION_METHOD)
            ->orElseThrow(new RoutingException("Not defined action method in routing."));

        return $route->get();
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
}
