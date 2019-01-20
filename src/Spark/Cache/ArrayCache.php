<?php


namespace Spark\Cache;

use Spark\Common\IllegalArgumentException;
use Spark\Utils\Collections;

/**
 *
 *
 * Date: 18.10.16
 * Time: 22:38
 */
class ArrayCache implements Cache {

    private $cache = [];

    private $time = 0;

    /**
     * @param time - in seconds
     */
    public function __construct($time = 0) {
        $this->time = $time;
    }

    public function put($key, $object) {
        $obj = ['value' => $object];
        if ($this->time > 0) {
            $obj['time'] = $this->time + time();
        }
        $this->cache[$key] = $obj;
    }

    public function get($key) {
        if ($this->has($key)) {
            $value = Collections::getValue($this->cache, $key);
            return $value['value'];
        }

        return null;
    }

    public function has($key) {
        return Collections::hasKey($this->cache, $key)
            && $this->isTimeValid($this->cache[$key]);
    }

    /**
     * @param $key
     * @return bool
     */
    private function isTimeValid($arrayObj): bool {
        return !Collections::hasKey($arrayObj, 'time')
            || time() < $arrayObj['time'];
    }


}