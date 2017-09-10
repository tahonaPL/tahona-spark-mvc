<?php

namespace Spark\Core\annotation\handler;

use Spark\Core\annotation\handler\AnnotationHandler;
use Spark\Utils\Collections;
use Spark\Utils\Functions;
use Spark\Utils\Objects;
use Spark\Utils\Predicates;
use Spark\Utils\StringUtils;

class ControllerClassHandler extends AnnotationHandler {

    private $controllerClassDefinition;

    public function __construct() {
        $this->controllerClassDefinition = "spark\\Controller";
    }

    public function handleClassAnnotations($annotations = array(), $class, \ReflectionClass $classReflection) {
        $defined = $this->controllerClassDefinition;

        if ($classReflection->isSubclassOf($defined) && !$classReflection->isAbstract()) {
            $obj = new $class();
            $className = $classReflection->getName();
            $this->getContainer()->register($className, $obj);
        }
    }
}