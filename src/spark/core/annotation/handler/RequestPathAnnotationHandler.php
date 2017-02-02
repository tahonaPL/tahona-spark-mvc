<?php

namespace spark\core\annotation\handler;

use spark\Config;
use spark\core\annotation\handler\AnnotationHandler;
use spark\core\annotation\RequestPath;
use spark\core\routing\RoutingDefinition;
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
            /** @var RequestPath $ann */
            $ann = $annotation->get();
            $reflectionClass = $methodReflection->getDeclaringClass();

            $routingDefinition = new RoutingDefinition();
            $routingDefinition->setPath($ann->path);
            $routingDefinition->setControllerClassName($reflectionClass->getName());
            $routingDefinition->setActionMethod($methodReflection->getName());
            $routingDefinition->setRequestHeaders($ann->header);
            $routingDefinition->setRequestMethods($ann->method);

            $this->getRouting()->addDefinition($routingDefinition);
        }
    }



    private function getClassName() {
        return Functions::getClassName();
    }

}