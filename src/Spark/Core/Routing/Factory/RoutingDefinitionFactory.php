<?php
/**
 * Created by PhpStorm.
 * User: primosz67
 * Date: 22.04.17
 * Time: 13:47
 */

namespace Spark\Core\Routing\Factory;


use Spark\Core\Annotation\Component;
use Spark\Core\Routing\RoutingDefinition;
use Spark\Routing\RoutingUtils;
use Spark\Utils\Collections;
use Spark\Utils\Functions;
use Spark\Utils\Objects;
use Spark\Utils\ReflectionUtils;


class RoutingDefinitionFactory {

    public function createDefinition(\ReflectionMethod $methodReflection, $classPathAnnotation, $methodAnnotation) {

        $reflectionClass = $methodReflection->getDeclaringClass();
        $controllerAnnotations = ReflectionUtils::getClassAnnotations($reflectionClass->getName());

        $path = $classPathAnnotation->path . $methodAnnotation->path;
        $requestHeaders = Collections::merge($classPathAnnotation->header, $methodAnnotation->header);
        $requestMethods = Collections::merge($classPathAnnotation->method, $methodAnnotation->method);

        $routingDefinition = new RoutingDefinition();
        $routingDefinition->setPath($path);

        $routingDefinition->setControllerClassName($reflectionClass->getName());
        $routingDefinition->setControllerAnnotations($controllerAnnotations);

        $routingDefinition->setActionMethod($methodReflection->getName());

        $routingDefinition->setActionMethodParameters($this->getMethodParameters($methodReflection));

        $routingDefinition->setRequestHeaders($requestHeaders);
        $routingDefinition->setRequestMethods($requestMethods);

        if (RoutingUtils::hasExpression($path)) {
            $routingDefinition->setParams(RoutingUtils::getParametrizedUrlKeys($path));
        }

        return $routingDefinition;
    }

    public function createDefinitionForMethod(\ReflectionMethod $methodReflection, $methodAnnotation) {
        $reflectionClass = $methodReflection->getDeclaringClass();
        $controllerAnnotations = ReflectionUtils::getClassAnnotations($reflectionClass->getName());

        $routingDefinition = new RoutingDefinition();
        $routingDefinition->setPath($methodAnnotation->path);
        $routingDefinition->setControllerClassName($reflectionClass->getName());
        $routingDefinition->setControllerAnnotations($controllerAnnotations);
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
    private function getMethodParameters(\ReflectionMethod $methodReflection) {
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