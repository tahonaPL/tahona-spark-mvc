<?php
/**
 * Date: 05.05.18
 * Time: 10:03
 */

namespace Spark\Core\Processor\Handler;


use ReflectionClass;

abstract  class ClassHandler extends Handler {

    abstract public function handleClass(ReflectionClass $classReflection) ;


}