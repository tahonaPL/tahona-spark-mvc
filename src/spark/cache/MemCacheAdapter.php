<?php


use spark\cache\BeanCache;
use spark\utils\Asserts;

class MemCacheAdapter implements BeanCache {

    /**
     * @var Memcache
     */
    private $cache;

    public function __construct($host, $port) {
        $this->cache = new Memcached();
        $success = $this->cache->connect("127.0.0.1", "11211");

        Asserts::checkState($success, "Can't connect to memcached server");
    }

    public function put($key, $object) {
        $this->cache->set($key, $object);
    }

    public function get($key) {
        return $this->cache->get($key);
    }


    public function clearAll() {
        return $this->cache->flush();
    }
}