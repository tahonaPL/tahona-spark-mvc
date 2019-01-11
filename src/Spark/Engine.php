<?php

namespace Spark;

use ErrorException;
use Spark\Cache\ApcuBeanCache;
use Spark\Cache\BeanCache;
use Spark\Cache\Service\CacheProvider;
use Spark\Cache\Service\CacheService;
use Spark\Common\Collection\FluentIterables;
use Spark\Core\Annotation\Handler\EnableApcuAnnotationHandler;
use Spark\Core\Command\Command;
use Spark\Core\Command\Input\InputInterface;
use Spark\Core\Command\Output\OutputInterface;
use Spark\Core\CoreConfig;
use Spark\Core\Error\ExceptionResolver;
use Spark\Core\Error\GlobalErrorHandler;
use Spark\Core\Event\EventBus;
use Spark\Core\Event\Handler\SubscribeAnnotationHandler;
use Spark\Core\Filler\BeanFiller;
use Spark\Core\Filler\CookieFiller;
use Spark\Core\Filler\FileObjectFiller;
use Spark\Core\Filler\MultiFiller;
use Spark\Core\Filler\RequestFiller;
use Spark\Core\Filler\SessionFiller;
use Spark\Core\Filler\SimpleMultiFiller;
use Spark\Core\Filter\FilterChain;
use Spark\Core\Filter\HttpFilter;
use Spark\Core\Interceptor\HandlerInterceptor;
use Spark\Core\Lang\CookieLangKeyProvider;
use Spark\Core\Lang\LangKeyProvider;
use Spark\Core\Lang\LangMessageResource;
use Spark\Core\Lang\LangResourcePath;
use Spark\Core\Library\Annotations;
use Spark\Core\Library\BeanLoader;
use Spark\Core\Processor\InitAnnotationProcessors;
use Spark\Core\Processor\Loader\Context;
use Spark\Core\Processor\Loader\StaticClassContextLoader;
use Spark\Core\Provider\BeanProvider;
use Spark\Core\Routing\Factory\RequestDataFactory;
use Spark\Core\Routing\RequestData;
use Spark\Core\Routing\RoutingDefinition;
use Spark\Core\Utils\SystemUtils;
use Spark\Form\Validator\AnnotationValidator;
use Spark\Http\Request;
use Spark\Http\RequestProvider;
use Spark\Http\Response;
use Spark\Http\Session\BaseSessionProvider;
use Spark\Http\Session\SessionProvider;
use Spark\Http\Session\SessionProviderProxy;
use Spark\Http\Utils\RequestUtils;
use Spark\Routing\RoutingInfo;
use Spark\Utils\Asserts;
use Spark\Utils\BooleanUtils;
use Spark\Utils\Collections;
use Spark\Utils\Functions;
use Spark\Utils\Objects;
use Spark\Utils\Predicates;
use Spark\Utils\Reflection\AnnotationReaderProvider;
use Spark\Utils\ReflectionUtils;
use Spark\Utils\StringUtils;
use Spark\Utils\UrlUtils;
use Spark\View\Json\JsonViewHandler;
use Spark\View\Json\JsonViewModel;
use Spark\View\Plain\PlainViewHandler;
use Spark\View\Redirect\RedirectViewHandler;
use Spark\View\Smarty\SmartyPlugins;
use Spark\View\Smarty\SmartyViewHandler;
use Spark\View\ViewHandlerProvider;
use Spark\View\ViewModel;

class Engine {


    /**
     * @var string Application Name used in apcu Cache as a prefix
     */
    private $appName;

    private $appPath;

    /**
     * @var BeanCache
     */
    private $beanCache;

    private $exceptionResolvers;

    private $profile;
    private $contextLoader;
    /**
     * @var Context
     */
    private $context;

    public function __construct(string $appName, $profile, array $rootAppPath) {
        Asserts::checkState(\extension_loaded('apcu'), 'Apcu Cache enable is mandatory!');
        Asserts::notNull($rootAppPath[0], "Engine configuration: did you forget root project path('s) field: 'root' e.g 'path'");

        $this->appName = $appName;
        $this->profile = $profile;

        $appPaths = $rootAppPath;
        $this->appPath = $appPaths[0];

        $this->beanCache = new ApcuBeanCache();
        $this->contextLoader = new StaticClassContextLoader();


        if ($this->contextLoader->hasData()) {
            $this->context = $this->contextLoader->getContext();
            $this->exceptionResolvers = $this->context->getExceptionResolvers();

            $resetParam = $this->context->getConfig()->getProperty(EnableApcuAnnotationHandler::APCU_CACHE_RESET);

            if (isset($_GET[$resetParam])) {
                $this->contextLoader->clear();
                $this->beanCache->clearAll();
            }
        }

        if (!$this->contextLoader->hasData()) {
            $this->contextLoader->clear();

            $container = new Container();
            $route = new Routing(array());
            $config = new Config();

            $config->set('app.profile', $this->getProfile());
            $config->set('app.path', $this->appPath);
            $config->set('app.paths', $appPaths);
            $config->set('src.path', $this->getSourcePath());

            $initAnnotationProcessors = new InitAnnotationProcessors($route, $config, $container);

            $beanLoader = new BeanLoader($initAnnotationProcessors, $config, $container);
            foreach ($appPaths as $path) {
                $beanLoader->addFromPath($path . '/src', array('proxy'));
            }

            $beanLoader->addClass(CoreConfig::class);
            $beanLoader->process();

            $this->addBaseServices($container, $route, $config);
            $container->setConfig($config);
            $container->initServices();

            $beanLoader->postProcess();

            $this->afterAllBean($container, $route);

            /** @var BeanProvider $bp */
            $bp = $container->get(BeanProvider::NAME);
            $bp->clear();

            $this->contextLoader->save(
                $config,
                $container,
                $route,
                $container->getByType(ExceptionResolver::class)
            );
            $this->context = $this->contextLoader->getContext();
        }

        /** @var GlobalErrorHandler $globalErrorHandler */
        $globalErrorHandler = $this->context->getGlobalErrorHandler();
        $globalErrorHandler->setup($this->context->getExceptionResolvers());
    }

