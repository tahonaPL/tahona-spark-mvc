<?php
/**
 * Date: 15.01.19
 * Time: 07:52
 */

namespace Spark\Core\Processor\Cycle;

interface BeanPostProcess {
    public function afterInit():void;
}