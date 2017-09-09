<?php
/**
 * Created by PhpStorm.
 * User: primosz67
 * Date: 22.04.17
 * Time: 13:47
 */

namespace spark\core\routing\factory;


use spark\core\annotation\Component;
use spark\core\routing\RoutingDefinition;
use spark\routing\RoutingUtils;
use spark\utils\Collections;
use spark\utils\Functions;
use spark\utils\Objects;


class RoutingDefinitionFactory {

    public function createDefinition(\ReflectionMethod $methodReflection, $classPathAnnotation, $methodAnnotation) {
        $reflectionClass = $methodReflection->getDeclaringClass();
        $path = $classPathAnnotation->path . $methodAnnotation->path;
        $requestHeaders = Collections::merge($classPathAnnotation->header, $methodAnnotation->header);
        $requestMethods = Collections::merge($classPathAnnotation->method, $methodAnnotation->method);

        $routingDefinition = new RoutingDefinition();
        $routingDefinition->setPath($path);
        $routingDefinition->setControllerClassName($reflectionClass->getName());
        $routingDefinition->setActionMethod($methodReflection->getName());

        $routingDefinition->setActionMethodParameters($this->getMethodParmeters($methodReflection));

        $routingDefinition->setRequestHeaders($requestHeaders);
        $routingDefinition->setRequestMethods($requestMethods);

        if (RoutingUtils::hasExpression($path)) {
            $routingDefinition->setParams(RoutingUtils::getParametrizedUrlKeys($path));
        }

        return $routingDefinition;
    }

    public function createDefinitionForMethod(\ReflectionMethod $methodReflection, $methodAnnotation) {
        $reflectionClass = $methodReflection->getDeclaringClass();

        $routingDefinition = new RoutingDefinition();
        $routingDefinition->setPath($methodAnnotation->path);
        $routingDefinition->setControllerClassName($reflectionClass->getName());
        $routingDefinition->setActionMethod($methodReflection->getName());

        $routingDefinition->setRequestHeaders($methodAnnotation->header);
        $routingDefinition->setRequestMethods($methodAnnotation->method);

        if (RoutingUtils::hasExpression($methodAnnotation->path)) {
            $routingDefinition->setParams(RoutingUtils::getParametrizedUrlKeys($methodAnnotation->path));
        }
        return $routingDefinition;
    }

    /**
     * @param \ReflectionMethod $methodReflection
     * @return array
     */
    private function getMethodParmeters(\ReflectionMethod $methodReflection) {
        $methodParameters = [];
        if ($methodReflection->getNumberOfParameters() > 0) {
            $parameters = $methodReflection->getParameters();
            $methodParameters = Collections::builder()
                ->addAll($parameters)
                ->convertToMap(Functions::field("name"))
                ->map(function ($param) {
                    return $param->getClass();
                })
                ->map(function ($cls) {
                    return Objects::isNotNull($cls)?$cls->name:null;
                })
                ->get();
            return $methodParameters;
        }
        return $methodParameters;
    }
}