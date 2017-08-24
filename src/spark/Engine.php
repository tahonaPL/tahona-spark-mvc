<?php
declare(strict_types = 1);

namespace spark;

use ErrorException;
use spark\cache\ApcuBeanCache;
use spark\cache\BeanCache;
use spark\cache\service\CacheProvider;
use spark\cache\service\CacheService;
use spark\core\annotation\handler\EnableApcuAnnotationHandler;
use spark\core\command\Command;
use spark\core\command\input\InputInterface;
use spark\core\command\output\OutputInterface;
use spark\core\error\ExceptionResolver;
use spark\core\error\GlobalErrorHandler;
use spark\core\interceptor\HandlerInterceptor;
use spark\core\lang\CookieLangKeyProvider;
use spark\core\lang\LangKeyProvider;
use spark\core\lang\LangMessageResource;
use spark\core\lang\LangResourcePath;
use spark\core\library\BeanLoader;
use spark\core\processor\InitAnnotationProcessors;
use spark\core\provider\BeanProvider;
use spark\core\utils\SystemUtils;
use spark\filter\FilterChain;
use spark\filter\HttpFilter;
use spark\http\Request;
use spark\http\RequestProvider;
use spark\http\Response;
use spark\http\utils\RequestUtils;
use spark\routing\RoutingInfo;
use spark\utils\Asserts;
use spark\utils\BooleanUtils;
use spark\utils\Collections;
use spark\utils\Functions;
use spark\utils\Objects;
use spark\utils\Predicates;
use spark\utils\StringUtils;
use spark\utils\UrlUtils;
use spark\view\json\JsonViewHandler;
use spark\view\plain\PlainViewHandler;
use spark\view\redirect\RedirectViewHandler;
use spark\view\smarty\SmartyPlugins;
use spark\view\smarty\SmartyViewHandler;
use spark\view\ViewHandlerProvider;
use spark\view\ViewModel;

class Engine {

    private static $rootAppPath;

    const CONTAINER_CACHE_KEY      = "container";
    const ROUTE_CACHE_KEY          = "route";
    const CONFIG_CACHE_KEY         = "config";
    const INTERCEPTORS_CACHE_KEY   = "interceptors";
    const ERROR_HANDLERS_CACHE_KEY = "exceptionResolvers";

    /**
     * @var string Application Name used in apcu Cache as a prefix
     */
    private $appName;
    private $apcuExtensionLoaded;

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
     * @var Container
     */
    private $container;

    /**
     * @var BeanCache
     */
    private $beanCache;

    private $interceptors;
    private $exceptionResolvers;
    private $profile;

    private $hasAllreadyCachedData;

