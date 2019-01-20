<?php
/**
 * Date: 01.11.18
 * Time: 18:08
 */

namespace Spark\Core\Definition;


class SimpleBeanFactory implements BeanFactory {

    public function createNewBean(string $class): object {
        return new $class;
    }
}