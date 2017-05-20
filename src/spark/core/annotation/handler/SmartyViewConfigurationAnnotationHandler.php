<?php

namespace spark\core\annotation\handler;

use spark\Config;
use spark\core\annotation\handler\AnnotationHandler;
use spark\core\annotation\SmartyViewConfiguration;
use spark\utils\Collections;
use spark\utils\Functions;
use spark\utils\Objects;
use spark\utils\Predicates;
use spark\utils\StringUtils;
use spark\view\smarty\SmartyViewHandler;


class SmartyViewConfigurationAnnotationHandler extends AnnotationHandler {

    private $annotationName;

    public function __construct() {
        $this->annotationName = "spark\\core\\annotation\\SmartyViewConfiguration";
    }

    public function handleClassAnnotations($annotations = array(), $class, \ReflectionClass $classReflection) {
        $annotation = Collections::builder($annotations)
            ->findFirst(Predicates::compute($this->getClassName(), StringUtils::predEquals($this->annotationName)));

        if ($annotation->isPresent()) {
            /** @var SmartyViewConfiguration $annotationValue */
            $annotationValue = $annotation->get();

            $config = $this->getConfig();

            $config->set(SmartyViewHandler::CACHE_ID, $annotationValue->cacheId);
            $config->set(SmartyViewHandler::FORCE_COMPILE, $annotationValue->forceCompile);
            $config->set(SmartyViewHandler::COMPILE_CHECK, $annotationValue->compileCheck);
            $config->set(SmartyViewHandler::CACHE_ENABLED, $annotationValue->caching);
            $config->set(SmartyViewHandler::CACHE_LIFE_TIME, $annotationValue->cacheLifetime);
            $config->set(SmartyViewHandler::ERROR_REPORTING, $annotationValue->errorReporting);
            $config->set(SmartyViewHandler::DEBUGGING, $annotationValue->debugging);
        }
    }

    private function getClassName() {
        return Functions::getClassName();
    }

}