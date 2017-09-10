<?php

namespace Spark\Core\annotation\handler;

use ReflectionClass;
use Spark\Config;
use Spark\Core\annotation\handler\AnnotationHandler;
use Spark\Core\annotation\Path;
use Spark\Core\routing\factory\RoutingDefinitionFactory;
use Spark\Core\routing\RoutingDefinition;
use Spark\Routing\RoutingUtils;
use Spark\Utils\Collections;
use Spark\Utils\Functions;
use Spark\Utils\Objects;
use Spark\Utils\Predicates;
use Spark\Utils\ReflectionUtils;
use Spark\Utils\StringFunctions;
use Spark\Utils\StringUtils;

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