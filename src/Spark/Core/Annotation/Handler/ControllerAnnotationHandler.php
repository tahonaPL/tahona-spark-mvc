<?php

namespace Spark\Core\Annotation\Handler;

use Spark\Cache\Service\CacheableServiceBeanProxy;
use Spark\Cache\Service\CacheService;
use Spark\Common\Optional;
use Spark\Cache\Annotation\Cache;
use Spark\Core\Annotation\Handler\AnnotationHandler;
use Spark\Core\Library\Annotations;
use Spark\Utils\Collections;
use Spark\Utils\Functions;
use Spark\Utils\Objects;
use Spark\Utils\Predicates;
use Spark\Utils\ReflectionUtils;
use Spark\Utils\StringFunctions;
use Spark\Utils\StringUtils;


class ControllerAnnotationHandler extends AnnotationHandler {

    private $annotationNames;

    public function __construct() {
        $this->annotationNames = array(
            Annotations::CONTROLLER,
            Annotations::REST_CONTROLLER
        );
    }

    public function handleClassAnnotations($annotations = array(), $class, \ReflectionClass $classReflection) {
        $annotation = AnnotationHandlerHelper::getAnnotation($annotations, $this->annotationNames);

        if ($annotation->isPresent()) {
            $className = $classReflection->getName();
            $this->getContainer()->registerClass($className, $class);
        }
    }

}