<?php

namespace spark\cache;

/**
 * Created by PhpStorm.
 * User: primosz67
 * Date: 18.10.16
 * Time: 22:12
 */
interface BeanCache {


    public function init();

    public function put($key, $object);

    public function get($key);
    public function has($key);

}