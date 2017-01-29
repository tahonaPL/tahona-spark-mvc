<?php


namespace  spark\cache;

/**
 * Created by PhpStorm.
 * User: primosz67
 * Date: 18.10.16
 * Time: 22:38
 */
class ApcuBeanCache implements BeanCache{

    public function init() {
        // TODO: Implement init() method.
    }

    public function put($key, $object) {
        apcu_store($key, $object);
    }

    public function get($key) {
        return apcu_fetch($key);
    }

    public function has($key) {
        return apcu_exists($key);
    }
}