<?php

namespace Spark\Core\Processor\Loader;


use Spark\Cache\Cache;
use Spark\Utils\Objects;

class CacheContextLoader implements ContextLoader {

    const CONTAINER_CACHE_KEY = 'container';
    const ROUTE_CACHE_KEY = 'route';
    const CONFIG_CACHE_KEY = 'config';
    const ERROR_HANDLERS_CACHE_KEY = 'exceptionResolvers';

    private $beanCache;
    private $hasAllreadyCachedData;
    /**
     * @var
     */
    private $appName;

    public function __construct($appName, Cache $cache) {
        $this->beanCache = $cache;
        $this->appName = $appName;
    }


    public function hasData() : bool {
        if (Objects::isNull($this->hasAllreadyCachedData)) {
            $container = $this->beanCache->get($this->getCacheKey(self::CONTAINER_CACHE_KEY));
            $this->hasAllreadyCachedData = Objects::isNotNull($container);
        }
        return $this->hasAllreadyCachedData;
    }

    public function getContainer() {
        return $this->beanCache->get($this->getCacheKey(self::CONTAINER_CACHE_KEY));
    }

    public function getRoute() {
        return $this->beanCache->get($this->getCacheKey(self::ROUTE_CACHE_KEY));
    }

    public function getConfig() {
        return $this->beanCache->get($this->getCacheKey(self::CONFIG_CACHE_KEY));
    }

    public function getExceptionResolvers() {
        return $this->beanCache->get($this->getCacheKey(self::ERROR_HANDLERS_CACHE_KEY));
    }

    public function clear() {
        $this->beanCache->clearAll();
        $this->hasAllreadyCachedData = false;
    }

    public function save($config, $container, $route, $exceptionResolvers) {
        $this->beanCache->put($this->getCacheKey(self::CONFIG_CACHE_KEY), $config);
        $this->beanCache->put($this->getCacheKey(self::CONTAINER_CACHE_KEY), $container);
        $this->beanCache->put($this->getCacheKey(self::ROUTE_CACHE_KEY), $route);
        $this->beanCache->put($this->getCacheKey(self::ERROR_HANDLERS_CACHE_KEY), $exceptionResolvers);
    }

    private function getCacheKey($key): string {
        return $this->appName . '_' . $key;
    }

}