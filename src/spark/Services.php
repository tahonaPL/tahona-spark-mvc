<?php

namespace spark;

use Exception;
use spark\core\module\LoadModule;
use spark\core\service\ServiceHelper;
use spark\utils\Asserts;
use spark\utils\Collections;
use spark\utils\Objects;
use spark\utils\ReflectionUtils;
use spark\utils\StringUtils;

abstract class Services {

    private $config;
    private $beanContainer = array();
    private $filters = array();

    private $initialized = false;

    public function registerObj($obj) {
        $this->register(lcfirst(Objects::getSimpleClassName($obj)), $obj);
    }

    public function register($name, $object) {
        Asserts::checkState(false === isset($this->beanContainer[$name]), "Bean already added: " . $name);

        $this->beanContainer[$name] = $object;
        if ($this->initialized) {
            $this->injectTo($this->beanContainer[$name]);
        }
    }

    public function injectTo($bean) {
        $annotationName = "spark\core\di\Inject";
        ReflectionUtils::handlePropertyAnnotation($bean, $annotationName,
            function ($bean, \ReflectionProperty $property, $annotation) {
                if (StringUtils::isNotBlank($annotation->name)) {
                    $name = $annotation->name;
                } else {
                    $name = $property->getName();
                }

                if (Collections::hasKey($this->beanContainer, $name)) {
                    $property->setAccessible(true);
                    $property->setValue($bean, $this->beanContainer[$name]);
                }
            }
        );

        if ($bean instanceof ServiceHelper) {
            $bean->setServices($this);
        }
        if ($bean instanceof core\ConfigAware) {
            $bean->setConfig($this->getConfig());
        }
    }

    /**
     *
     * @return Config
     */
    public function getConfig() {
        return $this->config;
    }

    public function setConfig($config) {
        $this->config = $config;
    }

    public function get($name) {
        if (false == isset($this->beanContainer[$name])) {
            throw new Exception("No bean with name: " . $name);
        }
        return $this->beanContainer[$name];
    }

    public final function initServices() {
        $this->beforeInit();
        $this->init();

        $this->buildBeanAnnotation($this);
        foreach ($this->beanContainer as $serviceName => $service) {
            $this->buildBeanAnnotation($service);
        }

        foreach ($this->beanContainer as $serviceName => $service) {
            $this->injectTo($service);
        }

        $this->filters = $this->initFilters();
        foreach ($this->filters as $filter) {
            $this->injectTo($filter);
        }

        $this->initialized = true;

        $this->afterInit();
    }

    public function init() {
        $modules = $this->getModules();
        Asserts::checkState(Collections::isNotEmpty($modules), "Modules should not be empty");

        foreach ($modules as $module) {
            /** @var LoadModule $module */
            $module->load($this);
        }
    }

    /**
     * Filter array of filters
     * @return array
     */
    protected function initFilters() {
        return array();
    }

    public final function clear() {
        $this->beanContainer = array();
    }

    public function getFilters() {
        return $this->filters;
    }

    private function buildBeanAnnotation($bean) {
        ReflectionUtils::handleMethodAnnotation($bean, "spark\core\\di\\Bean",
            function ($bean, \ReflectionMethod $method, $annotation) {
                if (StringUtils::isNotBlank($annotation->name)) {
                    $name = $annotation->name;
                } else {
                    $name = $method->getName();
                }

                $method->setAccessible(true);
                $newBean = $method->invoke($bean);
                $this->beanContainer[$name] = $newBean;

                $this->buildBeanAnnotation($newBean);
            }
        );
    }

    protected function afterInit() {
        //hook
    }

    protected function beforeInit() {
        //hook
    }

    protected abstract function getModules();

}
