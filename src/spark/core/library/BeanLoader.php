<?php
/**
 * Created by PhpStorm.
 * User: primosz67
 * Date: 31.01.17
 * Time: 09:25
 */

namespace spark\core\library;


use ReflectionClass;
use spark\Config;
use spark\core\processor\InitAnnotationProcessors;
use spark\utils\Collections;
use spark\utils\FileUtils;
use spark\utils\ReflectionUtils;

class BeanLoader {

    private $classesInSrc;
    private $localLib;

    /**
     * @var InitAnnotationProcessors
     */
    private $annotationProcessor;


    private $postLoad;
    private $annotationReader;
    /**
     * @var Config
     */
    private $config;

    /**
     * BeanLoader constructor.
     * @param $classesInSrc
     */
    public function __construct($annotationProcessor, Config $config) {
        $this->annotationProcessor = $annotationProcessor;

        $this->classesInSrc = array();
        $this->localLib = array();
        $this->postLoad = array();

        $this->config = $config;

        $this->annotationReader = ReflectionUtils::getReaderInstance();
    }

    /**
     * @param $classPath
     * @param $classesInPath
     */
    public function addLib($classPath) {
        $class_exists = class_exists($classPath);

        if ($class_exists) {
            $this->localLib[$classPath] = $classPath;
        }
    }

    public function addPostLoadLib(PostLoadDefinition $definition) {
        $class_exists = class_exists($definition->getClass());

        if ($class_exists) {
            $this->postLoad[$definition->getClass()] = $definition;
        }
    }

    public function addFromPath($src) {
        Collections::addAllOrReplace($this->classesInSrc, FileUtils::getAllClassesInPath($src));
    }

    public function addPersistanceLib() {
        $configClass = "spark\\persistence\\PersistenceConfig";

        if (class_exists($configClass)) {
            $this->addPostLoadLib(new PostLoadDefinition($configClass, function () {
                $this->config->getProperty("spark.data.repository.enabled", false);
            }));

            $this->annotationProcessor->addHandler(new \spark\persistence\annotation\handler\EnableDataRepositoryAnnotationHandler());
        }
    }

    public function process() {
        foreach ($this->classesInSrc as $class) {
            //project beans
            $this->processAnnotations($class);
        }

        //spark beans
        /** @var PostLoadDefinition $postLoadDefinition */
        foreach ($this->postLoad as $postLoadDefinition) {
            if ($postLoadDefinition->canLoad()) {
                $this->processAnnotations($postLoadDefinition->getClass());
            }
        }
    }

    /**
     * @param $class
     */
    private function processAnnotations($class) {
        $reflectionObject = new ReflectionClass($class);
        $classAnnotations = $this->annotationReader->getClassAnnotations($reflectionObject);
        $this->annotationProcessor->handleClassAnnotations($classAnnotations, null, $reflectionObject);

        $reflectionMethods = $reflectionObject->getMethods();
        foreach ($reflectionMethods as $method) {
            $methodAnnotations = $this->annotationReader->getMethodAnnotations($method);
            $this->annotationProcessor->handleMethodAnnotations($methodAnnotations, null, $method);
        }

        $reflectionProperties = $reflectionObject->getProperties();
        foreach ($reflectionProperties as $property) {
            $methodAnnotations = $this->annotationReader->getPropertyAnnotations($property);
            $this->annotationProcessor->handleFieldAnnotations($methodAnnotations, null, $property);
        }
    }

}