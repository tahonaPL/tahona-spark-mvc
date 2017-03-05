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
use spark\core\annotation\Configuration;
use spark\core\processor\InitAnnotationProcessors;
use spark\utils\Collections;
use spark\utils\Dev;
use spark\utils\FileUtils;
use spark\utils\Predicates;
use spark\utils\ReflectionUtils;
use spark\utils\StringUtils;

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

    /**
     * When AnnotationHandler must load first and when "Enable" annotation will be loaded,
     * then Framework will load corespondent @Configuration file.
     *
     * @param PostLoadDefinition $definition
     */
    public function addPostLoadLib(PostLoadDefinition $definition) {
        $class_exists = class_exists($definition->getClass());

        if ($class_exists) {
            $this->postLoad[$definition->getClass()] = $definition;
        }
    }

    public function addFromPath($src, $excludeDir = array()) {
        $this->classesInSrc = Collections::builder(FileUtils::getAllClassesInPath($src))
            ->filter(function ($cls) use ($excludeDir) {
                return !Collections::builder($excludeDir)->anyMatch(function($x) use ($cls){
                    return StringUtils::startsWith($cls,$x);
                });
            })
            ->get();
    }

    public function addPersistanceLib() {
        $configClass = "spark\\persistence\\PersistenceConfig";

        if (class_exists($configClass)) {
            $this->addPostLoadLib(new PostLoadDefinition($configClass, function () {
                return $this->config->getProperty("spark.data.repository.enabled", false);
            }));

            $this->annotationProcessor->addHandler(new \spark\persistence\annotation\handler\EnableDataRepositoryAnnotationHandler());

            $this->addPostLoadLib(new PostLoadDefinition("spark\\tools\\mail\\MailerConfig", function () {
                return $this->config->getProperty("spark.mailer.enabled", false);
            }));

            $this->annotationProcessor->addHandler(new \spark\tools\mail\annotation\handler\EnableMailerAnnotationHandler());
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
        $this->annotationProcessor->handleClassAnnotations($classAnnotations, $class, $reflectionObject);

        $reflectionMethods = $reflectionObject->getMethods();
        foreach ($reflectionMethods as $method) {
            $methodAnnotations = $this->annotationReader->getMethodAnnotations($method);
            $this->annotationProcessor->handleMethodAnnotations($methodAnnotations, $class, $method);
        }

        $reflectionProperties = $reflectionObject->getProperties();
        foreach ($reflectionProperties as $property) {
            $methodAnnotations = $this->annotationReader->getPropertyAnnotations($property);
            $this->annotationProcessor->handleFieldAnnotations($methodAnnotations, $class, $property);
        }
    }
}