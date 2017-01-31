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
use spark\utils\Collections;

class InitAnnotationProcessors extends AnnotationHandler {

    private $handlers;
    private $routing;
    private $config;
    private $services;

    public function __construct(&$routing, &$config, &$services) {
        $this->handlers = array(
            new ComponentAnnotationHandler(),
            new EnableApcuAnnotationHandler(),
            new RequestPathAnnotationHandler(),
            new SmartyViewConfigurationAnnotationHandler()
        );

        $this->routing = $routing;
        $this->config = $config;
        $this->services = $services;

        /** @var AnnotationHandler $handler */
        foreach ($this->handlers as $handler) {
            $this->updateHanlder($handler);
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

    public function addHandler($handler) {
        Collections::addAll($this->handlers, array($handler));
        $this->updateHanlder($handler);
    }

    /**
     * @param $handler
     */
    private function updateHanlder($handler) {
        $handler->setConfig($this->config);
        $handler->setRouting($this->routing);
        $handler->setServices($this->services);
    }


}