<?php


use spark\utils\Asserts;

class MemCacheAdapter implements BeanCache {


    /**
     * @var Memcache
     */
    private $cache;

    public function __construct() {
        $this->cache = new Memcached();
    }

    public function init() {
        $success = $this->cache->connect("127.0.0.1", "11211");
        Asserts::checkState($success, "Can't connect to memcached server");
    }

    public function put($key, $object) {
        $this->cache->set($key, $object);
    }

    public function get($key) {
        return $this->cache->get($key);
    }
}