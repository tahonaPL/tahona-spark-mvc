<?php

namespace Spark;

use Exception;
use Spark\Common\Collection\Entry;
use Spark\Common\IllegalArgumentException;
use Spark\Common\IllegalStateException;
use Spark\Common\Type\Orderable;
use Spark\Core\Definition\BeanDefinition;
use Spark\Core\Definition\BeanProxy;
use Spark\Core\Definition\ToInjectObserver;
use Spark\Core\Filler\Filler;
use Spark\Core\Library\Annotations;
use Spark\Core\Service\ServiceHelper;
use Spark\Utils\Asserts;
use Spark\Utils\Collections;
use Spark\Utils\Functions;
use Spark\Utils\Objects;
use Spark\Utils\ReflectionUtils;
use Spark\Utils\StringUtils;

class Container {

    private $config;
    private $beanContainer = array();
    private $typeMap = array();

    private $initialized = false;

    private $waitingList = array();
    const INJECT_ANNOTATION          = "Spark\core\annotation\Inject";
    const OVERRIDE_INJECT_ANNOTATION = "Spark\core\annotation\OverrideInject";

    public function registerObj($obj) {
        $this->register(lcfirst(Objects::getSimpleClassName($obj)), $obj);
    }

    public function register($name, $object) {
        Asserts::checkState(false === isset($this->beanContainer[$name]), "Bean already added: " . $name);

        $this->beanContainer[$name] = new BeanDefinition($name, $object, $this->getClassNames($object));

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

        $toInjectObservers = Collections::builder();
        if ($bean instanceof BeanProxy) {
            $toInjectObservers->addAll($this->injectTo($bean->getBean()));
        }

        if ($bean instanceof ServiceHelper) {
            $bean->setContainer($this);
        }
        if ($bean instanceof Core\ConfigAware) {
            $bean->setConfig($this->getConfig());
        }

        $overrideInjections = Annotations::getOverrideInjections(Objects::getClassName($bean));

        $result = ReflectionUtils::handlePropertyAnnotation($bean, Annotations::INJECT,
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
        $toInjectObservers->addAll($result);
        return $toInjectObservers->get();
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
        Asserts::checkState($this->hasBean($name), "No bean with name: " . $name);
        return $this->beanContainer[$name]->getBean();
    }

    public function hasBean($name) {
        return isset($this->beanContainer[$name]);
    }

    /**
     * @param $type
     * @return array
     */
    public function getByType($type) {
        if (Collections::hasKey($this->typeMap, (string)$type)) {
            return $this->typeMap[$type];
        }

        return array();
    }


    public final function initServices() {
        if (!$this->initialized) {

            // initialized flag must be before "injectAndCreate" for register bean in @PostConstruct block
            $this->initialized = true;
            $this->injectAndCreate($this->beanContainer);
        }
    }

    public function getBeanNames() {
        return Collections::getKeys($this->beanContainer);
    }


    public final function clear() {
        $this->beanContainer = array();
    }

    private function buildBeanAnnotation(BeanDefinition $def) {
        $bean = $def->getBean();
        $buildBeanDefinitions = $this->createBuildAnnotationBeans($bean);

        Collections::builder($buildBeanDefinitions)
            ->each(function ($def) {
                /** @var BeanDefinition $def */
                $this->beanContainer[$def->getName()] = $def;
            });

        //Then - Update their relations
        /** @var BeanDefinition $beanDef */
        foreach ($buildBeanDefinitions as $beanDef) {
            $this->updateRelations($beanDef->getName());

            $newBean = $beanDef->getBean();
            $waitingList = $this->injectTo($newBean);

            if (Collections::isEmpty($waitingList)) {
                $this->buildBeanAnnotation($beanDef);
            } else {
                $this->waitingList[$beanDef->getName()] = $waitingList;
            }
        }

        $this->addToTypeContainer($def);
        $this->invokePostConstruct($bean);
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

        //Build all @Build beans without wainting list  and update others waiting relations.
        $excludeBuildNamesList = Collections::getKeys($this->waitingList);

        foreach ($this->beanContainer as $serviceName => $definition) {
            if (!Collections::contains($serviceName, $excludeBuildNamesList)) {
                $this->buildBeanAnnotation($definition);
            }
        }

        if (Collections::isNotEmpty($this->waitingList)) {
            $message = "Missing Beans for: ";
            foreach ($this->waitingList as $k => $obsList) {
                $message .= "( $k -> (" . $this->getName($obsList) . " )) <br/>";
            }

            echo $message;
            throw  new IllegalStateException("Can't match beans");
        }

        $this->sortOrderableTypes();

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

            //if buildBean invoke multiple times
            $arry = array();
            foreach ($this->waitingList as $serviceName => $observerList) {
                if (Collections::isEmpty($observerList)) {
                    $arry[$serviceName] = $observerList;
                    Collections::removeByKey($this->waitingList, $serviceName);
                }
            }

            foreach ($arry as $serviceName => $observerList) {
                $this->buildBeanAnnotation($this->beanContainer[$serviceName]);
            }
        }
    }


