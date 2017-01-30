<?php

namespace spark\core\annotation\handler;

use spark\core\annotation\handler\AnnotationHandler;
use spark\utils\Collections;
use spark\utils\Functions;
use spark\utils\Objects;
use spark\utils\Predicates;
use spark\utils\StringUtils;

/**
 * Created by PhpStorm.
 * User: primosz67
 * Date: 30.01.17
 * Time: 08:57
 */
class ComponentAnnotationHandler extends AnnotationHandler {

    private $annotationNames;

    public function __construct() {
        $this->annotationNames = array(
            "spark\\core\\annotation\\Component",
            "spark\\core\\annotation\\Configuration",
            "spark\\core\\annotation\\Service"
        );
    }

    public function handleClassAnnotations($annotations = array(), $bean, \ReflectionClass $classReflection) {
        $defined = $this->annotationNames;
        $annotation = Collections::builder($annotations)
            ->filter(Predicates::compute($this->getClassName(), Predicates::contains($defined)))
            ->findFirst();

        if ($annotation->isPresent()) {
            $className = $classReflection->getName();
            $beanName = $this->getBeanName($annotation->get(), $className);
            $this->getServices()->register($beanName, new $className);
        }
    }

    /**
     * @return \Closure
     */
    private function getClassName() {
        return function ($x) {
            return Objects::getClassName($x);
        };
    }

    private function getBeanName($annotation, $class) {
        $isOk = Objects::isNotNull($annotation) && StringUtils::isNotBlank($annotation->name);
        $array = StringUtils::split($class, "\\");
        return $isOk ? $annotation->name : lcfirst(end($array));

    }

}