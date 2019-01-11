<?php

namespace Spark;

use Spark\Common\Collection\FluentIterables;
use Spark\Common\IllegalStateException;
use Spark\Common\Type\Orderable;
use Spark\Core\Definition\BeanConstructorFactory;
use Spark\Core\Definition\BeanDefinition;
use Spark\Core\Definition\BeanFactory;
use Spark\Core\Definition\BeanProxy;
use Spark\Core\Definition\SimpleBeanFactory;
use Spark\Core\Definition\ToInjectObserver;
use Spark\Core\Library\Annotations;
use Spark\Utils\Asserts;
use Spark\Utils\Collections;
use Spark\Utils\Functions;
use Spark\Utils\Objects;
use Spark\Utils\Predicates;
use Spark\Utils\Reflection\PropertyAnnotation;
use Spark\Utils\ReflectionUtils;
use Spark\Utils\StringUtils;

class Container {

    private $config;
    private $beanContainer = array();
    private $typeMap = array();

    private $initialized = false;

    private $waitingList = array();

    private $beanFactories = [];
    private $defaultBeanFactory;
    private $beanNames = [];

    public function __construct() {
        $this->defaultBeanFactory = new SimpleBeanFactory();
    }

    public function registerObj($obj) {
        $this->addBean(lcfirst(Objects::getSimpleClassName($obj)), $obj);
    }

    public function registerClass($beanName, $class) {

        $reflectionClass = new \ReflectionClass($class);

        if (ReflectionUtils::hasConstructParameters($reflectionClass)) {
            $methodParameters = FluentIterables::of($reflectionClass->getConstructor()->getParameters())
                ->convertToMap(Functions::field('name'))
                ->map(function ($param) {
                    return $param->getClass();
                })
                ->map(function ($cls) {
                    return Objects::isNotNull($cls) ? $cls->name : null;
                })
                ->get();

            $this->beanContainer[$beanName] = new BeanConstructorFactory($beanName, $class, $methodParameters);
        } else {
            $this->beanNames[$beanName] = $class;
        }
    }

    private function createBean($class) {
        $beanFactory = Collections::getValueOrDefault($this->beanFactories, $class, $this->defaultBeanFactory);
        /** @var BeanFactory $beanFactory */
        return $beanFactory->createNewBean($class);
    }

    public function addBean($name, $object, $replace = false, $canBeReplaced = false) {
        Asserts::checkState($replace || !isset($this->beanContainer[$name]), 'Bean already added: ' . $name);

        $beanDefinition = new BeanDefinition($name, $object, $this->getClassNames($object), $canBeReplaced);
        $this->addToContainer($beanDefinition);

        if ($this->initialized) {
            $waiting = $this->initLifeCycle($beanDefinition);

            if (Collections::isNotEmpty($waiting)) {
                throw new IllegalStateException('Problem with injection');
            }
        }
    }

    /**
     * @param $bean
     * @return array lista Observerów czekających na wstrzyknięcie.
     */
    public function injectTo(BeanDefinition $beanDef): array {
        $bean = $beanDef->getBean();

        if ($bean instanceof BeanProxy) {
            $variable = $bean->getBean();

            return FluentIterables::of()
                //InjectTo Cached Bean
                ->addAll($this->injectTo(new BeanDefinition($beanDef->getName(), $variable, $beanDef->getClassNames())))
                //Inject To Proxy
                ->addAll($this->injectToBeanOnly(new BeanDefinition($beanDef->getName(), $bean, $beanDef->getClassNames())))
                ->filter(Predicates::notNull())
                ->get();
        }

        if ($bean instanceof Core\ConfigAware) {
            $bean->setConfig($this->getConfig());
        }

        return $this->injectToBeanOnly($beanDef);
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
        return $this->getBeanDefinition($name)
            ->getBean();
    }

    public function hasBean($name) {
        return isset($this->beanContainer[$name]) && Objects::isNotNull($this->beanContainer[$name]);
    }

