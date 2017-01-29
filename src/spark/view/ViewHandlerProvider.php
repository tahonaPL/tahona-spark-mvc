<?php
/**
 * Created by PhpStorm.
 * User: primosz67
 * Date: 06.07.14
 * Time: 17:42
 */

namespace spark\view;


use spark\common\IllegalArgumentException;
use spark\Config;
use spark\utils\Objects;
use spark\view\ViewModel;

class ViewHandlerProvider {

    const NAME = "viewHandlerProvider";

    /**
     * @var Config
     */
    private $config;

    private $handlers = array();

    /**
     * @var ViewHandler
     */
    private $defaultHandler;

    /**
     * @param \spark\view\ViewHandler $defaultHandler
     */
    public function setDefaultHandler($defaultHandler) {
        $this->defaultHandler = $defaultHandler;
    }

    /**
     * @param mixed $handlers
     */
    public function setHandlers($handlers) {
        $this->handlers = $handlers;
    }

    public function setConfig($config) {
        $this->config = $config;
    }

    public function handleView($viewModel, $request) {
        /** @var $viewHandler ViewHandler */
        $viewHandler = $this->getProvider($viewModel, $request);
        if (Objects::isNull($viewHandler)) {
            $viewHandler = $this->defaultHandler;
        }

        $viewHandler->setViewHandlerProvider($this);
        $viewHandler->handleView($viewModel, $request);
    }

    private function getProvider(ViewModel $viewModel, $request) {
        /** @var $handler ViewHandler */
        foreach ($this->handlers as $handler) {
            if ($handler->isView($viewModel)) {
                return $handler;
            }
        }
        return null;
    }

    public function getConfig() {
        return $this->config;
    }

    public function addHandler(ViewHandler $handler) {
        $this->handlers[] = $handler;
    }


}