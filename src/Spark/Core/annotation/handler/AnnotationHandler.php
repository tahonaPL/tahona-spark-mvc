<?php
/**
 * Created by PhpStorm.
 * User: primosz67
 * Date: 30.01.17
 * Time: 08:57
 */

namespace Spark\Core\annotation\handler;


use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;
use Spark\Config;
use Spark\Core\annotation\Inject;
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

    public function handleClassAnnotations($annotations = array(), $class, ReflectionClass $classReflection) {

    }

    public function handleMethodAnnotations($annotations = array(), $class, ReflectionMethod $methodReflection) {

    }

    public function handleFieldAnnotations($annotations = array(), $class, ReflectionProperty $fieldReflection) {

    }

    /**
     * @return Container
     */
    protected function getContainer() {
        return $this->container;
    }

    /**
     * @param Container $container
     */
    public function setContainer(&$container) {
        $this->container = $container;
    }

    /**
     * @return Routing
     */
    protected function getRouting() {
        return $this->routing;
    }

    /**
     * @param RoutingInfo $routing
     */
    public function setRouting(&$routing) {
        $this->routing = $routing;
    }

    /**
     * @return Config
     */
    protected function getConfig() {
        return $this->config;
    }

    /**
     * @param Config $config
     */
    public function setConfig(&$config) {
        $this->config = $config;
    }

    public function clear() {
        $this->routing = null;
        $this->container = null;
        $this->config = null;
    }


}