    public function __construct($appName, $profile, $rootAppPath) {
        $this->appName = $appName;
        $this->profile = $profile;
        $this->apcuExtensionLoaded = extension_loaded("apcu");

        Asserts::notNull($rootAppPath, "Engine configuration: did you forget root project path('s) field: 'root' e.g 'path'");
        self::$rootAppPath = $rootAppPath;

        $this->beanCache = new ApcuBeanCache();

        $container = $this->beanCache->get($this->getCacheKey(self::CONTAINER_CACHE_KEY));
        $this->hasAllreadyCachedData = $this->apcuExtensionLoaded && $container;

        if ($this->hasAllreadyCachedData) {
            $this->container = $container;
            $this->route = $this->beanCache->get($this->getCacheKey(self::ROUTE_CACHE_KEY));
            $this->config = $this->beanCache->get($this->getCacheKey(self::CONFIG_CACHE_KEY));
            $this->interceptors = $this->beanCache->get($this->getCacheKey(self::INTERCEPTORS_CACHE_KEY));
            $this->exceptionResolvers = $this->beanCache->get($this->getCacheKey(self::ERROR_HANDLERS_CACHE_KEY));

            $this->clearCacheIfResetParam();
        }

        if (!$this->hasAllreadyCachedData) {
            if ($this->apcuExtensionLoaded) {
                $this->beanCache->clearAll();
            }

            $src = $rootAppPath . "/src";

            $this->container = new Container();
            $this->route = new Routing(array());
            $this->config = new Config();

            $this->config->set("app.profile", $this->getProfile());
            $this->config->set("app.path", $rootAppPath);
            $this->config->set("src.path", $rootAppPath . "/src");

            $initAnnotationProcessors = new InitAnnotationProcessors($this->route, $this->config, $this->container);

            $beanLoader = new BeanLoader($initAnnotationProcessors, $this->config, $this->container);
            $beanLoader->addFromPath($src, array("proxy"));
            $beanLoader->addClass("spark\core\CoreConfig");
            $beanLoader->process();

            $this->addBaseServices();

            $this->container->registerObj($this->container);
            $this->container->setConfig($this->config);
            $this->container->initServices();
            $beanLoader->postProcess();

            $this->afterAllBean();

            $this->interceptors = $this->container->getByType(HandlerInterceptor::CLASS_NAME);
            $this->exceptionResolvers = $this->container->getByType(ExceptionResolver::CLASS_NAME);

            if ($this->isApcuCacheEnabled()) {
                $this->beanCache->put($this->getCacheKey(self::CONFIG_CACHE_KEY), $this->config);
                $this->beanCache->put($this->getCacheKey(self::CONTAINER_CACHE_KEY), $this->container);
                $this->beanCache->put($this->getCacheKey(self::ROUTE_CACHE_KEY), $this->route);
                $this->beanCache->put($this->getCacheKey(self::INTERCEPTORS_CACHE_KEY), $this->interceptors);
                $this->beanCache->put($this->getCacheKey(self::ERROR_HANDLERS_CACHE_KEY), $this->exceptionResolvers);
            }
        }

        /** @var GlobalErrorHandler $globalErrorHandler */
        $globalErrorHandler = $this->container->get(GlobalErrorHandler::NAME);
        $globalErrorHandler->setup($this->exceptionResolvers);
    }

    /**
     * use $this->config->getProperty("app.path")
     *
     * @deprecated
     * @return mixed
     */
    public static function getRootPath() {
        return self::$rootAppPath;
    }

    public function run() {
        if (SystemUtils::isCommandLineInterface()) {
            $this->runCommand();
        } else {
            $this->runController();
        }
    }

    private function runController() {
        $registeredHostPath = $this->getRegisteredHostPath();
        $urlName = UrlUtils::getPathInfo($registeredHostPath);

        $this->handleRequest($urlName);
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
        $request = $this->route->createRequest($urlName, $registeredHostPath);

        $this->updateRequest($request);

        //Interceptor
        $this->preHandleInterceptor($request);
        $this->filter($request);

        //Controller
        $controllerName = $request->getControllerClassName();

        /** @var $controller Controller */
        $controller = $this->container->get($controllerName);
        $controller->init($request, $responseParams);
        $controller->setContainer($this->container);

        //ACTION->VIEW
        $this->handleAction($request, $controller);
    }

    private function devToolsInit() {
        $enabled = $this->config->getProperty(Config::DEV_ENABLED);
        if ($enabled) {
            RequestUtils::setCookie("XDEBUG_SESSION", true);
        }
    }

    private function addBaseServices() {
        $this->container->register("cache", $this->beanCache);
        $this->container->registerObj(new CacheProvider());
        $this->container->registerObj(new CacheService());
        $this->container->register(LangMessageResource::NAME, new LangMessageResource(array()));
        $this->container->register(LangKeyProvider::NAME, new CookieLangKeyProvider("lang"));

        $this->container->register(SmartyPlugins::NAME, new SmartyPlugins());
        $this->container->register(RequestProvider::NAME, new RequestProvider());
        $this->container->register(RoutingInfo::NAME, new RoutingInfo($this->route));
        $this->container->registerObj(new BeanProvider($this->container));
        $this->container->registerObj($this->config);
        $this->container->registerObj($this->route);
        $this->container->registerObj(new GlobalErrorHandler($this));

        $this->addViewHandlersToService();
    }

    private function afterAllBean() {
        $resourcePaths = $this->container->getByType(LangResourcePath::CLASS_NAME);

        /** @var LangMessageResource $resource */
        $resource = $this->container->get(LangMessageResource::NAME);
        $resource->addResources($resourcePaths);
    }

