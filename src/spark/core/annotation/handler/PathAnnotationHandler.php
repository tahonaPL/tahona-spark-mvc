<?php

namespace spark\core\annotation\handler;

use ReflectionClass;
use spark\Config;
use spark\core\annotation\handler\AnnotationHandler;
use spark\core\annotation\Path;
use spark\core\routing\factory\RoutingDefinitionFactory;
use spark\core\routing\RoutingDefinition;
use spark\routing\RoutingUtils;
use spark\utils\Collections;
use spark\utils\Functions;
use spark\utils\Objects;
use spark\utils\Predicates;
use spark\utils\ReflectionUtils;
use spark\utils\StringFunctions;
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
            ->filter(Predicates::compute($this->getClassName(), Predicates::equals($this->annotationName)))
            ->get();
    }

    public function handleMethodAnnotations($methodAnnotations = array(), $class, \ReflectionMethod $methodReflection) {

        $methodAnnotations = Collections::builder($methodAnnotations)
            ->filter(Predicates::compute($this->getClassName(), StringFunctions::equals($this->annotationName)))
            ->get();

        $routingDefinitionFactory = new RoutingDefinitionFactory();

        if ($this->hasPathAnnotation($class)) {

            /** @var Path $classPathAnnotation */
            foreach ($this->annotations[$class] as $classPathAnnotation) {

                /** @var Path $methodAnnotation */
                foreach ($methodAnnotations as $methodAnnotation) {
                    $routingDefinition = $routingDefinitionFactory->createDefinition($methodReflection, $classPathAnnotation, $methodAnnotation);
                    $this->getRouting()->addDefinition($routingDefinition);
                }
            }
        } else {
            /** @var Path $ann */
            foreach ($methodAnnotations as $methodAnnotation) {
                $routingDefinition = $routingDefinitionFactory->createDefinitionForMethod($methodReflection, $methodAnnotation);
                $this->getRouting()->addDefinition($routingDefinition);
            }
        }

    }

    private function getClassName() {
        return Functions::getClassName();
    }

    /**
     *
     * @param $class
     * @return bool
     */
    private function hasPathAnnotation($class) {
        return Collections::hasKey($this->annotations, $class) && Collections::isNotEmpty($this->annotations[$class]);
    }

}