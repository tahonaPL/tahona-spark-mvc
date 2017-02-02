<?php

namespace spark;

use spark\common\IllegalStateException;
use spark\common\Optional;
use spark\core\routing\RoutingDefinition;
use spark\core\routing\RoutingException;
use spark\http\Request;
use spark\routing\RoutingUtils;
use spark\utils\Asserts;
use spark\utils\Collections;
use spark\utils\Dev;
use spark\utils\Functions;
use spark\utils\Objects;
use spark\utils\Predicates;
use spark\utils\StringFunctions;
use spark\utils\StringUtils;

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
        $urlPath = explode("?", $urlPath); //for params
        $urlPath = $urlPath[0];

        $urlParts = $this->buildControllerDefinitionArray($urlPath);

        $route = $urlParts[0]; // defined as Route in routing
        $controllerName = $urlParts[1];
        $methodName = $urlParts[2];

        $request = new \spark\http\Request();
        $request->setHostPath($registeredHostPath);
        $request->setMethodName($methodName);
        $request->setControllerClassName($controllerName);
        $request->setSecurityRoles($this->getRoles($route));
        $request->setUrlParams($this->extractUrlParameters($urlPath, $route));


        $this->fillModuleData($request, $controllerName);

        return $request;
    }

    /**
     * Returns array as follow ($route, $controllerName, $methodName)
     *
     * @param $urlPath
     * @return array
     */
    private function buildControllerDefinitionArray($urlPath) {

        if (isset($this->routing[$urlPath])) {
            $routesDefinitions = $this->routing[$urlPath];

            $route =  RoutingUtils::findRoute($routesDefinitions);
            $route->orElseThrow(new IllegalStateException("Route not found for: $urlPath"));

            //backward compatibility: "com.some.AccController " change into com\some\AccController
            $controllerName = $route->map(Functions::invokeGetMethod(RoutingDefinition::D_CONTROLLER_CLASS_NAME))
                ->map(StringFunctions::replace(".", "\\"))
                ->orElseThrow(new IllegalStateException("Nod defined controller class in rout"));

            $actionMethod = $route->mapProperty(RoutingDefinition::D_ACTION_METHOD)
                ->orElseThrow(new IllegalStateException("Not defined action method in routing."));

            return array( $urlPath, $controllerName, $actionMethod);
        } else {

            foreach ($this->parametrizedRouting as $routesDefinitions => $definition) {
                if ($this->checkAllPathElements($routesDefinitions, $urlPath)) {
                    $controllerName =
                        Optional::of($this->parametrizedRouting[$routesDefinitions][Routing::CONTROLLER_NAME])
                            ->map(StringUtils::mapReplace("/", ""))
                            ->map(StringUtils::mapReplace(".", "\\"))
                            ->get();

                    return array($routesDefinitions, $controllerName, $this->parametrizedRouting[$routesDefinitions][Routing::METHOD_NAME]);
                }
            }
        }

        throw new RoutingException("Cannot find routing for " . $urlPath);
    }

    private function checkAllPathElements($route, $urlPath) {
        $hasExpression = RoutingUtils::hasExpression($route);

        if ($hasExpression) {
            $routeDefinitionParams = $this->parametrizedRouting[$route][Routing::PARAMS];
            $hasExpressionParams = RoutingUtils::hasExpressionParams($route, $urlPath, $routeDefinitionParams);
            return $hasExpressionParams;
        }

        return StringUtils::startsWith($urlPath, $route);
    }

    private function getRoles($routePath) {
        if (isset($this->routing[$routePath])) {
            $route = $this->routing[$routePath];
            return $this->getRoleArray($route);

        } else if (isset($this->parametrizedRouting[$routePath])) {
            $route = $this->parametrizedRouting[$routePath];
            return $this->getRoleArray($route);

        } else {
            return array();
        }
    }

    /**
     * @param $route
     * @return array
     */
    private function getRoleArray($route) {
        return isset($route[Routing::ROLES]) ? $route[Routing::ROLES] : array();
    }

    private function extractUrlParameters($urlPath, $route) {
        if (isset($this->routing[$urlPath])) {
            return array();
        } else if (isset($this->parametrizedRouting[$route])) {
            $routingDefinition = $this->parametrizedRouting[$route];
            $urlParameters = array();

            $hasExpression = RoutingUtils::hasExpression($route);

            //TODO cache exploded version of route
            $urlElements = explode("/", $urlPath);
            $routElements = explode("/", $route);

            foreach ($urlElements as $index => $urlElement) {
                $routingDefinitionParams = $routingDefinition[Routing::PARAMS];

                //next element
                if (in_array($urlElement, $routingDefinitionParams)) {
                    $urlParameters[$urlElement] = $urlElements[$index + 1];

                    //based on {expression}
                } else if ($hasExpression) {
                    $clearedRouteParamExpression = RoutingUtils::clearRouteParamExpression($routElements[$index]);
                    if (in_array($clearedRouteParamExpression, $routingDefinitionParams)) {
                        $urlParameters[$clearedRouteParamExpression] = $urlElements[$index];
                    }
                }

            }

            return $urlParameters;
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
        $this->definitions[] = $routingDefinition->getPath();
        if (!Collections::hasKey($this->routing, $routingDefinition->getPath())) {
            $this->routing[$routingDefinition->getPath()] = array();
        }
        $this->routing[$routingDefinition->getPath()][] = $routingDefinition;
    }

    /**
     * @param $routing
     */
    public function addAll($routing) {
        foreach ($routing as $key => $value) {
            Asserts::checkArgument(false === Collections::hasKey($this->routing, $key), "Can't use same weg");
            Asserts::checkArgument(false === Collections::hasKey($this->parametrizedRouting, $key), "Can't");

            if (isset($value["params"])) {
                $this->parametrizedRouting[$key] = $value;
            } else {
                $definition = new RoutingDefinition();
                $definition->setPath($key);
                $definition->setControllerClassName($value[Routing::CONTROLLER_NAME]);
                $definition->setActionMethod($value[Routing::METHOD_NAME]);
                $definition->setRequestHeaders(Collections::getValue($value, Routing::REQUEST_HEADERS_NAME));
                $definition->setRequestMethods(Collections::getValue($value, Routing::REQUEST_METHODS_NAME));

                $this->addDefinition($definition);
            }
        }
    }


}
