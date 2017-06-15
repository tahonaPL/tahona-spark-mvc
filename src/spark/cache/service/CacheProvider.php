<?php
/**
 * Created by PhpStorm.
 * User: crownclown67
 * Date: 10.06.17
 * Time: 01:52
 */

namespace spark\cache\service;


use spark\cache\Cache;
use spark\Container;
use spark\core\annotation\Inject;
use spark\core\annotation\PostConstruct;
use spark\core\provider\BeanProvider;

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