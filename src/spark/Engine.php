<?php

namespace spark;

header('Content-Type: text/html; charset=utf-8');

use ErrorException;
use spark\cache\ApcuBeanCache;
use spark\cache\BeanCache;
use spark\core\Bootstrap;
use spark\core\engine\EngineConfig;
use spark\core\engine\EngineFactory;
use spark\core\error\EngineExceptionWrapper;
use spark\core\error\GlobalErrorHandler;
use spark\filter\FilterChain;
use spark\http\Request;
use spark\http\RequestProvider;
use spark\loader\ClassLoaderRegister;
use spark\routing\RoutingInfo;
use spark\lang\LangMessageResource;
use spark\http\utils\RequestUtils;
use spark\utils\UrlUtils;
use spark\utils\Asserts;
use spark\utils\Collections;
use spark\utils\Objects;
use spark\utils\StringUtils;
use spark\view\json\JsonViewHandler;
use spark\view\plain\PlainViewHandler;
use spark\view\smarty\SmartyPlugins;
use spark\view\smarty\SmartyViewHandler;
use spark\view\ViewHandlerProvider;
use spark\view\ViewModel;
use tahona\Config;


/**
 * Description of Engine
 *
 * @author primosz67
 */
class Engine {
    private static $ROOT_APP_PATH;

    /**
     * @var EngineConfig
     */
    private $engineConfig;

    /**
     * @var Routing
     */
    private $route;

    /**
     * App configuration
     * @var Config
     */
    private $config;

    /**
     * @var Services
     */
    private $services;

    /**
     * @var BeanCache
     */
    private $beanCache;

    public function __construct($params) {
        $this->engineConfig = new EngineConfig($params);
        self::$ROOT_APP_PATH = $this->engineConfig->getRootAppPath();

        ClassLoaderRegister::register($this->engineConfig);

        if ($this->engineConfig->isBeanCacheEnabled()) {
            $this->beanCache = new ApcuBeanCache();
            $this->beanCache->init();
        }

        $errorHandler = new GlobalErrorHandler();
        $errorHandler->setHandler(function ($error) {
            throw $error;
        });
        $errorHandler->setup();
    }

    public static function getRootPath() {
        return self::$ROOT_APP_PATH;
    }

    public function run() {
        $this->runController();
    }

    private function runController() {
        $rootNamespace = $this->engineConfig->getRootNamespace();

        if ($this->hasServicesCached()) {
            $this->services = $this->beanCache->get($this->getCacheKey("services"));
            $this->route = $this->beanCache->get($this->getCacheKey("route"));
            $this->config = $this->beanCache->get($this->getCacheKey("config"));

        } else {

            $configClass = $rootNamespace . "\\Config";
            $this->config = new $configClass;
            $this->config->setMode($this->engineConfig->getConfigName());

            $routeClass = $rootNamespace . "\\Routing";
            $this->route = new $routeClass;

            $servicesClass = $rootNamespace . "\\Services";
            $this->services = new $servicesClass;

            $this->services->setConfig($this->config);
            $this->initServices();

            if ($this->isBeanCacheEnabled()) {
                $this->beanCache->put($this->getCacheKey("config"), $this->config);
                $this->beanCache->put($this->getCacheKey("services"), $this->services);
                $this->beanCache->put($this->getCacheKey("route"), $this->route);
            }
        }

        UrlUtils::setWebPage($this->config->getProperty("web.page"));

        $registeredHostPath = $this->getRegisteredHostPath();
        $urlName = UrlUtils::getPathInfo($registeredHostPath);

        try {
            $this->handleRequest($urlName);
        } catch (\Exception $exception) {
            $this->handleRequestException($exception);
        }
    }

    /**
     * @return mixed
     */
    private function getRegisteredHostPath() {
        return UrlUtils::getHost();
    }

    private function handleRequest($urlName, $responseParams = array()) {
        $this->devToolsInit();

        $registeredHostPath = $this->getRegisteredHostPath();
        $request = $this->route->createRequest($urlName, $this->engineConfig->getNamespaces(), $registeredHostPath);

        //Controller
        $controllerName = $request->getControllerClassName();
        /** @var $controller Controller */
        $controller = new $controllerName();

        //BootStrap
        $bootstrap = EngineFactory::getBootstrap($request, $this->engineConfig);

        $bootstrap->setServices($this->services);
        $bootstrap->setConfig($this->config);
        $bootstrap->setRouting($this->route);

        $bootstrap->setRequest($request);
        $bootstrap->setController($controller);

        /** @var RequestProvider $requestProvider */
        $requestProvider = $this->services->get(RequestProvider::NAME);
        $requestProvider->setRequest($request);

        $this->filter($this->services->getFilters(), $request);
        $bootstrap->init();

        $controller->setServices($this->services);
        $controller->init($request, $responseParams);

        //ACTION->VIEW
        $this->handleAction($responseParams, $request, $controller, $bootstrap);
    }

