<?php

namespace spark\core\annotation\handler;

use spark\core\annotation\handler\AnnotationHandler;
use spark\utils\Collections;
use spark\utils\Functions;
use spark\utils\Objects;
use spark\utils\Predicates;
use spark\utils\StringUtils;

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