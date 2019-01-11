<?php
/**
 * Date: 10.01.19
 * Time: 20:03
 */

namespace Spark\Core\Processor\Loader;


use Spark\Config;
use Spark\Core\Error\GlobalErrorHandler;
use Spark\Http\RequestProvider;
use Spark\Routing;

class Context {

    private $config;
    private $route;
    private $httpFilters;
    private $interceptors;
    private $controller;
    private $exceptionResolvers;
    private $globalErrorHandler;
    private $commands;
    private $langResources;
    private $langResourcePaths;
    private $sessionProvider;
    private $viewHandlers;
    private $fillers;
    private $requestProvider;

    public function __construct($config, $route, $httpFilters, $interceptors,
                                $controller, $exceptionResolvers, $globalErrorHandler, $commands,
                                $langResources, $langResourcePaths,
                                $sessionProvider,
                                $viewHandlers,
                                $fillers,
                                $requestProvider) {
        $this->config = $config;
        $this->route = $route;
        $this->httpFilters = $httpFilters;
        $this->interceptors = $interceptors;
        $this->controller = $controller;
        $this->exceptionResolvers = $exceptionResolvers;
        $this->globalErrorHandler = $globalErrorHandler;
        $this->commands = $commands;
        $this->langResources = $langResources;
        $this->langResourcePaths = $langResourcePaths;
        $this->sessionProvider = $sessionProvider;
        $this->viewHandlers = $viewHandlers;
        $this->fillers = $fillers;
        $this->requestProvider = $requestProvider;
    }


    public function getConfig(): Config {
        return $this->config;
    }

    public function getRoute(): Routing {
        return $this->route;
    }

    public function getHttpFilters(): array {
        return $this->httpFilters;
    }

    public function getInterceptors(): array {
        return $this->interceptors;
    }

    public function getController() {
        return $this->controller;
    }

    public function getExceptionResolvers(): array {
        return $this->exceptionResolvers;
    }

    public function getGlobalErrorHandler(): GlobalErrorHandler {
        return $this->globalErrorHandler;
    }

    public function getCommands() {
        return $this->commands;
    }

    public function getLangResources() {
        return $this->langResources;
    }

    public function getLangResourcePaths() {
        return $this->langResourcePaths;
    }

    public function getSessionProvider() {
        return $this->sessionProvider;
    }

    public function getViewHandlers() {
        return $this->viewHandlers;
    }

    public function getFillers() {
        return $this->fillers;
    }

    public function getRequestProvider() : RequestProvider {
        return $this->requestProvider;
    }

}