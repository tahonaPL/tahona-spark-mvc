<?php

namespace spark\core\service;

use spark\Services;

class ServiceHelper {

    /**
     *
     * @var Services 
     */
    private $services;

    /**
     *  Method for bean locating and getting from Service resources.
     *
     * use:
     *
     * Services->register("UserService", new UserService());
     * $this->get("UserService");
     *
     *
     * @param $name
     * @return mixed
     */
    protected function get($name) {
        return $this->services->get($name);
    }

    public function setServices(Services $services) {
        $this->services = $services;
    }

}
