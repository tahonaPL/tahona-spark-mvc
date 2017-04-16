<?php

namespace spark\core;

use spark\Config;
use spark\Container;
use spark\Controller;
use spark\http\Request;
use spark\Routing;
use spark\view\ViewModel;

abstract class Bootstrap {

    /**
     *
     * @var Controller
     */
    private $controller;

    /**
     *
     * @var Container
     */
    private $container;

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
        return $this->container->get($name);
    }

    public function setContainer(Container $container) {
        $this->container = $container;
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
     * @return Container
     */
    protected final function getContainer() {
        return $this->container;
    }
}