    public function run(): void {

        if (SystemUtils::isCommandLineInterface()) {
            $this->runCommand();
        } else {
            $this->runController();
        }
    }

    private function runController(): void {
        $this->handleRequest([]);
    }

    private function handleRequest(array $responseParams = array()): void {
        $this->devToolsInit();

        $registeredHostPath = UrlUtils::getHost();
        $requestData = $this->context->getRoute()
            ->createRequest($registeredHostPath);

        //TODO!!!!!!!!!!!!!!!!!!!!
        $this->updateRequestProvider($requestData);

        //Interceptor
        $this->executeFilter($requestData);
        $this->preHandleInterceptor($requestData);

        //Controller

        /** @var $controller Controller */
        $controller = $this->context->getController();

        if ($controller instanceof Controller) {
            $controller->init($requestData, $responseParams);
        }

        //ACTION->VIEW
        $this->handleAction($requestData, $controller);
    }

    private function devToolsInit(): void {
        $enabled = $this->context->getConfig()->getProperty(Config::DEV_ENABLED);
        if ($enabled) {
            RequestUtils::setCookie('XDEBUG_SESSION', true);
        }
    }

    private function addBaseServices(Container $container, Routing $route, Config $config): void {
        $container->register('cache', $this->beanCache);
        $container->registerObj(new CacheProvider());
        $container->registerObj(new CacheService());

        //EventBus
        $container->registerObj(new EventBus());
        $container->registerObj(new SubscribeAnnotationHandler());

        $langResource = new LangMessageResource(array());
        $container->register(LangMessageResource::NAME, $langResource);
        $container->register(LangKeyProvider::NAME, new CookieLangKeyProvider('lang'));

        $container->register(SmartyPlugins::NAME, new SmartyPlugins());
        $container->register(RequestProvider::NAME, new RequestProvider());
        $container->register(RoutingInfo::NAME, new RoutingInfo($route));
        $container->register('sessionProvider', new SessionProviderProxy());
        $container->register('defaultSessionProvider', new BaseSessionProvider());

        $container->registerObj(new RequestDataFactory());
        $container->registerObj(new BeanProvider($container));

        $validator = new AnnotationValidator($langResource, new AnnotationReaderProvider());
        $validator->addDefaultValidators();
        $container->addBean('annotationValidator', $validator);

        $container->registerObj($config);
        $container->registerObj($route);
        $container->registerObj(new GlobalErrorHandler($this));

        $container->registerObj(new SimpleMultiFiller());
        $container->registerObj(new RequestFiller());
        $container->registerObj(new SessionFiller());
        $container->registerObj(new FileObjectFiller());
        $container->registerObj(new CookieFiller());
        $container->registerObj(new BeanFiller());

        $this->addViewHandlersToService($container);
    }

    private function afterAllBean(Container $container , Routing $route): void {
        $resourcePaths = $container->getByType(LangResourcePath::class);

        /** @var LangMessageResource $resource */
        $resource = $container->get(LangMessageResource::NAME);
        $resource->addResources($resourcePaths);

        $sessionProvider = $container->get('sessionProvider');
        $route->setSessionProvider($sessionProvider);
    }

    private function addViewHandlersToService(Container $container): void {
        $smartyViewHandler = new SmartyViewHandler($this->appPath);
        $plainViewHandler = new PlainViewHandler();
        $jsonViewHandler = new JsonViewHandler();
        $redirectViewHandler = new RedirectViewHandler();

        $provider = new ViewHandlerProvider();
        $container->register(ViewHandlerProvider::NAME, $provider);
        $container->register('defaultViewHandler', $smartyViewHandler);
        $container->register(SmartyViewHandler::NAME, $smartyViewHandler);
        $container->register(PlainViewHandler::NAME, $plainViewHandler);
        $container->register(JsonViewHandler::NAME, $jsonViewHandler);
        $container->register(RedirectViewHandler::NAME, $redirectViewHandler);
    }

