<?php
/**
 * Date: 01.11.18
 * Time: 18:04
 */

namespace Spark\Core\Definition;


interface BeanFactory {

    public function createNewBean(string $class) : object ;

}