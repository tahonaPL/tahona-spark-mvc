<?php

namespace spark\core\annotation\handler;

use spark\cache\service\CacheableServiceBeanProxy;
use spark\cache\service\CacheService;
use spark\common\Optional;
use spark\core\annotation\Cache;
use spark\core\annotation\handler\AnnotationHandler;
use spark\core\library\Annotations;
use spark\utils\Collections;
use spark\utils\Functions;
use spark\utils\Objects;
use spark\utils\Predicates;
use spark\utils\ReflectionUtils;
use spark\utils\StringFunctions;
use spark\utils\StringUtils;

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
     * @return \spark\common\Optional
     */
    private function getAnnotation($annotations, $defined) {
        return Collections::builder($annotations)
            ->filter(Predicates::compute($this->getClassName(), Predicates::contains($defined)))
            ->findFirst();
    }

}