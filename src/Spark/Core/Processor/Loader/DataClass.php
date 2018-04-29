<?php


namespace context;


class DataLoader {

    private $container;
    private $route;
    private $config;
    private $exceptionResolvers;

    public function __construct() {
        $this->container = unserialize("{123_CONTAINER}");
        $this->route = unserialize("{123_ROUTE}");
        $this->config = unserialize("{123_CONFIG}");
        $this->exceptionResolvers = unserialize("{123_EXCEPTIONS}");
    }


    public function getContainer() {
        return $this->container;
    }

    public function getRoute() {
        return $this->route;
    }

    public function getConfig() {
        return $this->config;
    }

    public function getExceptionResolvers() {
        return $this->exceptionResolvers;
    }
}