<?php

namespace spark;

use Exception;
use spark\common\IllegalArgumentException;
use spark\common\IllegalStateException;
use spark\core\definition\BeanDefinition;
use spark\core\definition\ToInjectObserver;
use spark\core\library\Annotations;
use spark\core\service\ServiceHelper;
use spark\filter\HttpFilter;
use spark\utils\Asserts;
use spark\utils\Collections;
use spark\utils\Functions;
use spark\utils\Objects;
use spark\utils\ReflectionUtils;
use spark\utils\StringUtils;

class Container {

    private $config;
    private $beanContainer = array();
    private $filters = array();

    private $initialized = false;
    private $waitingList = array();

    const INJECT_ANNOTATION = "spark\core\annotation\Inject";
    const OVERRIDE_INJECT_ANNOTATION = "spark\core\annotation\OverrideInject";

    public function registerObj($obj) {
        $this->register(lcfirst(Objects::getSimpleClassName($obj)), $obj);
    }

    public function register($name, $object) {
        Asserts::checkState(false === isset($this->beanContainer[$name]), "Bean already added: " . $name);

        $this->beanContainer[$name] = new BeanDefinition($name, $object);

        if ($this->initialized) {
            /** @var BeanDefinition $definition */
            $definition = $this->beanContainer[$name];

            $this->updateRelations($name);

            $bean = $definition->getBean();
            $this->injectTo($bean);
            $this->buildBeanAnnotation($bean);
        }
    }

    /**
     * @param $bean
     * @return array  lista Observerów czekających na wstrzyknięcie.
     */
    public function injectTo($bean) {

        if ($bean instanceof ServiceHelper) {
            $bean->setContainer($this);
        }
        if ($bean instanceof core\ConfigAware) {
            $bean->setConfig($this->getConfig());
        }

        $overrideInjections = Annotations::getOverrideInjections(Objects::getClassName($bean));

        return ReflectionUtils::handlePropertyAnnotation($bean, Annotations::INJECT,
            function ($bean, \ReflectionProperty $property, $annotation) use ($overrideInjections) {
                $beanNameToInject = $this->getBeanName($property, $annotation);

                $swap = Collections::hasKey($overrideInjections, $beanNameToInject);
                if ($swap) {
                    $beanNameToInject = $overrideInjections[$beanNameToInject]->newName;
                }

                $hasKey = Collections::hasKey($this->beanContainer, $beanNameToInject);

                if ($hasKey) {
                    $property->setAccessible(true);
                    $property->setValue($bean, $this->get($beanNameToInject));
                    return null;
                }

                return new ToInjectObserver($bean, $beanNameToInject);
            }
        );
    }

    /**
     *
     * @return Config
     */
    public function getConfig() {
        return $this->config;
    }

    public function setConfig(&$config) {
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

        $this->initialized = true;

        $beansToInject = $this->beanContainer;

        $this->injectAndCreate($beansToInject);

        $this->filters = $this->getByType(HttpFilter::CLASS_NAME);

        foreach ($this->filters as $filter) {
            $this->injectTo($filter);
        }

        $this->afterInit();
    }

    public function getBeanNames() {
        return Collections::getKeys($this->beanContainer);
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

        $buildedBeans = array();
        //First - Build  all subBeans...
        ReflectionUtils::handleMethodAnnotation($bean, "spark\\core\\annotation\\Bean",
            function ($bean, \ReflectionMethod $method, $annotation) use (&$buildedBeans) {
                if (StringUtils::isNotBlank($annotation->name)) {
                    $name = $annotation->name;
                } else {
                    $name = $method->getName();
                }

                $method->setAccessible(true);
                $newBean = $method->invoke($bean);

                $beanDef = new BeanDefinition($name, $newBean);
                $buildedBeans[$name] = $beanDef;
                $this->beanContainer[$name] = $beanDef;

            }
        );

        //Then - Update their relations
        /** @var BeanDefinition $beanDef */
        foreach ($buildedBeans as $beanDef) {
            $this->updateRelations($beanDef->getName());

            $newBean = $beanDef->getBean();
            $this->injectTo($newBean);
            $this->buildBeanAnnotation($newBean);
        }


        ReflectionUtils::handleMethodAnnotation($bean, "spark\\core\\annotation\\PostConstruct",
            function ($bean, \ReflectionMethod $method, $annotation) {
                $method->setAccessible(true);
                $method->invoke($bean);
            }
        );
    }

    protected function afterInit() {
        //hook
    }

    protected function beforeInit() {
        //hook
    }

