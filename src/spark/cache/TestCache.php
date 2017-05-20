<?php
/**
 * Created by PhpStorm.
 * User: crownclown67
 * Date: 20.05.17
 * Time: 01:45
 */

namespace spark\cache;


class TestCache implements BeanCache{

    public function init() {

    }

    public function put($key, $val) {
        $val = serialize($val);
        // HHVM fails at __set_state, so just use object cast for now
//        $val = str_replace('stdClass::__set_state', '(object)', $val);
        // Write to temp file first to ensure atomicity
        $tmp = "/tmp/$key." . uniqid('', true) . '.tmp';
        file_put_contents($tmp, $val, LOCK_EX);
        rename($tmp, "/tmp/$key");
    }

    public function get($key) {
        return unserialize(file_get_contents("/tmp/$key"));
    }

    public function has($key) {
        return file_exists("/tmp/$key");
    }

    public function clearAll() {

    }
}