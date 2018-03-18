<?php

namespace Spark\Core\Annotation\Handler;

use ReflectionClass;
use Spark\Common\Collection\FluentIterables;
use Spark\Config;
use Spark\Controller;
use Spark\Core\Annotation\Handler\AnnotationHandler;
use Spark\Core\Annotation\Path;
use Spark\Core\Library\Annotations;
use Spark\Core\Routing\Factory\RoutingDefinitionFactory;
use Spark\Core\Routing\RoutingDefinition;
use Spark\Routing\RoutingUtils;
use Spark\Utils\Collections;
use Spark\Utils\Functions;
use Spark\Utils\Objects;
use Spark\Utils\Predicates;
use Spark\Utils\ReflectionUtils;
use Spark\Utils\StringFunctions;
use Spark\Utils\StringPredicates;
use Spark\Utils\StringUtils;

/**
 *
 *
 * Date: 30.01.17
 * Time: 08:57
 */
class PathAnnotationHandler extends AnnotationHandler {

    private $annotationName;
    private $classes;
    private $annotations;

    public function __construct() {
        $this->annotationName = Annotations::PATH;
        $this->classes = array();
        $this->annotations = array();
    }

    public function handleClassAnnotations($annotations = array(), $class, ReflectionClass $classReflection) {
        $this->annotations[$class] = Collections::builder($annotations)
            ->filter(Predicates::compute($this->getClassName(), Predicates::equals($this->annotationName)))
            ->get();
    }

    public function handleMethodAnnotations($methodAnnotations = array(), $class, \ReflectionMethod $methodReflection) {
        $methodAnnotations = FluentIterables::of($methodAnnotations)
            ->filter(Predicates::compute($this->getClassName(), StringPredicates::equals($this->annotationName)))
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

    protected function supports($class): bool {
        $classAnnotations = ReflectionUtils::getClassAnnotations($class);
        return FluentIterables::of(Objects::getClassNames($class))
                ->anyMatch(StringPredicates::equals(Controller::class))
            || FluentIterables::of($classAnnotations)
                ->map(Functions::getClassName())
                ->anyMatch(Predicates::in([
                    Annotations::CONTROLLER,
                    Annotations::REST_CONTROLLER
                ]));
    }

    private function getClassName() {
        return Functions::getClassName();
    }

    private function hasPathAnnotation($class): bool {
        return Collections::hasKey($this->annotations, $class) && Collections::isNotEmpty($this->annotations[$class]);
    }
}