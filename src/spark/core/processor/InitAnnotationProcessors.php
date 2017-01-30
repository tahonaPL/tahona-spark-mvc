<?php
/**
 * Created by PhpStorm.
 * User: primosz67
 * Date: 30.01.17
 * Time: 09:45
 */

namespace spark\core\processor;


use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;
use spark\core\annotation\handler\AnnotationHandler;
use spark\core\annotation\handler\ComponentAnnotationHandler;
use spark\core\annotation\handler\EnableApcuAnnotationHandler;
use spark\core\annotation\handler\RequestPathAnnotationHandler;
use spark\core\annotation\handler\SmartyViewConfigurationAnnotationHandler;
use spark\core\annotation\SmartyViewConfiguration;

class InitAnnotationProcessors extends AnnotationHandler{

    private  $handlers;

    public function __construct(&$routing, &$config, &$services) {
        $this->handlers = array(
            new ComponentAnnotationHandler(),
            new EnableApcuAnnotationHandler(),
            new RequestPathAnnotationHandler(),
            new SmartyViewConfigurationAnnotationHandler()
        );

        /** @var AnnotationHandler $handler */
        foreach ($this->handlers as $handler) {
            $handler->setConfig($config);
            $handler->setRouting($routing);
            $handler->setServices($services);
        }
    }

    public function handleClassAnnotations($annotations = array(), $bean, ReflectionClass $classReflection) {
        /** @var AnnotationHandler $handler */
        foreach ($this->handlers as $handler) {
            $handler->handleClassAnnotations($annotations, $bean, $classReflection);
        }
    }

    public function handleMethodAnnotations($annotations = array(), $bean, ReflectionMethod $methodReflection) {
        /** @var AnnotationHandler $handler */
        foreach ($this->handlers as $handler) {
            $handler->handleMethodAnnotations($annotations, $bean, $methodReflection);
        }
    }

    public function handleFieldAnnotations($annotations = array(), $bean, ReflectionProperty $fieldReflection) {
        /** @var AnnotationHandler $handler */
        foreach ($this->handlers as $handler) {
            $handler->handleFieldAnnotations($annotations, $bean, $fieldReflection);
        }
    }


}