    private function addViewHandlersToService() {
        $smartyViewHandler = new SmartyViewHandler(self::$rootAppPath);
        $plainViewHandler = new PlainViewHandler();
        $jsonViewHandler = new JsonViewHandler();
        $redirectViewHandler = new RedirectViewHandler();

        $provider = new ViewHandlerProvider();
        $this->container->register(ViewHandlerProvider::NAME, $provider);
        $this->container->register("defaultViewHandler", $smartyViewHandler);
        $this->container->register(SmartyViewHandler::NAME, $smartyViewHandler);
        $this->container->register(PlainViewHandler::NAME, $plainViewHandler);
        $this->container->register(JsonViewHandler::NAME, $jsonViewHandler);
        $this->container->register(RedirectViewHandler::NAME, $redirectViewHandler);
    }

    /**
     * @param Request $request
     * @param $controller
     * @throws ErrorException
     */
    private function handleAction(Request $request, $controller) {

        /** @var $viewModel ViewModel */
        $methodName = $request->getMethodName();
        $viewModel = $controller->$methodName();

        $this->handleViewModel($request, $viewModel);
    }

    /**
     * @param ViewModel $viewModel
     * @param Request $request
     */
    private function handleView($viewModel, $request) {
        $handler = $this->container->get(ViewHandlerProvider::NAME);

        Asserts::notNull($handler, "No handler found for response objcet" . Objects::getClassName($viewModel));

        /** @var $handler ViewHandlerProvider */
        $handler->handleView($viewModel, $request);
    }


    private function filter(Request $request) {
        $filters = $this->container->getByType(HttpFilter::CLASS_NAME);

        if (Collections::isNotEmpty($filters)) {
            $filtersIterator = new \ArrayIterator($filters);
            $chain = new FilterChain($filtersIterator->current(), $filtersIterator);
            $chain->doFilter($request);
        }
    }


    /**
     * @return string
     */
    private function getCacheKey($key) {
        return $this->appName . "_" . $key;
    }

    /**
     * @return null
     */
    private function isApcuCacheEnabled() {
        return $this->apcuExtensionLoaded && $this->config->getProperty(EnableApcuAnnotationHandler::APCU_CACHE_ENABLED, false);
    }

    private function preHandleInterceptor(Request $request) {
        /** @var HandlerInterceptor $interceptor */
        foreach ($this->interceptors as $interceptor) {
            if (BooleanUtils::isFalse($interceptor->preHandle($request))) {
                break;
            }
        }
    }

    private function postHandleIntercetors(Request $request, Response $response) {
        /** @var HandlerInterceptor $interceptor */
        foreach ($this->interceptors as $interceptor) {
            $interceptor->postHandle($request, $response);
        }

    }

    /**
     *
     * @param Request $request
     * @param ViewModel $viewModel
     * @throws ErrorException
     * @throws common\IllegalStateException
     */
    public function handleViewModel(Request $request, $viewModel) {
        Asserts::checkState($viewModel instanceof Response, "Wrong controller action response type. Returned type from controller needs to be instance of Response.");

        $this->postHandleIntercetors($request, $viewModel);

        if (isset($viewModel)) {
            $this->handleView($viewModel, $request);
        } else {
            throw new ErrorException("ViewModel not found. Did you initiated ViewModel? ");
        }
    }

    /**
     *
     * @param $request
     * @throws \Exception
     */
    public function updateRequest($request) {
        /** @var RequestProvider $requestProvider */
        $requestProvider = $this->container->get(RequestProvider::NAME);
        $requestProvider->setRequest($request);
    }

    private function runCommand() {
        $input = new InputInterface();
        $out = new OutputInterface();

        $commands = $this->container->getByType(Command::class);
        Collections::builder($commands)
            ->filter(Predicates::compute(Functions::get("name"), function ($n) use ($input) {
                return StringUtils::startsWith($n, $input->get("command"));
            }))
            ->each(function ($command) use ($input, $out) {
                /** @var Command $command */
                $command->execute($input, $out);
            });
    }

    private function clearCacheIfResetParam() {
        $resetParam = $this->config->getProperty(EnableApcuAnnotationHandler::APCU_CACHE_RESET);

        if (isset($_GET[$resetParam])) {
            $this->beanCache->clearAll();
            $this->hasAllreadyCachedData = false;
        }
    }

    private function getProfile() {
        if (SystemUtils::isCommandLineInterface()) {
            return SystemUtils::getProfile();
        }
        return $this->profile;
    }


}
