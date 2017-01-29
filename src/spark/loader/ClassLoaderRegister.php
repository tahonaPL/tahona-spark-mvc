<?php
/**
 * Created by PhpStorm.
 * User: primosz67
 * Date: 19.10.16
 * Time: 23:15
 */

namespace spark\loader;


use Doctrine\Common\Annotations\AnnotationRegistry;
use spark\core\engine\EngineConfig;
use spark\utils\Objects;

class ClassLoaderRegister {

    /**
     * @var EngineConfig
     */
    private $config;

    public static function register(EngineConfig $config) {
        $register = new ClassLoaderRegister($config);
        $register->init();

        AnnotationRegistry::registerLoader('class_exists');
    }

    public function __construct(EngineConfig $config) {
        $this->config = $config;
    }


    /**
     * @return ClassLoader
     */
    private function createClassLoader() {
        $beanCache = $this->config->isBeanCacheEnabled();

        if ($beanCache && extension_loaded('apc')) {
            $namespace = $this->config->getRootNamespace();
            $configName = $this->config->getConfigName();
            $rootAppPath = $this->config->getRootAppPath();

            $prefix = "apc.spark_" . $configName . "_" . $namespace . "_" . $rootAppPath;

            return new ApcUniversalClassLoader($prefix);
        } else {
            return new TestClassLoader();
        }
    }

    private function registerLocalNamespaces($classLoader) {
        $rootAppPath = $this->config->getRootAppPath();
        $namespaceRootPath = array(
            $rootAppPath . "/src",
            $rootAppPath . "/config"
        );

        foreach ($this->config->getNamespaces() as $space) {
            $classLoader->registerNamespace($space, $namespaceRootPath);
        }
    }

    private function init() {
        $classLoader = $this->createClassLoader();
        $this->registerLocalNamespaces($classLoader);
        $classLoader->registerNamespaces($this->config->getVendors());
        $classLoader->register();
    }

}