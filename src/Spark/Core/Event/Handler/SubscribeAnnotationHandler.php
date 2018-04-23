<?php
/**
 *
 * User: crownclown67
 * Date: 10.06.17
 * Time: 03:03
 */

namespace Spark\Core\Event\Handler;

use ReflectionMethod;
use ReflectionParameter;
use Spark\Common\Collection\FluentIterables;
use Spark\Common\Optional;
use Spark\Core\Annotation\Handler\AnnotationHandler;
use Spark\Core\Event\EventBus;
use Spark\Core\Event\Handler\Event\ObjectEventHandler;
use Spark\Utils\Asserts;
use Spark\Utils\Collections;
use Spark\Utils\Functions;
use Spark\Utils\Objects;
use Spark\Utils\Predicates;

class SubscribeAnnotationHandler extends AnnotationHandler {

    private $annotationName;
    /**
     * @var EventBus
     */
    private $eventBus;

    public function __construct() {
        $this->annotationName = "Spark\\Core\\Event\\Annotation\\Subscribe";
    }

    public function handleMethodAnnotations($annotations = array(), $class, ReflectionMethod $methodReflection) {
        $cacheAnnotation = $this->getAnnotations($annotations);

        if (Collections::isNotEmpty($cacheAnnotation)) {
            $cacheService = $this->getEventBus();

            foreach ($cacheAnnotation as $annotation) {
                $objects = $this->getByClass($class);

                foreach ($objects as $object) {
                    $parameters = $methodReflection->getParameters();

                    Asserts::checkState(Collections::isNotEmpty($parameters), 'Subscribe method need to have at least one parameter');

                    /** @var ReflectionParameter $param */
                    $param = $parameters[0];

                    $name = Objects::defaultIfNull($annotation->name, $this->getParamTypeClass($param));
                    $name = Objects::defaultIfNull($name, $param->getName());

                    Asserts::notNull($name, 'Parameter needs to have name or Name');
                    $cacheService->register($name, new ObjectEventHandler($object, $methodReflection->getName()));
                }
            }
        }
    }

    private
    function getClassName() {
        return Functions::getClassName();
    }

    /**
     *
     * @return EventBus
     * @throws \Exception
     */
    private
    function getEventBus() {
        /** @var EventBus eventBus */
        if (Objects::isNull($this->eventBus)) {
            $this->eventBus = $this->getContainer()->get(EventBus::NAME);
        }
        return $this->eventBus;
    }

    /**
     *
     * @param $annotations
     * @return array
     */
    private
    function getAnnotations($annotations) {
        $authorizeAnnotations = FluentIterables::of($annotations)
            ->filter(Predicates::compute($this->getClassName(),
                Predicates::equals($this->annotationName)))
            ->get();
        return $authorizeAnnotations;
    }

    private
    function getByClass($class) {
        return $this->getContainer()->getByType($class);
    }

    private function getParamTypeClass(ReflectionParameter $param) {
        return Optional::ofNullable($param->getType())
            ->mapProperty('name')
            ->getOrNull();
    }
}