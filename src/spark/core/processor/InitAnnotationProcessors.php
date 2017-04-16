<?php
/**
 * Created by PhpStorm.
 * User: primosz67
 * Date: 30.01.17
 * Time: 09:45
 */

namespace spark\core\processor;


use Doctrine\Common\Annotations\AnnotationReader;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;
use spark\core\annotation\handler\AnnotationHandler;
use spark\core\annotation\handler\ComponentAnnotationHandler;
use spark\core\annotation\handler\EnableApcuAnnotationHandler;
use spark\core\annotation\handler\EnableMailerAnnotationHandler;
use spark\core\annotation\handler\PathAnnotationHandler;
use spark\core\annotation\handler\SmartyViewConfigurationAnnotationHandler;
use spark\core\annotation\SmartyViewConfiguration;
use spark\utils\Collections;
use spark\utils\ReflectionUtils;

class InitAnnotationProcessors extends AnnotationHandler {

    private $handlers;
    private $postHandlers;
    private $routing;
    private $config;
    private $container;
    /**
     * @var AnnotationReader
     */
    private $annotationReader;

    public function __construct(&$routing, &$config, &$container) {
        $this->handlers = array(
            new ComponentAnnotationHandler(),
            new EnableApcuAnnotationHandler(),
            new PathAnnotationHandler(),
            new SmartyViewConfigurationAnnotationHandler()
        );

        $this->postHandlers = array();

        $this->routing = $routing;
        $this->config = $config;
        $this->container = $container;

        /** @var AnnotationHandler $handler */
        foreach ($this->handlers as $handler) {
            $this->updateHanlder($handler);
        }

        $this->annotationReader = ReflectionUtils::getReaderInstance();
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
        $handler->setContainer($this->container);
    }

    public function addPostHandler($handler) {
        Collections::addAll($this->postHandlers, array($handler));
        $this->updateHanlder($handler);
    }

    public function processAnnotations($class) {
        $handlers = $this->handlers;
        $this->processAnnotationsForHandlers($class, $handlers);
    }

    public function processPostAnnotations($class) {
        $handlers = $this->postHandlers;
        if (Collections::isNotEmpty($handlers)) {
            $this->processAnnotationsForHandlers($class, $handlers);
        }
    }

    /**
     *
     * @param $class
     * @param $handlers
     */
    private function processAnnotationsForHandlers($class, $handlers) {
        $reflectionObject = new ReflectionClass($class);

        //Class
        $classAnnotations = $this->annotationReader->getClassAnnotations($reflectionObject);
        /** @var AnnotationHandler $handler */
        foreach ($handlers as $handler) {
            $handler->handleClassAnnotations($classAnnotations, $class, $reflectionObject);
        }

        //Methods
        $reflectionMethods = $reflectionObject->getMethods();
        foreach ($reflectionMethods as $method) {
            $methodAnnotations = $this->annotationReader->getMethodAnnotations($method);
            /** @var AnnotationHandler $handler */
            foreach ($handlers as $handler) {
                $handler->handleMethodAnnotations($methodAnnotations, $class, $method);
            }
        }

        //Field
        $reflectionProperties = $reflectionObject->getProperties();
        foreach ($reflectionProperties as $property) {
            $methodAnnotations = $this->annotationReader->getPropertyAnnotations($property);

            /** @var AnnotationHandler $handler */
            foreach ($handlers as $handler) {
                $handler->handleFieldAnnotations($methodAnnotations, $class, $property);
            }
        }
    }


}