    /**
     * @param RequestData|Request $requestData
     * @param $controller
     * @throws ErrorException
     */
    private function handleAction(RequestData $requestData, $controller): void {

        /** @var $viewModel ViewModel */
        $methodName = $requestData->getMethodName();
        $routeDef = $requestData->getRouteDefinition();
        $methodFillersParams = $this->executeFillers($routeDef);

        $viewModel = Objects::invokeMethod($controller, $methodName, $methodFillersParams);

        if ($this->isRestController($routeDef)) {
            $viewModel = new JsonViewModel($viewModel);
        }

        $this->handleViewModel($requestData, $viewModel);
    }

    /**
     * @param ViewModel $viewModel
     * @param Request $request
     */
    private function handleView($viewModel, $request): void {
        $handler = $this->context->getViewHandlers();

        Asserts::notNull($handler, 'No handler found for response object' . Objects::getClassName($viewModel));

        /** @var $handler ViewHandlerProvider */
        $handler->handleView($viewModel, $request);
    }


    private function executeFilter( Request $request): void {
        $filters = $this->context->getHttpFilters();

        if (Collections::isNotEmpty($filters)) {
            $filtersIterator = new \ArrayIterator($filters);
            $chain = new FilterChain($filtersIterator->current(), $filtersIterator);
            $chain->doFilter($request);
        }
    }

    private function preHandleInterceptor(Request $request): void {
        $interceptors = $this->context->getInterceptors();

        /** @var HandlerInterceptor $interceptor */
        foreach ($interceptors as $interceptor) {
            if (BooleanUtils::isFalse($interceptor->preHandle($request))) {
                break;
            }
        }
    }

    private function postHandleIntercetors(Request $request, Response $response): void {
        $interceptors = $this->context->getInterceptors();

        /** @var HandlerInterceptor $interceptor */
        foreach ($interceptors as $interceptor) {
            $interceptor->postHandle($request, $response);
        }

    }

    /**
     *
     * @param Request $request
     * @param ViewModel $viewModel
     * @throws ErrorException
     * @throws Common\IllegalStateException
     */
    public function handleViewModel(Request $request, $viewModel): void {
        Asserts::checkState($viewModel instanceof Response, 'Wrong controller action response type. Returned type from controller needs to be instance of Response.');

        $this->postHandleIntercetors($request, $viewModel);

        if (Objects::isNotNull($viewModel)) {
            $this->handleView($viewModel, $request);
        } else {
            throw new ErrorException('ViewModel not found. Did you initiated ViewModel? ');
        }
    }

    /**
     *
     * @param $request
     * @throws \Exception
     */
    public function updateRequestProvider(Request $request): void {
        /** @var RequestProvider $requestProvider */
        $requestProvider = $this->context->getRequestProvider();
        $requestProvider->setRequest($request);
    }

    private function runCommand(): void {
        $input = new InputInterface();
        $out = new OutputInterface();

        $commands = $this->context->getCommands();
        Collections::builder($commands)
            ->filter(Predicates::compute(Functions::get('name'), function ($n) use ($input) {
                return StringUtils::startsWith($n, $input->get('command'));
            }))
            ->each(function ($command) use ($input, $out) {
                /** @var Command $command */
                $command->execute($input, $out);
            });
    }


    private function getProfile(): ?string {
        if (SystemUtils::isCommandLineInterface()) {
            return SystemUtils::getProfile();
        }
        return $this->profile;
    }

    private function executeFillers(RoutingDefinition $rf): array {
        $parameters = $rf->getActionMethodParameters();
        $simpleFiller = $this->context->getFillers();


        $tmpParameters = $parameters;

        $results = [];
        /** @var MultiFiller $filler */
        foreach ($simpleFiller as $filler) {
            $filedParams = $filler->filter($tmpParameters);

            $newParams = [];
            foreach ($filedParams as $k => $filedParam) {

                if (Objects::isNotNull($filedParam) && !Collections::hasKey($results, $k)) {
                    $results[$k] = $filedParam;
                } else {
                    $newParams[$k] = $parameters[$k];
                }
            }
            $tmpParameters = $newParams;
        }

        return FluentIterables::of(Collections::getKeys($parameters))
            ->map(function ($key) use ($results) {
                return Collections::getValue($results, $key);
            })->getList();
    }

    private function isRestController(RoutingDefinition $routeDef): bool {
        $controllerAnnotations = $routeDef->getControllerAnnotations();

        $restControllerAnnotation = Collections::builder()
            ->addAll($controllerAnnotations)
            ->findFirst(function ($ann) {
                return Objects::getClassName($ann) === Annotations::REST_CONTROLLER;
            });

        return $restControllerAnnotation->isPresent();
    }

    /**
     * @return string
     */
    private function getSourcePath(): string {
        return $this->appPath . '/src';
    }



}
