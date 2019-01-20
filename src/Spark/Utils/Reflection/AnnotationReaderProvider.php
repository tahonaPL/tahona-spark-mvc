<?php
/**
 * Date: 11.01.19
 * Time: 00:12
 */

namespace Spark\Utils\Reflection;


use Doctrine\Common\Annotations\AnnotationReader;
use Spark\Utils\ReflectionUtils;

class AnnotationReaderProvider {
    public function get(): AnnotationReader {
        return ReflectionUtils::getReaderInstance();
    }

}