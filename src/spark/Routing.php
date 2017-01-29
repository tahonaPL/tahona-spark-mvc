<?php

namespace spark;

use spark\common\Optional;
use spark\core\routing\RoutingException;
use spark\routing\RoutingUtils;
use spark\utils\Collections;
use spark\utils\StringUtils;

class Routing {

    const CONTROLLER_NAME = "controller";
    const METHOD_NAME = "method";
    const ROLES = "roles";
    const PARAMS = "params";
    private $routing = array();
    private $parametrizedRouting = array();
    private $definitions;

    public function __construct($routing) {
        $this->definitions = $routing;

        //Check is key->definiton map or definitions array
        if ( isset($routing[0])) {
            $tmpRouting = array();

            Collections::builder($routing)
                ->map(function ($e) use (&$tmpRouting) {
                    $tmpRouting = array_replace($tmpRouting, $e);
                });

        } else {
            $tmpRouting = $routing;
            $this->routing = $tmpRouting;
        }

        foreach ($tmpRouting as $key => $value) {
//            Asserts::checkArgument(false === Collections::hasKey($this->routing, $key), "Can't use same weg");
//            Asserts::checkArgument(false === Collections::hasKey($this->parametrizedRouting, $key), "Can't");

            if (isset($value["params"])) {
                $this->parametrizedRouting[$key] = $value;
            } else {
                $this->routing[$key] = $value;
            }
        }
    }

    /**
     *
     * @param type $urlPath
     * @param type $nameSpace string or array
     * @param type $registeredHostPath
     * @return \spark\http\Request
     */
    public function createRequest($urlPath, $nameSpace = "", $registeredHostPath = "") {
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

        $names = $this->getModuleName($nameSpace, $controllerName);
        $request->setNamespace($names["namespace"]);
        $request->setModuleName($names["moduleName"]);
        $request->setControllerPrefix($names["controllerModule"]);
        $request->setControllerName($names["controllerSimpleName"]);

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
            $controllerName = StringUtils::replace($this->routing[$urlPath][Routing::CONTROLLER_NAME], ".", "\\");
            return array(
                $urlPath,
                $controllerName,
                $this->routing[$urlPath][Routing::METHOD_NAME]
            );
        } else {
            foreach ($this->parametrizedRouting as $route => $definition) {
                if ($this->checkAllPathElements($route, $urlPath)) {
                    $controllerName =
                        Optional::of($this->parametrizedRouting[$route][Routing::CONTROLLER_NAME])
                            ->map(StringUtils::mapReplace("/", ""))
                            ->map(StringUtils::mapReplace(".", "\\"))
                            ->get();

                    return array($route, $controllerName, $this->parametrizedRouting[$route][Routing::METHOD_NAME]);
                }
            }
        }

        throw new RoutingException("Cannot find routing for ".$urlPath);
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

    private function getModuleName($namespace, $controllerName) {

        $controllerName = str_replace("\\", "/", $controllerName);
        $splitedControllerName = StringUtils::split($controllerName, "/");

        if (is_array($namespace)) {
            foreach ($namespace as $space) {
                if (StringUtils::equals($splitedControllerName[0], $space)) {
                    $controllerName = str_replace($space, "", $controllerName);
                    $namespace = $space;
                    break;
                }
            }
        } else {
            $controllerName = str_replace($namespace, "", $controllerName);
        }

        $splited = explode("controller", $controllerName);


        $controllerSimpleName = $splited[1];
        $controllerParts = explode("/", $splited[1]);

        $controllerModule = "";

        if (count($controllerParts) > 1) {
            $controllerSimpleName = $controllerParts[count($controllerParts) - 1];
            $controllerModule = str_replace("/".$controllerSimpleName, "", $splited[1]);
        }

        return array(
            "moduleName" => StringUtils::join("/", explode("/", $splited[0]), true),
            "controllerModule" => $controllerModule,
            "namespace" => $namespace,
            "controllerSimpleName" => str_replace("Controller", "", str_replace("/", "", $controllerSimpleName))
        );
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


}
