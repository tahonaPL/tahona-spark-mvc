<?php

namespace spark\core\annotation\handler;

use spark\Config;
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
class RequestPathAnnotationHandler extends AnnotationHandler {

    private $annotationName;

    public function __construct() {
        $this->annotationName = "spark\\core\\annotation\\RequestPath";
    }

    public function handleMethodAnnotations($annotations = array(), $bean, \ReflectionMethod $methodReflection) {
        $annotation = Collections::builder($annotations)
            ->findFirst(Predicates::compute($this->getClassName(), StringUtils::predEquals($this->annotationName)));

        if ($annotation->isPresent()) {
            $requestPath = $annotation->get();
            $reflectionClass = $methodReflection->getDeclaringClass();
            $this->getRouting()->addPath($requestPath->path, $reflectionClass->getName(), $methodReflection->getName());
        }
    }



    private function getClassName() {
        return Functions::getClassName();
    }

}