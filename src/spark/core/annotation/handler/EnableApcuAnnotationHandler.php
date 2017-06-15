<?php

namespace spark\core\annotation\handler;

use spark\Config;
use spark\core\annotation\EnableApcuBeanCache;
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
    const APCU_CACHE_PREFIX = "spark.apcu.cache.prefix";
    const APCU_CACHE_RESET = "spark.apcu.cache.reset";

    private $annotationName;

    public function __construct() {
        $this->annotationName = "spark\\core\\annotation\\EnableApcuBeanCache";
    }

    public function handleClassAnnotations($annotations = array(), $class, \ReflectionClass $classReflection) {

        $annotation = Collections::builder($annotations)
            ->findFirst(Predicates::compute($this->getClassName(), StringUtils::predEquals($this->annotationName)));

        if ($annotation->isPresent()) {
            /** @var EnableApcuBeanCache $param */
            $param = $annotation->getOrNull();

            $config = $this->getConfig();
            $config->set(self::APCU_CACHE_ENABLED, true);
            $config->set(self::APCU_CACHE_PREFIX, $param->prefix);
            $config->set(self::APCU_CACHE_RESET, $param->resetParam);
        }
    }

    private function getClassName() {
        return Functions::getClassName();
    }

}