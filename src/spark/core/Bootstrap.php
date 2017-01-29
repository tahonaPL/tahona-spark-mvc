<?php

namespace spark\core;

use Doctrine\ORM\EntityManager;
use spark\Config;
use spark\Controller;
use spark\http\Request;
use spark\Routing;
use spark\Services;
use spark\utils\Objects;
use spark\view\ViewModel;

abstract class Bootstrap {

    /**
     *
     * @var Controller
     */
    private $controller;

    /**
     *
     * @var \spark\Services
     */
    private $services;

    /**
     *
     * @var Request
     */
    private $request;

    /**
     *
     * @var Config
     */
    private $config;

    /**
     *
     * @var Routing
     */
    private $routing;

    /**
     * @var ViewModel
     */
    private $viewModel;

    public function init() {
        //hook
    }

    public function getController() {
        return $this->controller;
    }

    public function setController(Controller $controller) {
        $this->controller = $controller;
    }

    public function getRequest() {
        return $this->request;
    }

    public function setRequest(Request $request) {
        $this->request = $request;
    }

    public function getConfig() {
        return $this->config;
    }

    public function setConfig(Config $config) {
        $this->config = $config;
    }

    public function getRouting() {
        return $this->routing;
    }

    public function setRouting(Routing $routing) {
        $this->routing = $routing;
    }

    public function get($name) {
        return $this->services->get($name);
    }

    public function setServices(\spark\Services $services) {
        $this->services = $services;
    }

    /**
     * Setted after method in controller invoked
     * @param $viewModel
     */
    public function setViewModel($viewModel) {
        $this->viewModel = $viewModel;
    }

    /**
     * @return \spark\view\ViewModel
     */
    protected function getViewModel() {
        return $this->viewModel;
    }

    /**
     * hook for some action like transaction commit
     */
    public function after() {
    }

    /**
     * To add new beans in new module
     *
     * @return \spark\Services
     */
    protected final function getServices() {
        return $this->services;
    }
}
