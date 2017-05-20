<?php

namespace spark\core\annotation\handler;

use spark\Config;
use spark\core\annotation\handler\AnnotationHandler;
use spark\utils\Collections;
use spark\utils\Functions;
use spark\utils\Objects;
use spark\utils\Predicates;
use spark\utils\StringFunctions;
use spark\utils\StringUtils;

/**
 * Created by PhpStorm.
 * User: primosz67
 * Date: 30.01.17
 * Time: 08:57
 */
class DebugAnnotationHandler extends AnnotationHandler {

    private $annotationNames;

    public function __construct() {
        $this->annotationNames =
            "spark\\core\\annotation\\Debug";
    }

    /**
     *
     * @param array $annotations
     * @param $class
     * @param \ReflectionClass $classReflection
     */
    public function handleClassAnnotations($annotations = array(), $class, \ReflectionClass $classReflection) {
        $defined = $this->annotationNames;
        $annotation = Collections::builder($annotations)
            ->filter(Predicates::compute($this->getClassName(), StringFunctions::equals($defined)))
            ->findFirst();

        if ($annotation->isPresent()) {
            $this->getConfig()->set(Config::DEV_ENABLED, true);
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

}