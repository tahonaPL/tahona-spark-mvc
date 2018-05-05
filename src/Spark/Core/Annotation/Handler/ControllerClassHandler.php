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
        $defined = $this->controllerClassDefinition;

        if ($classReflection->isSubclassOf($defined) && !$classReflection->isAbstract()) {
            $className = $classReflection->getName();

            $obj = new $className();
            $this->getContainer()->register($className, $obj);
        }
    }

    /**
     * @param $class
     * @return bool
     */
    protected function supports($class): bool {
        return true;
    }
}