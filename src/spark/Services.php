<?php

namespace spark;

use Exception;
use spark\core\definition\BeanDefinition;
use spark\core\module\LoadModule;
use spark\core\service\ServiceHelper;
use spark\utils\Asserts;
use spark\utils\Collections;
use spark\utils\Functions;
use spark\utils\Objects;
use spark\utils\ReflectionUtils;
use spark\utils\StringUtils;

class Services {

    private $config;
    private $beanContainer = array();
    private $filters = array();

    private $initialized = false;

    public function registerObj($obj) {
        $this->register(lcfirst(Objects::getSimpleClassName($obj)), $obj);
    }

    public function register($name, $object) {
        Asserts::checkState(false === isset($this->beanContainer[$name]), "Bean already added: " . $name);

        $this->beanContainer[$name] = new BeanDefinition($name, $object);

        if ($this->initialized) {
            /** @var BeanDefinition $definition */
            $definition = $this->beanContainer[$name];
            $this->injectTo($definition->getBean());
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
                    $property->setValue($bean, $this->beanContainer[$name]->getBean());
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
        return $this->beanContainer[$name]->getBean();
    }


    public function getByType($type) {
        return Collections::builder($this->beanContainer)
            ->filter(function ($definition) use ($type) {
                /** @var BeanDefinition $definition */
                return $definition->hasType($type);
            })
            ->map(Functions::invokeGetMethod(BeanDefinition::D_BEAN))
            ->get();
    }

    public final function initServices() {
        $this->beforeInit();

        foreach ($this->beanContainer as $serviceName => $service) {
            $this->buildBeanAnnotation($service->getBean());
        }

        foreach ($this->beanContainer as $serviceName => $service) {
            $this->injectTo($service->getBean());
        }

        $this->filters = $this->initFilters();
        foreach ($this->filters as $filter) {
            $this->injectTo($filter);
        }

        $this->initialized = true;

        $this->afterInit();
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
                $this->beanContainer[$name] = new BeanDefinition($name, $newBean);

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

    protected function getModules(){
        return array();
    }

}
