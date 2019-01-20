<?php
/**
 * Date: 01.11.18
 * Time: 18:13
 */

namespace Spark\Cache\Factory;


use Spark\Cache\Service\CacheableServiceBeanProxy;
use Spark\Core\Definition\BeanFactory;

class CachedBeanFactory implements BeanFactory {

    public function createNewBean(string $class): object {
        return new CacheableServiceBeanProxy(new $class);
    }
}