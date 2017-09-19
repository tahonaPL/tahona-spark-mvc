<?php


namespace Spark\Cache;
use Spark\Common\IllegalArgumentException;

/**
 *
 *
 * Date: 18.10.16
 * Time: 22:38
 */
class ApcuBeanCache implements BeanCache {

    public function put($key, $object) {
        try{
            apcu_store($key, $object);
        }catch(\Exception $e){
            throw new IllegalArgumentException("Error when serializing $key", $e);
        }
    }

    public function get($key) {
        return apcu_fetch($key);
    }

    public function has($key) {
        return apcu_exists($key);
    }

    public function clearAll() {
        apcu_clear_cache();
    }
}