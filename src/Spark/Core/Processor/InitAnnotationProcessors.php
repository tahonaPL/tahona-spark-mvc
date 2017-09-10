<?php
/**
 * Created by PhpStorm.
 * User: primosz67
 * Date: 30.01.17
 * Time: 09:45
 */

namespace Spark\Core\Processor;


use Doctrine\Common\Annotations\AnnotationReader;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;
use Spark\Common\Optional;
use Spark\Container;
use Spark\Core\Annotation\Handler\AnnotationHandler;
use Spark\Core\Annotation\Handler\CacheAnnotationHandler;
use Spark\Core\Annotation\Handler\ComponentAnnotationHandler;
use Spark\Core\Annotation\Handler\ControllerAnnotationHandler;
use Spark\Core\Annotation\Handler\ControllerClassHandler;
use Spark\Core\Annotation\Handler\DebugAnnotationHandler;
use Spark\Core\Annotation\Handler\EnableApcuAnnotationHandler;
use Spark\Core\Annotation\Handler\EnableMailerAnnotationHandler;
use Spark\Core\Annotation\Handler\PathAnnotationHandler;
use Spark\Core\Annotation\Handler\SmartyViewConfigurationAnnotationHandler;
use Spark\Core\Annotation\RestController;
use Spark\Core\Annotation\SmartyViewConfiguration;
use Spark\Core\Library\Annotations;
use Spark\Utils\Collections;
use Spark\Utils\Functions;
use Spark\Utils\Objects;
use Spark\Utils\Predicates;
use Spark\Utils\ReflectionUtils;
use Spark\Utils\StringUtils;

class InitAnnotationProcessors extends AnnotationHandler {

    private $handlers;
    private $postHandlers;
    private $routing;
    private $config;
    /**
     * @var Container
     */
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
            new SmartyViewConfigurationAnnotationHandler(),
            new DebugAnnotationHandler(),
            new ControllerClassHandler(),
            new ControllerAnnotationHandler(),
        );

        $this->postHandlers = array(
            new CacheAnnotationHandler()
        );

        $this->routing = $routing;
        $this->config = $config;
        $this->container = $container;

        /** @var AnnotationHandler $handler */
        foreach ($this->handlers as $handler) {
            $this->updateHanlder($handler);
        }

        foreach ($this->postHandlers as $handler) {
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
        $this->processAnnotationsForHandlers($class, $this->handlers);
    }

    public function processPostAnnotations($class) {
        if (Collections::isNotEmpty($this->postHandlers)) {
            $this->processAnnotationsForHandlers($class, $this->postHandlers);
        }
    }

    /**
     *
     * @param $class
     * @param $handlers
     */
    private function processAnnotationsForHandlers($class, $handlers) {

        $reflectionObject = new ReflectionClass($class);
        $classAnnotations = $this->annotationReader->getClassAnnotations($reflectionObject);

        if ($this->hasValidProfile($classAnnotations)) {
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

    public function clear() {
        /** @var AnnotationHandler $handler */
        foreach ($this->handlers as $handler) {
            $handler->clear();
        }
        foreach ($this->postHandlers as $handler) {
            $handler->clear();;
        }
    }

    private function hasValidProfile($classAnnotations) {
        if (Collections::isNotEmpty($classAnnotations)) {
            $profile = $this->getAnnotation($classAnnotations, array(Annotations::PROFILE));
            return $this->isProperProfile($profile);
        }
        return true;
    }

    /**
     * @param $annotations
     * @param $defined
     * @return \Spark\Common\Optional
     */
    private function getAnnotation($annotations, $defined = array()) {
        return Collections::builder($annotations)
            ->filter(Predicates::compute($this->getClassName(), Predicates::contains($defined)))
            ->findFirst();
    }

    /**
     * @param $profile Optional
     * @return bool
     */
    private function isProperProfile($profile) {
        $profileName = $this->config->getProperty("app.profile");

        $annotationProfileName = $profile->map(Functions::field("name"))
            ->orElse(null);

        return StringUtils::isBlank($annotationProfileName)
        || StringUtils::equals($profileName, $annotationProfileName);
    }

    private function getClassName() {
        return function ($x) {
            return Objects::getClassName($x);
        };
    }


}