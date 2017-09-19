<?php

namespace Spark\Core\Annotation\Handler;

use Spark\Cache\Service\CacheableServiceBeanProxy;
use Spark\Cache\Service\CacheService;
use Spark\Common\Optional;
use Spark\Core\Annotation\Cache;
use Spark\Core\Annotation\Handler\AnnotationHandler;
use Spark\Core\Definition\BeanConstructorFactory;
use Spark\Core\Library\Annotations;
use Spark\Utils\Collections;
use Spark\Utils\Functions;
use Spark\Utils\Objects;
use Spark\Utils\Predicates;
use Spark\Utils\ReflectionUtils;
use Spark\Utils\StringFunctions;
use Spark\Utils\StringUtils;

/**
 *
 *
 * Date: 30.01.17
 * Time: 08:57
 */
class ComponentAnnotationHandler extends AnnotationHandler {

    private $annotationNames;

    public function __construct() {
        $this->annotationNames = array(
            Annotations::COMPONENT,
            Annotations::CONFIGURATION,
            Annotations::SERVICE,
            Annotations::REPOSITORY
        );
    }

    public function handleClassAnnotations($annotations = array(), $class, \ReflectionClass $classReflection) {
        $annotation = $this->getAnnotation($annotations, $this->annotationNames);

        if ($annotation->isPresent()) {
            $className = $classReflection->getName();
            $beanName = $this->getBeanName($annotation->get(), $className);

            $this->addBean($class, $beanName);
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


    /**
     * @param $annotations
     * @param $defined
     * @return \Spark\Common\Optional
     */
    private function getAnnotation($annotations, $defined) {
        return Collections::builder($annotations)
            ->filter(Predicates::compute($this->getClassName(), Predicates::in($defined)))
            ->findFirst();
    }

    /**
     * @param $class
     * @param $beanName
     */
    private function addBean($class, $beanName) {
        $this->getContainer()->registerClass($beanName, $class);
    }

}