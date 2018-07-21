<?php

namespace Spark\Http;

interface Session {

    public function add($key, $value): Session;

    public function addAll(array $array): Session;

    public function getParams(): array;

    public function has($key): bool;

    public function get($key);

    public function remove($key): Session;

}
