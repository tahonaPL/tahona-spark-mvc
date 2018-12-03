<?php

namespace Spark\Core\Annotation\Handler;

use ReflectionClass;
use Spark\Controller;
use Spark\Core\Annotation\Handler\AnnotationHandler;
use Spark\Core\Processor\Handler\ClassHandler;
use Spark\Utils\Collections;
use Spark\Utils\Functions;
use Spark\Utils\Objects;
use Spark\Utils\Predicates;
use Spark\Utils\StringUtils;

class ControllerClassHandler extends ClassHandler {

    private $controllerClassDefinition;

    public function __construct() {
        $this->controllerClassDefinition = Controller::class;
    }

    public function handleClass(ReflectionClass $classReflection) {
        $className = $classReflection->getName();
        $obj = new $className();
        $this->getContainer()->addBean($className, $obj);
    }

    /**
     * @param ReflectionClass $classReflection
     * @return bool
     * @internal param $class
     */
    protected function supports(ReflectionClass $classReflection): bool {
        return $classReflection->isSubclassOf($this->controllerClassDefinition) && !$classReflection->isAbstract();
    }
}