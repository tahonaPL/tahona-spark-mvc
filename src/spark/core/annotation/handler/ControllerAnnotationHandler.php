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

            $this->getContainer()->register($className, $this->getCreateBean($class));
        }
    }

    private function getCreateBean($class) {
        $bean = new $class;

        if (Annotations::hasCacheAnnotations($class)) {
            return new CacheableServiceBeanProxy($bean);
        } else {
            return new $bean;
        }

    }


}