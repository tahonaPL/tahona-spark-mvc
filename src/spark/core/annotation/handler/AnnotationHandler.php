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
use spark\Services;

abstract class AnnotationHandler {

    /**
     * @var Services
     */
    private $services;

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
     * @return Services
     */
    protected function getServices() {
        return $this->services;
    }

    /**
     * @param Services $services
     */
    public function setServices(&$services) {
        $this->services = $services;
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