<?php

namespace spark\core\module;


use spark\Services;

interface LoadModule {
    /**
     * @param $services Services
     * @return mixed
     */
    public function load($services);
}