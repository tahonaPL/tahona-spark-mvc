<?php
/**
 *
 *
 * Date: 30.01.17
 * Time: 08:57
 */

namespace Spark\Core\Annotation\Handler;

use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;
use Spark\Config;
use Spark\Core\Annotation\Inject;
use Spark\Routing;
use Spark\Routing\RoutingInfo;
use Spark\Container;

abstract class AnnotationHandler {

    /**
     * @Inject()
     * @var Container
     */
    private $container;
    /**
     * @Inject()
     * @var Routing
     */
    private $routing;
    /**
     * @Inject()
     * @var Config
     */
    private $config;

    public function handleClassAnnotations($annotations = array(), $class, ReflectionClass $classReflection){
    }

    public function handleMethodAnnotations($annotations = array(), $class, ReflectionMethod $methodReflection){
    }

    public function handleFieldAnnotations($annotations = array(), $class, ReflectionProperty $fieldReflection){
    }

    protected function supports($class): bool {
        return true;
    }

    protected function getContainer(): Container {
        return $this->container;
    }

    public function setContainer(Container $container){
        $this->container = $container;
    }

    protected function getRouting(): Routing {
        return $this->routing;
    }

    /**
     * @param RoutingInfo $routing
     */
    public function setRouting(&$routing){
        $this->routing = $routing;
    }

    protected function getConfig(): Config {
        return $this->config;
    }

    /**
     * @param Config $config
     */
    public function setConfig(&$config){
        $this->config = $config;
    }

    public function clear(){
        $this->routing = null;
        $this->container = null;
        $this->config = null;
    }
}