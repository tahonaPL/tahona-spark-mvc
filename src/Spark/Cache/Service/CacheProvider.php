<?php
/**
 * Created by PhpStorm.
 * User: crownclown67
 * Date: 10.06.17
 * Time: 01:52
 */

namespace Spark\Cache\Service;


use Spark\Cache\Cache;
use Spark\Container;
use Spark\Core\annotation\Inject;
use Spark\Core\annotation\PostConstruct;
use Spark\Core\provider\BeanProvider;

class CacheProvider {

    private $caches;

    /**
     * @Inject()
     * @var BeanProvider
     */
    private $beanProvider;

    /**
     * @PostConstruct()
     */
    public function init() {
        $this->caches = $this->beanProvider->getByType(Cache::class);
        $this->beanProvider = null;
    }

    /**
     * @param $name
     * @return Cache
     */
    public function getCache($name) {
        return $this->caches[$name];
    }


}