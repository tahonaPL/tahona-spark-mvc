<?php

namespace spark\core\annotation\handler;

use ReflectionClass;
use spark\Config;
use spark\core\annotation\handler\AnnotationHandler;
use spark\core\annotation\Path;
use spark\core\routing\RoutingDefinition;
use spark\routing\RoutingUtils;
use spark\utils\Collections;
use spark\utils\Functions;
use spark\utils\Objects;
use spark\utils\Predicates;
use spark\utils\ReflectionUtils;
use spark\utils\StringUtils;

/**
 * Created by PhpStorm.
 * User: primosz67
 * Date: 30.01.17
 * Time: 08:57
 */
class PathAnnotationHandler extends AnnotationHandler {

    private $annotationName;
    private $classes;

    private $annotations;

    const PATH_ANNOTATION = "spark\\core\\annotation\\Path";

    public function __construct() {
        $this->annotationName = self::PATH_ANNOTATION;
        $this->classes = array();
        $this->annotations = array();
    }

    public function handleClassAnnotations($annotations = array(), $class, ReflectionClass $classReflection) {

        $this->annotations[$class] = Collections::builder($annotations)
            ->filter(Predicates::compute($this->getClassName(), StringUtils::predEquals($this->annotationName)))
            ->get();
    }

    public function handleMethodAnnotations($methodAnnotations = array(), $class, \ReflectionMethod $methodReflection) {

        $methodAnnotations = Collections::builder($methodAnnotations)
            ->filter(Predicates::compute($this->getClassName(), StringUtils::predEquals($this->annotationName)))
            ->get();


        if (Collections::hasKey($this->annotations, $class) && Collections::isNotEmpty($this->annotations[$class])) {
            /** @var Path $classPathAnnotation */

            foreach ($this->annotations[$class] as $classPathAnnotation) {

                /** @var Path $methodAnnotation */
                foreach ($methodAnnotations as $methodAnnotation) {
                    $reflectionClass = $methodReflection->getDeclaringClass();
                    $path = $classPathAnnotation->path . $methodAnnotation->path;

                    $routingDefinition = new RoutingDefinition();
                    $routingDefinition->setPath($path);
                    $routingDefinition->setControllerClassName($reflectionClass->getName());
                    $routingDefinition->setActionMethod($methodReflection->getName());

                    $routingDefinition->setRequestHeaders(Collections::merge($classPathAnnotation->header, $methodAnnotation->header));
                    $routingDefinition->setRequestMethods(Collections::merge($classPathAnnotation->method, $methodAnnotation->method));

                    if (RoutingUtils::hasExpression($path)) {
                        $routingDefinition->setParams(RoutingUtils::getParametrizedUrlKeys($path));
                    }
//                    $routingDefinition->setRoles($)

                    $this->getRouting()->addDefinition($routingDefinition);
                }
            }

        } else {

            /** @var Path $ann */
            foreach ($methodAnnotations as $methodAnnotation) {
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

                $this->getRouting()->addDefinition($routingDefinition);

            }
        }

    }


    private function getClassName() {
        return Functions::getClassName();
    }

}