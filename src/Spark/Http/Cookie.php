<?php
/**
 * Created by PhpStorm.
 * User: crownclown67
 * Date: 2018-03-17
 * Time: 14:21
 */

namespace Spark\Http;

interface Cookie {

    public function set($key, $value): Cookie;

    public function setAll(array $array): Cookie;

    public function getParams(): array;

    public function has($key): bool;

    public function get($key);


    public function remove($key): Cookie;
}