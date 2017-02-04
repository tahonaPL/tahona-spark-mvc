<?php

namespace spark\core\service;

use spark\Container;

class ServiceHelper {

    /**
     *
     * @var Container
     */
    private $container;

    /**
     *  Method for bean locating and getting from Service resources.
     *
     * use:
     *
     * Container->register("UserService", new UserService());
     * $this->get("UserService");
     *
     *
     * @param $name
     * @return mixed
     */
    protected function get($name) {
        return $this->container->get($name);
    }

    public function setContainer(Container $container) {
        $this->container = $container;
    }

}
