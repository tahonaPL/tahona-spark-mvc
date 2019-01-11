<?php
/**
 * Date: 28.04.18
 * Time: 22:17
 */

namespace Spark\Core\Processor\Loader;


use Spark\Common\Collection\FluentIterables;
use Spark\Common\IllegalStateException;
use Spark\Common\Optional;
use Spark\Config;
use Spark\Container;
use Spark\Core\Command\Command;
use Spark\Core\Definition\BeanDefinition;
use Spark\Core\Error\GlobalErrorHandler;
use Spark\Core\Filler\MultiFiller;
use Spark\Core\Filter\HttpFilter;
use Spark\Core\Interceptor\HandlerInterceptor;
use Spark\Core\Lang\LangMessageResource;
use Spark\Core\Lang\LangResourcePath;
use Spark\Core\Routing\Exception\RouteNotFoundException;
use Spark\Core\Routing\RoutingDefinition;
use Spark\Http\RequestProvider;
use Spark\Http\Session\SessionProvider;
use Spark\Http\Utils\RequestUtils;
use Spark\Routing;
use Spark\Routing\RoutingUtils;
use Spark\Utils\Asserts;
use Spark\Utils\Collections;
use Spark\Utils\FileUtils;
use Spark\Utils\Functions;
use Spark\Utils\StringFunctions;
use Spark\Utils\StringUtils;
use Spark\Utils\UrlUtils;
use Spark\View\ViewHandlerProvider;

class StaticClassContextLoader implements ContextLoader {

    private $contexts;

    private const CLS = 'DataLoader';

    private const ERROR_CONTEXT = 'ErrorContext';

    public function __construct() {
    }


    public function getContext(): Context {
        if ($this->hasData()) {
            $this->contexts = StaticClassFactory::getObject(self::CLS);

            $path = UrlUtils::getSimplePath();

            if (Collections::hasKey($this->contexts, $path)) {
                $contextName = $this->contexts[$path][0]['context'];
                return StaticClassFactory::getObject($contextName);
            } else {
                $headers = RequestUtils::getHeaders();
                $method = RequestUtils::getMethod();

                $definition = FluentIterables::of($this->contexts)
                    ->flatMap(Functions::getSameObject())
                    ->findFirst(function ($def) use ($path, $headers, $method) {
                        return RoutingUtils::hasExpressionParams($def['path'], $path, $def['params'])
                            && RoutingUtils::isDefinitionCorrect($def['methods'], $def['headers'], $headers, $method);
                    })->orElse(['context' => self::ERROR_CONTEXT]);

                $name = $definition['context'];

                return StaticClassFactory::getObject($name);
            }
        }

        throw new IllegalStateException('No application context found !');
    }

    public function hasData(): bool {
        return StaticClassFactory::isExist(self::CLS);
    }

    public function clear() {
        StaticClassFactory::removeClass(self::CLS);
    }

    public function save(Config $config, Container $container, Routing $route, $exceptionResolvers) {

        $allDefinitions = $route->getDefinitions();

        $controllers = $allDefinitions
            ->flatMap(Functions::getSameObject())
            ->groupBy(function ($def) {
                /** @var RoutingDefinition $def */
                return $def->getControllerClassName();
            })->get();

        $dataLoader = [];

//        FluentIterables::of($container->getAll())
//            ->each(function ($bd) {
//                /** @var BeanDefinition $bd */
//                StaticClassFactory::createClass($bd->getName(), $bd->getBean());
//            });

        $httpFilters = $container->getByType(HttpFilter::class);
        $interceptors = $container->getByType(HandlerInterceptor::class);
        $globalErrorHandler = $container->get(GlobalErrorHandler::NAME);
        $commands = $container->getByType(Command::class);
        $langResources = $container->get(LangMessageResource::NAME);
        $langResourcePaths = $container->getByType(LangResourcePath::class);
        $viewHandlers = $container->get(ViewHandlerProvider::NAME);
        $fillers = $container->getByType(MultiFiller::class);
        $requestProvider = $container->get(RequestProvider::NAME);


        $context = new Context(
            $config,
            $route,
            $httpFilters,
            $interceptors,
            null,
            $exceptionResolvers,
            $globalErrorHandler,
            $commands,
            $langResources,
            $langResourcePaths,
            null,
            $viewHandlers,
            $fillers,
            $requestProvider
        );
        StaticClassFactory::createClass(self::ERROR_CONTEXT, $context);

        foreach ($controllers as $controllerName => $controllerDefinitions) {
            $route = new Routing($controllerDefinitions);
            $route->setSessionProvider($container->get('sessionProvider'));

            $context = new Context(
                $config,
                $route,
                $httpFilters,
                $interceptors,
                $container->get($controllerName),
                $exceptionResolvers,
                $globalErrorHandler,
                $commands,
                $langResources,
                $langResourcePaths,
                null,
                $viewHandlers,
                $fillers,
                $requestProvider
            );

            $contextClassName = StringUtils::replace($controllerName, '\\', '') . 'Context';
            StaticClassFactory::createClass($contextClassName, $context);

            foreach ($controllerDefinitions as $def) {
                /** @var RoutingDefinition $def */
                $path = $def->getPath();

                $dataLoader[$path][] = [
                    'context' => $contextClassName,
                    'path' => $path,
                    'params' => $def->getParams(),
                    'methods' => $def->getRequestMethods(),
                    'headers' => $def->getRequestHeaders()
                ];
            }
        }
        StaticClassFactory::createArrayClass(self::CLS, $dataLoader);
    }

}