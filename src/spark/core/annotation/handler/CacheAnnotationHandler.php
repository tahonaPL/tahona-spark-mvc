<?php
/**
 * Created by PhpStorm.
 * User: crownclown67
 * Date: 10.06.17
 * Time: 03:03
 */

namespace spark\core\annotation\handler;


use ReflectionMethod;
use spark\cache\service\CacheService;
use spark\utils\Collections;
use spark\utils\Functions;
use spark\utils\Objects;
use spark\utils\Predicates;
use spark\utils\StringUtils;

class CacheAnnotationHandler extends AnnotationHandler {

    private $annotationName;

    /**
     * @var CacheService
     */
    private $cacheService;

    public function __construct() {
        $this->annotationName = "spark\\core\\annotation\\Cache";
    }


    public function handleMethodAnnotations($annotations = array(), $class, ReflectionMethod $methodReflection) {

        $cacheAnnotation = $this->getAnnotations($annotations);

        if (Collections::isNotEmpty($cacheAnnotation)) {

            $cacheService = $this->getCacheService();
            foreach ($cacheAnnotation as $annotation) {
                $cacheService->addDefinition(
                    $class,
                    $methodReflection->name,
                    $annotation->cache,
                    $annotation->key,
                    $annotation->time
                );
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
        if (Objects::isNull($this->cacheService)) {
            $this->cacheService = $this->getContainer()->get(CacheService::NAME);
        }
        return $this->cacheService;
    }

    /**
     *
     * @param $annotations
     * @return array
     */
    private function getAnnotations($annotations) {
        $authorizeAnnotations = Collections::builder($annotations)
            ->filter(Predicates::compute($this->getClassName(), StringUtils::predEquals($this->annotationName)))
            ->get();
        return $authorizeAnnotations;
    }


}