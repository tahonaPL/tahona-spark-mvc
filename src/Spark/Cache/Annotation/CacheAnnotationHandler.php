<?php
/**
 *
 * 
 * Date: 10.06.17
 * Time: 03:03
 */

namespace Spark\Cache\Annotation;


use ReflectionMethod;
use Spark\Cache\Factory\CachedBeanFactory;
use Spark\Cache\Service\CacheService;
use Spark\Core\Annotation\Handler\AnnotationHandler;
use Spark\Core\Library\Annotations;
use Spark\Utils\Collections;
use Spark\Utils\Functions;
use Spark\Utils\Objects;
use Spark\Utils\Predicates;
use Spark\Utils\StringUtils;

class CacheAnnotationHandler extends AnnotationHandler {

    private $annotationName;

    /**
     * @var CacheService
     */
    private $cacheService;

    public function __construct() {
        $this->annotationName = "Spark\\Cache\\Annotation\\Cache";
    }

    public function handleMethodAnnotations($annotations = array(), $class, ReflectionMethod $methodReflection) {

        $cacheAnnotation = $this->getAnnotations($annotations);

        if (Collections::isNotEmpty($cacheAnnotation)) {

            $cacheService = $this->getCacheService();
            foreach ($cacheAnnotation as $annotation) {


                if (Objects::isNotNull($cacheService)) {
                    //will be executed as post action
                    $cacheService->addDefinition(
                        $class,
                        $methodReflection->name,
                        $annotation->cache,
                        $annotation->key,
                        $annotation->time
                    );
                } else {
                    // init action
                    $this->getContainer()->registerFactory($class, new CachedBeanFactory());

                }
            }
        }
    }

    private function getClassName() {
        return Functions::getClassName();
    }

    /**
     *
     * @return CacheService
     * @throws \Exception
     */
    private function getCacheService() {
        /** @var CacheService $securityManager */
        $container = $this->getContainer();

        if (Objects::isNull($this->cacheService) && $container->hasBean(CacheService::NAME)) {
            $this->cacheService = $container->get(  CacheService::NAME);
        }
        return $this->cacheService;
    }

    /**
     *
     * @param $annotations
     * @return array
     */
    private function getAnnotations($annotations) {
        return Collections::builder($annotations)
            ->filter(Predicates::compute($this->getClassName(), Predicates::equals($this->annotationName)))
            ->get();
    }


}