    private function getBeanName(\ReflectionProperty $property, $annotation) {
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
                return StringUtils::equals($observer->getBeanNameToInject(), $name);
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

                $overrideInjections = Collections::builder(ReflectionUtils::getClassAnnotation(Objects::getClassName($targetBean), self::OVERRIDE_INJECT_ANNOTATION))
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

    /**
     * @param $object
     * @return array
     */
    private function getClassNames($object) {
        if ($object instanceof BeanProxy) {
            $classNames = Objects::getClassNames($object->getBean());
            return $classNames;
        } else {
            $classNames = Objects::getClassNames($object);
            return $classNames;
        }
    }

    /**
     * @param $bean
     * @return array - bean definitions
     */
    private function createBuildAnnotationBeans($bean) {
        $buildedBeans = array();
        //First - Build  all subBeans...
        ReflectionUtils::handleMethodAnnotation($bean, Annotations::BEAN,
            function ($bean, \ReflectionMethod $method, $annotation) use (&$buildedBeans) {
                if (StringUtils::isNotBlank($annotation->name)) {
                    $name = $annotation->name;
                } else {
                    $name = $method->getName();
                }

                $method->setAccessible(true);
                $newBean = $method->invoke($bean);
                //TODO cacheBean ?

                $beanDef = new BeanDefinition($name, $newBean, $this->getClassNames($newBean));
                $buildedBeans[$name] = $beanDef;
            }
        );
        return $buildedBeans;
    }

    /**
     * @param $bean
     */
    private function invokePostConstruct($bean) {
        ReflectionUtils::handleMethodAnnotation($bean, "Spark\\core\\annotation\\PostConstruct",
            function ($bean, \ReflectionMethod $method, $annotation) {
                $method->setAccessible(true);
                $method->invoke($bean);
            }
        );
    }


    /**
     * @param $beanDefinition
     */
    private function addToTypeContainer(BeanDefinition $beanDefinition) {
        $classNames = $beanDefinition->getClassNames();
        foreach ($classNames as $className) {

            if (!Collections::hasKey($this->typeMap, $className)) {
                $this->typeMap[$className] = [];
            }
            $this->typeMap[$className][$beanDefinition->getName()] = $beanDefinition->getBean();
        }
    }

    /**
     *
     */
    private function sortOrderableTypes() {
        foreach ($this->typeMap as $className => $beans) {

            if (Collections::first($beans)->get() instanceof Orderable) {

                $this->typeMap[$className] = Collections::builder($beans)
                    ->entries()
                    ->sort(function ($x, $y) {
                        return $x->getValue()->getOrder() - $y->getValue()->getOrder();
                    })
                    ->convertToMap(function ($entry) {
                        return $entry->getKey();
                    })
                    ->map(function ($entry) {
                        return $entry->getValue();
                    })
                    ->get();
            }
        }
    }
}
