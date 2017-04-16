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

    public function handleMethodAnnotations($annotations = array(), $class, \ReflectionMethod $methodReflection) {

        $annotations = Collections::builder($annotations)
            ->filter(Predicates::compute($this->getClassName(), StringUtils::predEquals($this->annotationName)))
            ->get();


        if (Collections::hasKey($this->annotations, $class) && Collections::isNotEmpty($this->annotations[$class])) {
            /** @var Path $prefixAnnotation */

            foreach ($this->annotations[$class] as $prefixAnnotation) {

                /** @var Path $ann */
                foreach ($annotations as $ann) {
                    $reflectionClass = $methodReflection->getDeclaringClass();
                    $path = $prefixAnnotation->path . $ann->path;

                    $routingDefinition = new RoutingDefinition();
                    $routingDefinition->setPath($path);
                    $routingDefinition->setControllerClassName($reflectionClass->getName());
                    $routingDefinition->setActionMethod($methodReflection->getName());

                    $routingDefinition->setRequestHeaders(Collections::merge($prefixAnnotation->header, $ann->header));
                    $routingDefinition->setRequestMethods(Collections::merge($prefixAnnotation->method, $ann->method));

                    if (RoutingUtils::hasExpression($path)) {
                        $routingDefinition->setParams(RoutingUtils::getParametrizedUrlKeys($path));
                    }
//                    $routingDefinition->setRoles($)

                    $this->getRouting()->addDefinition($routingDefinition);
                }
            }

        } else {

            /** @var Path $ann */
            foreach ($annotations as $ann) {
                $reflectionClass = $methodReflection->getDeclaringClass();

                $routingDefinition = new RoutingDefinition();
                $routingDefinition->setPath($ann->path);
                $routingDefinition->setControllerClassName($reflectionClass->getName());
                $routingDefinition->setActionMethod($methodReflection->getName());


                $routingDefinition->setRequestHeaders($ann->header);
                $routingDefinition->setRequestMethods($ann->header);

                if (RoutingUtils::hasExpression($ann->path)) {
                    $routingDefinition->setParams(RoutingUtils::getParametrizedUrlKeys($ann->path));
                }

                $this->getRouting()->addDefinition($routingDefinition);

            }
        }

    }


    private function getClassName() {
        return Functions::getClassName();
    }

}