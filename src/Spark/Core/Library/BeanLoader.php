<?php
/**
 *
 *
 * Date: 31.01.17
 * Time: 09:25
 */

namespace Spark\Core\Library;


use function foo\func;
use ReflectionClass;
use Spark\Common\Collection\FluentIterables;
use Spark\Config;
use Spark\Container;
use Spark\Core\Annotation\Configuration;
use Spark\Core\Annotation\Handler\AnnotationHandler;
use Spark\Core\Processor\InitAnnotationProcessors;
use Spark\Utils\Asserts;
use Spark\Utils\Collections;
use Spark\Utils\FileUtils;
use Spark\Utils\Functions;
use Spark\Utils\Predicates;
use Spark\Utils\ReflectionUtils;
use Spark\Utils\StringUtils;

class BeanLoader {

    private $classesInSrc;

    /**
     * @var InitAnnotationProcessors
     */
    private $annotationProcessor;


    private $postLoad;
    /**
     * @var Config
     */
    private $config;

    /**
     * @var Container
     */
    private $container;

    /**
     * BeanLoader constructor.
     * @param $annotationProcessor
     * @param Config $config
     */
    public function __construct($annotationProcessor, Config $config, $container) {
        $this->annotationProcessor = $annotationProcessor;

        $this->classesInSrc = array();
        $this->postLoad = array();

        $this->config = $config;
        $this->container = $container;
    }

    /**
     * @param $classPath
     * @param $classesInPath
     */
    public function addClass($classPath) {
        $this->checkClassExist($classPath);

        $this->classesInSrc[$classPath] = new ReflectionClass($classPath);
    }

    /**
     * When AnnotationHandler must load first and when "Enable" annotation will be loaded,
     * then Framework will load corespondent @Configuration file.
     *
     * @param PostLoadDefinition $definition
     */
    public function addPostLoadLib(PostLoadDefinition $definition) {
        $classPath = $definition->getClass();
        $this->checkClassExist($classPath);

        $this->postLoad[$definition->getClass()] = $definition;
    }

    public function addFromPath($src, $excludeDir = array()) {
        $this->classesInSrc = Collections::stream(FileUtils::getAllClassesInPath($src))
            ->filter(function ($cls) use ($excludeDir) {
                return !Collections::builder($excludeDir)->anyMatch(function ($x) use ($cls) {
                    /** @var ReflectionClass $cls */
                    return StringUtils::startsWith($cls, $x);
                });
            })
            ->map(function ($name) {
                return new ReflectionClass($name);
            })
            ->addAll($this->classesInSrc)
            ->convertToMap(Functions::field('name'))
            ->get();
    }

    /**
     *  Function that process all bean ins application with Spark basics annotation handlers
     * @throws \ReflectionException
     */
    public function process(): void {
        foreach ($this->classesInSrc as $classReflection) {
            //project beans
            $this->annotationProcessor->processClass($classReflection);
            $this->annotationProcessor->processAnnotations($classReflection);
        }
    }

    /**
     *  Function that process all bean with user custom Annotation Handlers after bean initialization.
     *  Functionality is limited and do not work for creation new beans and auto inject (Inject annotation),
     *  but for everything else works well.
     */
    public function postProcess() {
        $handlers = $this->container->getByType(AnnotationHandler::class);
        Collections::builder($handlers)
            ->each(function ($h) {
                $this->annotationProcessor->addPostHandler($h);
            });

        foreach ($this->classesInSrc as $classReflection) {
            //project beans
            $this->annotationProcessor->processPostAnnotations($classReflection);
        }

        //Spark beans
        /** @var PostLoadDefinition $postLoadDefinition */
        foreach ($this->postLoad as $postLoadDefinition) {
            if ($postLoadDefinition->canLoad()) {
                $this->annotationProcessor->processPostAnnotations(
                    new ReflectionClass($postLoadDefinition->getClass())
                );
            }
        }
        $this->annotationProcessor->clear();
    }

    /**
     * @param $classPath
     * @throws \Spark\Common\IllegalArgumentException
     */
    private function checkClassExist($classPath) {
        Asserts::checkArgument(class_exists($classPath), "Class doesn't exist : (" . $classPath . ")");
    }

}