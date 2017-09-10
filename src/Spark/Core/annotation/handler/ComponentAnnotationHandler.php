<?php

namespace Spark\Core\annotation\handler;

use Spark\Cache\Service\CacheableServiceBeanProxy;
use Spark\Cache\Service\CacheService;
use Spark\Common\Optional;
use Spark\Core\annotation\Cache;
use Spark\Core\annotation\handler\AnnotationHandler;
use Spark\Core\library\Annotations;
use Spark\Utils\Collections;
use Spark\Utils\Functions;
use Spark\Utils\Objects;
use Spark\Utils\Predicates;
use Spark\Utils\ReflectionUtils;
use Spark\Utils\StringFunctions;
use Spark\Utils\StringUtils;

/**
 * Created by PhpStorm.
 * User: primosz67
 * Date: 30.01.17
 * Time: 08:57
 */
class ComponentAnnotationHandler extends AnnotationHandler {

    private $annotationNames;

    public function __construct() {
        $this->annotationNames = array(
            "spark\\core\\annotation\\Component",
            "spark\\core\\annotation\\Configuration",
            "spark\\core\\annotation\\Service",
            "spark\\core\\annotation\\Repository"
        );
    }

    public function handleClassAnnotations($annotations = array(), $class, \ReflectionClass $classReflection) {
        $annotation = $this->getAnnotation($annotations, $this->annotationNames);

        if ($annotation->isPresent()) {
            $className = $classReflection->getName();
            $beanName = $this->getBeanName($annotation->get(), $className);

            $this->getContainer()->register($beanName, $this->getCreateBean($class));
        }
    }

    /**
     * @return \Closure
     */
    private function getClassName() {
        return function ($x) {
            return Objects::getClassName($x);
        };
    }

    private function getBeanName($annotation, $class) {
        $isOk = Objects::isNotNull($annotation) && StringUtils::isNotBlank($annotation->name);
        $array = StringUtils::split($class, "\\");
        return $isOk ? $annotation->name : lcfirst(end($array));

    }

    private function getCreateBean($class) {
        $bean = new $class;

        $cacheDefinition = array();
        ReflectionUtils::handleMethodAnnotation($bean, "spark\core\annotation\Cache", function ($bean, $reflectionProperty, $annotation) use (&$cacheDefinition) {
            /** @var Cache $annotation */
            /** @var \ReflectionMethod $reflectionProperty */
            $cacheDefinition[$reflectionProperty->getName()] = $annotation;
        });

        if (Collections::isNotEmpty($cacheDefinition)) {
            return new CacheableServiceBeanProxy($bean);
        } else {
            return $bean;
        }

    }

    /**
     * @param $annotations
     * @param $defined
     * @return \Spark\Common\Optional
     */
    private function getAnnotation($annotations, $defined) {
        return Collections::builder($annotations)
            ->filter(Predicates::compute($this->getClassName(), Predicates::contains($defined)))
            ->findFirst();
    }

}