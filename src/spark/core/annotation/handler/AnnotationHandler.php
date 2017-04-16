<?php
/**
 * Created by PhpStorm.
 * User: primosz67
 * Date: 30.01.17
 * Time: 08:57
 */

namespace spark\core\annotation\handler;


use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;
use spark\Config;
use spark\Routing;
use spark\routing\RoutingInfo;
use spark\Container;

abstract class AnnotationHandler {

    /**
     * @var Container
     */
    private $container;

    /**
     * @var Routing
     */
    private $routing;

    /**
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


}