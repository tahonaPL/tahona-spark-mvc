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
    }

    public function __construct(EngineConfig $config) {
        $this->config = $config;
    }

    private function init() {
        $classLoader = new TestClassLoader();

        $rootAppPath = $this->config->getRootAppPath();
        $namespaceRootPath = array(
            $rootAppPath . "/src"
        );


        foreach ($this->config->getNamespaces() as $space) {
            $classLoader->registerNamespace($space, $namespaceRootPath);
        }

        $classLoader->register();
    }

}