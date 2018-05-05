<?php
/**
 *
 *
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
use Spark\Core\Annotation\Handler\PathAnnotationHandler;
use Spark\Core\Annotation\Handler\SmartyViewConfigurationAnnotationHandler;
use Spark\Core\Annotation\RestController;
use Spark\Core\Annotation\SmartyViewConfiguration;
use Spark\Core\Library\Annotations;
use Spark\Core\Processor\Handler\ClassHandler;
use Spark\Core\Processor\Handler\Handler;
use Spark\Utils\Collections;
use Spark\Utils\Functions;
use Spark\Utils\Objects;
use Spark\Utils\Predicates;
use Spark\Utils\ReflectionUtils;
use Spark\Utils\StringUtils;

class InitAnnotationProcessors extends AnnotationHandler {

    private $handlers;
    private $postHandlers;
    private $classHandlers;
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
            new ControllerAnnotationHandler(),
        );

        $this->classHandlers = [
            new ControllerClassHandler()
        ];

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

        foreach ($this->classHandlers as $handler) {
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
    private function updateHanlder(Handler $handler): void {
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
     * @param ReflectionClass $reflectionClass
     * @param $handlers
     * @internal param $class
     */
    private function processAnnotationsForHandlers(ReflectionClass $reflectionClass, $handlers) {
        $class = $reflectionClass->name;
        $classAnnotations = $this->annotationReader->getClassAnnotations($reflectionClass);

        if ($this->hasValidProfile($classAnnotations)) {

            $methodCache = [];
            $propertyCache = [];

            $reflectionMethods = $reflectionClass->getMethods();
            $reflectionProperties = $reflectionClass->getProperties();

            foreach ($reflectionMethods as $method) {
                $methodAnnotations = $this->annotationReader->getMethodAnnotations($method);
                if (Collections::isNotEmpty($methodAnnotations)) {
                    $methodCache[$method->name] = [
                        'annotations' => $methodAnnotations,
                        'method' => $method
                    ];
                }
            }

            foreach ($reflectionProperties as $property) {
                $propertiesAnnotations = $this->annotationReader->getPropertyAnnotations($property);
                if (Collections::isNotEmpty($propertiesAnnotations)) {
                    $propertyCache[$property->name] = [
                        'annotations' => $propertiesAnnotations,
                        'property' => $property
                    ];
                }
            }

            /** @var AnnotationHandler $handler */
            foreach ($handlers as $handler) {
                $supports = $handler->supports($class);

                if ($supports) {
                    if (Collections::isNotEmpty($classAnnotations)) {
                        $handler->handleClassAnnotations($classAnnotations, $class, $reflectionClass);
                    }

                    //Methods
                    foreach ($methodCache as $value) {
                        /** @var AnnotationHandler $handler */
                        $handler->handleMethodAnnotations($value['annotations'], $class, $value['method']);
                    }


                    //Field
                    foreach ($propertyCache as $value) {
                        /** @var AnnotationHandler $handler */
                        $handler->handleFieldAnnotations($value['annotations'], $class, $value['property']);
                    }
                }
            }
        }
    }

    public function clear(): void {
        /** @var AnnotationHandler $handler */
        foreach ($this->handlers as $handler) {
            $handler->clear();
        }
        foreach ($this->postHandlers as $handler) {
            $handler->clear();
        }
        foreach ($this->classHandlers as $handler) {
            $handler->clear();
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
            ->filter(Predicates::compute($this->getClassName(), Predicates::in($defined)))
            ->findFirst();
    }

    /**
     * @param $profile Optional
     * @return bool
     */
    private function isProperProfile($profile) {
        $profileName = $this->config->getProperty('app.profile');

        $annotationProfileName = $profile->map(Functions::field('name'))
            ->orElse(null);

        return StringUtils::isBlank($annotationProfileName)
            || StringUtils::equals($profileName, $annotationProfileName);
    }

    private function getClassName() {
        return function ($x) {
            return Objects::getClassName($x);
        };
    }

    public function processClass(ReflectionClass $rFClass) {

        /** @var ClassHandler $classHandler */
        foreach ($this->classHandlers as $classHandler) {
            if ($classHandler->supports($rFClass->name)) {
                $classHandler->handleClass($rFClass);
            }
        }
    }
}