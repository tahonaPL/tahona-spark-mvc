<?php
/**
 *
 *
 * Date: 30.01.17
 * Time: 08:57
 */

namespace Spark\Core\Annotation\Handler;

use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;
use Spark\Config;
use Spark\Core\Annotation\Inject;
use Spark\Core\Processor\Handler\Handler;
use Spark\Routing;
use Spark\Routing\RoutingInfo;
use Spark\Container;

abstract class AnnotationHandler extends Handler {


    public function handleClassAnnotations($annotations = array(), $class, ReflectionClass $classReflection) {
    }

    public function handleMethodAnnotations($annotations = array(), $class, ReflectionMethod $methodReflection) {
    }

    public function handleFieldAnnotations($annotations = array(), $class, ReflectionProperty $fieldReflection) {
    }

    protected function supports($class): bool {
        return true;
    }


}