    /**
     * @param $type
     * @return array
     */
    public function getByType($type): array {
        if (Collections::hasKey($this->typeMap, (string)$type)) {
            return $this->typeMap[$type];
        }

        return array();
    }


    final public function initServices() {
        if (!$this->initialized) {


            // initialized flag must be before "injectAndCreate" for register bean in @PostConstruct block
            $this->initialized = true;
            $this->buildAllBeans();
        }
    }

    public function getBeanNames() {
        return Collections::getKeys($this->beanContainer);
    }

    final public function clear() {
        $this->beanContainer = array();
    }

    private function buildBeanAnnotation(BeanDefinition $def) {
        $bean = $def->getBean();
        $buildBeanDefinitions = $this->createBuildAnnotationBeans($bean);

        FluentIterables::of($buildBeanDefinitions)
            ->each(function ($newDef) {
                $this->addToContainer($newDef);
            });

        //Then - Update their relations
        /** @var BeanDefinition $beanDexf */
        $this->initLifeCyclesAndUpdateWaitingList($buildBeanDefinitions);
    }

    private function buildAllBeans() {

        Collections::stream($this->beanNames)
            ->each(function ($class, $beanName) {
                /** @var SimpleBeanFactory $factory */
                $obj = $this->createBean($class);
                $this->addToContainer(new BeanDefinition(
                    $beanName,
                    $obj,
                    $this->getClassNames($obj),
                    false
                ));
            });

        $this->initLifeCyclesAndUpdateWaitingList($this->beanContainer);

        Collections::stream($this->beanContainer)
            ->each(function ($beanDef) {
                $this->invokePostConstruct($beanDef);
            });

        if (Collections::isNotEmpty($this->waitingList)) {
            $message = "Missing Beans for:\n \n";
            foreach ($this->waitingList as $k => $obsList) {

                $obsListNames = FluentIterables::of($obsList)
                    ->map(Functions::invokeGetMethod(ToInjectObserver::D_BEAN_NAME_TO_INJECT))
                    ->get();

//                $obsListNames = FluentIterables::of($obsListNames)
//                    ->filter(Predicates::not(Predicates::in($beanNames)))
//                    ->get();

//                if (Collections::isNotEmpty($obsListNames)) {
                $message .= "( $k -> (" . $this->getName($obsListNames) . " )) <br/>\n";
//                }
            }

            echo $message;
            throw  new IllegalStateException("Can't match beans");
        }

        $this->sortOrderableTypes();
        $this->cleanTmpCollections();
    }

    /**
     * @param $waitingBeansNames
     * @return int
     */
    private function getName($waitingBeansNames = array()) {
        return StringUtils::join(',', $waitingBeansNames);
    }

    private function updateRelations(string $newBeanName) {
        $beansToUpdate = $this->getBeansToUpdate($newBeanName);

        if (Collections::isNotEmpty($beansToUpdate)) {
            $this->updateBeansRelation($beansToUpdate);
            $this->clearEmptyWaitingList();
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
        $beansToUpdate = Collections::stream($this->waitingList)
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
    private function updateBeansRelation($beansToUpdate) {

        if (Collections::isNotEmpty($beansToUpdate)) {
            /** @var ToInjectObserver $observer */
            foreach ($beansToUpdate as $observer) {
                $beanDef = $observer->getBeanDef();
                $this->removeFromWaitingList($observer);
                $this->initLifeCycle($beanDef);
            }
        }
    }

    /**
     * @param $object
     * @return array
     */
    private function getClassNames($object) {
        if ($object instanceof BeanProxy) {
            return Objects::getClassNames($object->getBean());
        }
        return Objects::getClassNames($object);
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

                $beanDef = new BeanDefinition($name, $newBean, $this->getClassNames($newBean));
                $buildedBeans[$name] = $beanDef;
            }
        );
        return $buildedBeans;
    }