    private function devToolsInit() {
        $enabled = $this->config->getProperty(Config::DEV_ENABLED);
        if ($enabled) {
            $xdebugEnabled = $this->config->getProperty(Config::DEV_XDEBUG);
            if ($xdebugEnabled) {
                RequestUtils::setCookie("XDEBUG_SESSION", true);
            }
        }
    }

    private function initServices() {
        if ($this->config->hasProperty("messages")) {
            $messagesLocalizationFilePaths = $this->config->getProperty("messages");
        } else {
            $messagesLocalizationFilePaths = array();
        }

        $this->services->clear();
        $this->services->register(LangMessageResource::NAME, new LangMessageResource($messagesLocalizationFilePaths));
        $this->services->register(SmartyPlugins::NAME, new SmartyPlugins());
        $this->services->register(RequestProvider::NAME, new RequestProvider());
        $this->services->register(RoutingInfo::NAME, new RoutingInfo($this->route));
        $this->addViewHandlersToService();

        $this->services->initServices();

    }

    private function addViewHandlersToService() {
        $smartyViewHandler = new SmartyViewHandler($this->engineConfig->getRootAppPath());
        $plainViewHandler = new PlainViewHandler();
        $jsonViewHandler = new JsonViewHandler();

        $provider = new ViewHandlerProvider();
        $provider->setConfig($this->config);
        $provider->setDefaultHandler($smartyViewHandler);
        $provider->setHandlers(array($jsonViewHandler, $plainViewHandler));

        $this->services->register(ViewHandlerProvider::NAME, $provider);
        $this->services->register(SmartyViewHandler::NAME, $smartyViewHandler);
        $this->services->register(PlainViewHandler::NAME, $plainViewHandler);
        $this->services->register(JsonViewHandler::NAME, $jsonViewHandler);
    }


    /**
     * @param $errorArray
     * @param $request Request
     * @param $controller
     * @param $bootstrap Bootstrap
     * @throws \ErrorException
     */
    private function handleAction($errorArray, $request, $controller, $bootstrap) {
        /** @var $viewModel ViewModel */
        $methodName = $request->getMethodName();
        $viewModel = $controller->$methodName();

        Asserts::checkState($viewModel instanceof ViewModel, "Wrong viewModel type. Returned type from controller needs to be instance of ViewModel.");

        if (isset($viewModel)) {
            $bootstrap->setViewModel($viewModel);
            $bootstrap->after();

            $redirect = $viewModel->getRedirect();
            if (StringUtils::isNotBlank($redirect)) {
                $request->instantRedirect($redirect);
            }

            $page = $this->getRegisteredHostPath();

            //Deprecated use e.g.: {path path="/next/page"}
            $viewModel->add("web", array(
                "page" => $page
            ));

            $this->handleView($viewModel, $request);
        } else {
            throw new ErrorException("ViewModel not found. Did you initiated ViewModel? ");
        }
    }

    /**
     * @param ViewModel $viewModel
     * @param Request $request
     */
    private function handleView($viewModel, $request) {
        $handler = $this->services->get(ViewHandlerProvider::NAME);
        /** @var $handler ViewHandlerProvider */
        $handler->handleView($viewModel, $request);
    }

    private function handleRequestException(\Exception $exception) {
        $errorHandling = $this->config->getProperty(Config::ERROR_HANDLING_ENABLED);
        if ($errorHandling) {
            $basePath = $this->route->getBaseErrorPath();
            if (isset($basePath)) {
                $this->handleRequest($basePath, array(
                    "stackTrace" => $exception->getTraceAsString(),
                    "exception" => $exception
                ));
            }
        } else {
            throw new EngineExceptionWrapper("Not handled exception", 0, $exception);
        }
    }

    private function isBeanCacheEnabled() {
        return Objects::isNotNull($this->beanCache);
    }

    private function filter($filters = array(), Request $request) {
        if (Collections::isNotEmpty($filters)) {
            $filtersIterator = new \ArrayIterator($filters);
            $chain = new FilterChain($filtersIterator->current(), $filtersIterator);
            $chain->doFilter($request);
        }
    }

    private function hasServicesCached() {
        return $this->isBeanCacheEnabled() && $this->beanCache->has($this->getCacheKey("services"));
    }

    /**
     * @return string
     */
    private function getCacheKey($key) {
        return "spark_" . $key;
    }
}