    protected function getModules() {
        return array();
    }

    /**
     * @param $beansToInject
     */
    private function injectAndCreate(&$beansToInject) {

        //Inject all beans already created
        /** @var BeanDefinition $definition */
        foreach ($beansToInject as $serviceName => $definition) {
            $bean = $definition->getBean();
            $failedInjectionList = $this->injectTo($bean);

            if (Collections::isNotEmpty($failedInjectionList)) {
                $this->waitingList[$serviceName] = $failedInjectionList;
            } else {
                $this->beanContainer[$serviceName] = $definition;
            }
        }


        //Build Normal
        $excludeBuildNamesList = Collections::getKeys($this->waitingList);

        foreach ($this->beanContainer as $serviceName => $definition) {
            if (!Collections::contains($serviceName, $excludeBuildNamesList)) {
                $this->buildBeanAnnotation($definition->getBean());
            }
        }

        if (Collections::isNotEmpty($this->waitingList)) {
            $message = "Missing Beans for: ";
            foreach ($this->waitingList as $k => $obsList) {
                $message .= "( $k -> (" . $this->getName($obsList) . " ) <br/>";
            }

            throw  new IllegalArgumentException($message);
        }
    }

    /**
     * @param $waitingBeans
     * @return int
     */
    private function getName($waitingBeans = array()) {
        $arr = Collections::builder($waitingBeans)
            ->map(Functions::invokeGetMethod(ToInjectObserver::D_BEAN_NAME_TO_INJECT))
            ->get();
        return StringUtils::join(",", $arr);
    }

    private function updateRelations($newBeanName) {
        $beanDefinition = &$this->beanContainer[$newBeanName];
        $beansToUpdate = $this->getBeansToUpdate($newBeanName);


        if (Collections::isNotEmpty($beansToUpdate)) {

            $this->updateBeansRelation($beansToUpdate, $beanDefinition);

            //Remove beans from waiting list if there is nothing to injdect
            /** @var ToInjectObserver $obs */
            foreach ($this->waitingList as $serviceName => $observerList) {
                if (Collections::isEmpty($observerList)) {
                    Collections::removeByKey($this->waitingList, $serviceName);
                    $this->buildBeanAnnotation($this->get($serviceName));
                }
            }
        }
//        var_dump($this->waitingList);exit();
    }

    public function getBeanName(\ReflectionProperty $property, $annotation) {
        if (StringUtils::isNotBlank($annotation->name)) {
            return $annotation->name;
        } else {
            return $property->getName();
        }
    }

    private function removeFromWaitingList(&$observer) {
        $waitingList = &$this->waitingList;
        foreach ($waitingList as $beanName => &$observerList) {
            Collections::removeByKey($observerList, $observer->getId());
        }

    }

    /**
     * @param $name
     * @return array
     */
    private function getBeansToUpdate($name) {
        $beansToUpdate = Collections::builder($this->waitingList)
            ->flatMap(Functions::getSameObject())
            ->filter(function ($observer) use ($name) {
                /** @var ToInjectObserver $observer */
                return  StringUtils::equals($observer->getBeanNameToInject(), $name);
            })->get();
        return $beansToUpdate;
    }

    /**
     * @param $beansToUpdate
     * @param $beanDefinition
     */
    private function updateBeansRelation($beansToUpdate, $beanDefinition) {
//
        if (Collections::isNotEmpty($beansToUpdate)) {
            /** @var ToInjectObserver $observer */
            foreach ($beansToUpdate as $observer) {
                $targetBean = $observer->getBean();

                $overrideInjections = Collections::builder(ReflectionUtils::getClassAnnotations(Objects::getClassName($targetBean), self::OVERRIDE_INJECT_ANNOTATION))
                    ->convertToMap(Functions::field("oldName"))
                    ->get();

                ReflectionUtils::handlePropertyAnnotation($targetBean, self::INJECT_ANNOTATION,
                    function (&$bean, \ReflectionProperty $property, $annotation) use ($observer, $beanDefinition, $overrideInjections) {
                        $beanNameToInject = $this->getBeanName($property, $annotation);

                        if (Collections::hasKey($overrideInjections, $beanNameToInject)) {
                            $beanNameToInject = $overrideInjections[$beanNameToInject]->newName;
                        }

                        if (StringUtils::equals($beanNameToInject, $observer->getBeanNameToInject())) {
                            $property->setAccessible(true);
                            $property->setValue($bean, $beanDefinition->getBean());

                            $this->removeFromWaitingList($observer);
                        }
                        return null;
                    });

            }
        }
    }

}