    private function invokePostConstruct(BeanDefinition $beanDefinition) {
        $bean = $beanDefinition->getBean();
        if ($bean instanceof BeanProxy) {
            $bean = $bean->getBean();
        }

        ReflectionUtils::handleMethodAnnotation($bean, Annotations::POST_CONSTRUCT,
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

    /**
     * @param $name
     * @param $beanDefinition
     */
    private function addToContainer(BeanDefinition $beanDefinition) {
        $beanName = $beanDefinition->getName();

        if (Collections::hasKey($this->beanContainer, $beanName)) {
            $bean = $this->beanContainer[$beanName];
            Asserts::checkState($bean->canBeReplaced(), "Cannot add same bean twice! ($beanName)");
        }

        $this->beanContainer[$beanDefinition->getName()] = $beanDefinition;
        $this->addToTypeContainer($beanDefinition);
    }

    private function initLifeCycle(BeanDefinition $beanDef): array {

        if (!$beanDef->isReady()) {
            $waitingList = $this->injectTo($beanDef);

            if (Collections::isEmpty($waitingList)) {
                $beanDef->ready();
                $this->updateRelations($beanDef->getName());
                $this->buildBeanAnnotation($beanDef);
            }

            return $waitingList;
        }
        return array();
    }

    /**
     * @param $name
     * @return BeanDefinition
     */
    private function getBeanDefinition($name) {
        Asserts::checkState($this->hasBean($name), 'No bean with name: ' . $name);
        return $this->beanContainer[$name];
    }

    private function clearEmptyWaitingList() {
        foreach ($this->waitingList as $serviceName => $observerList) {
            if (Collections::isEmpty($observerList)) {
                Collections::removeByKey($this->waitingList, $serviceName);
            }
        }
    }

    /**
     * @param BeanDefinition $beanDef
     * @return array
     */
    private function injectToBeanOnly(BeanDefinition $beanDef): array {
        $bean = $beanDef->getBean();

        $overrideInjections = Annotations::getOverrideInjections(Objects::getClassName($bean));

        $result = ReflectionUtils::getPropertyAnnotations($bean, Annotations::INJECT);

        $waitingList = Collections::stream($result)
            ->map(function ($prop) use ($overrideInjections, $beanDef) {
                /** @var PropertyAnnotation $prop */
                $property = $prop->getReflectionProperty();
                $annotation = $prop->getAnnotation();

                $beanNameToInject = $this->getBeanName($property, $annotation);

                $swap = Collections::hasKey($overrideInjections, $beanNameToInject);
                if ($swap) {
                    $beanNameToInject = $overrideInjections[$beanNameToInject]->newName;
                }

                $hasKey = Collections::hasKey($this->beanContainer, $beanNameToInject);

                if ($hasKey) {
                    $property->setAccessible(true);
                    $toInject = $this->getBeanDefinition($beanNameToInject);
                    $property->setValue($beanDef->getBean(), $toInject->getBean());
                    return null;
                }

                return new ToInjectObserver($beanDef, $beanNameToInject);
            })
            ->filter(Predicates::notNull())
            ->convertToMap(Functions::get(ToInjectObserver::D_ID))
            ->get();

        return $waitingList;
    }

    public function registerFactory($class, BeanFactory $factory) {
        $this->beanFactories[$class] = $factory;
    }

    /**
     * @param $buildBeanDefinitions
     */
    private function initLifeCyclesAndUpdateWaitingList(array $buildBeanDefinitions): void {
        /** @var BeanDefinition $beanDef */
        foreach ($buildBeanDefinitions as $beanDef) {
            $waitingList = $this->initLifeCycle($beanDef);

            if (Collections::isNotEmpty($waitingList)) {
                $this->waitingList[$beanDef->getName()] = $waitingList;
            }
        }
    }

    private function cleanTmpCollections() {
        $this->beanFactories = [];
        $this->beanNames = [];
        $this->waitingList = [];
    }

    public function register($beanName, $class) {
        $this->addBean($beanName, $class, false, true);
    }

    public function getAll() : array {
        return $this->beanContainer;
    }
}
