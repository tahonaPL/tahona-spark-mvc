<?php

namespace spark\core\annotation\handler;

use spark\Config;
use spark\core\annotation\handler\AnnotationHandler;
use spark\utils\Collections;
use spark\utils\Functions;
use spark\utils\Objects;
use spark\utils\Predicates;
use spark\utils\StringUtils;

/**
 * Created by PhpStorm.
 * User: primosz67
 * Date: 30.01.17
 * Time: 08:57
 */
class EnableApcuAnnotationHandler extends AnnotationHandler {

    const APCU_CACHE_ENABLED = "spark.apcu.cache.enabled";
    private $annotationName;

    public function __construct() {
        $this->annotationName = "spark\\core\\annotation\\EnableApcuBeanCache";
    }

    public function handleClassAnnotations($annotations = array(), $bean, \ReflectionClass $classReflection) {

        $annotation = Collections::builder($annotations)
            ->findFirst(Predicates::compute($this->getClassName(), StringUtils::predEquals($this->annotationName)));


        if ($annotation->isPresent()) {
            $this->getConfig()->set(self::APCU_CACHE_ENABLED, true);
        }
    }

    private function getClassName() {
        return Functions::getClassName();
    }

}