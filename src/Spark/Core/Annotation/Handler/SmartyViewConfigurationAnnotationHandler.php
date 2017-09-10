<?php

namespace Spark\Core\Annotation\Handler;

use Spark\Config;
use Spark\Core\Annotation\Handler\AnnotationHandler;
use Spark\Core\Annotation\SmartyViewConfiguration;
use Spark\Utils\Collections;
use Spark\Utils\Functions;
use Spark\Utils\Objects;
use Spark\Utils\Predicates;
use Spark\Utils\StringUtils;
use Spark\View\Smarty\SmartyViewHandler;


class SmartyViewConfigurationAnnotationHandler extends AnnotationHandler {

    private $annotationName;

    public function __construct() {
        $this->annotationName = "Spark\\core\\annotation\\SmartyViewConfiguration";
    }

    public function handleClassAnnotations($annotations = array(), $class, \ReflectionClass $classReflection) {
        $annotation = Collections::builder($annotations)
            ->findFirst(Predicates::compute($this->getClassName(), Predicates::equals($this->annotationName)));

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