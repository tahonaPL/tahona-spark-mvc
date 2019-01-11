<?php
/**
 * Date: 28.04.18
 * Time: 22:17
 */

namespace Spark\Core\Processor\Loader;


use Spark\Common\Collection\FluentIterables;
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
use Spark\Core\Routing\RoutingDefinition;
use Spark\Http\RequestProvider;
use Spark\Http\Session\SessionProvider;
use Spark\Http\Utils\RequestUtils;
use Spark\Routing;
use Spark\Routing\RoutingUtils;
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

    public function __construct() {
    }


    public function getContext(): Context {
        if ($this->hasData()) {
            $this->contexts = StaticClassFactory::getObject(self::CLS);

            $path = UrlUtils::getSimplePath();

            if (Collections::hasKey($this->contexts, $path)) {
                return StaticClassFactory::getObject($this->contexts[$path][0]['context']);
            } else {
                $headers = RequestUtils::getHeaders();
                $method = RequestUtils::getMethod();

                $def = FluentIterables::of($this->contexts)
                    ->flatMap(Functions::getSameObject())
                    ->findFirst(function ($def) use ($path, $headers, $method) {
                        return RoutingUtils::hasExpressionParams($def['path'], $path, $def['params'])
                            && RoutingUtils::isDefinitionCorrect($def['methods'], $def['headers'], $headers, $method);
                    })->orElse(null);

                return StaticClassFactory::getObject($def['context']);
            }
        }
    }

    public
    function hasData(): bool {
        return StaticClassFactory::isExist(self::CLS);
    }

    public
    function clear() {
        StaticClassFactory::removeClass(self::CLS);
    }

    public
    function save(Config $config, Container $container, Routing $route, $exceptionResolvers) {

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


        foreach ($controllers as $controllerName => $controllerDefinitions) {
            $route = new Routing($controllerDefinitions);
            $route->setSessionProvider($container->get('sessionProvider'));


            $context = new Context(
                $config,
                $route,
                $container->getByType(HttpFilter::class),
                $container->getByType(HandlerInterceptor::class),
                $container->get($controllerName),
                $exceptionResolvers,
                $container->get(GlobalErrorHandler::NAME),
                $container->getByType(Command::class),
                $container->get(LangMessageResource::NAME),
                $container->getByType(LangResourcePath::class),
                null,
                $container->get(ViewHandlerProvider::NAME),
                $container->getByType(MultiFiller::class),
                $container->get(RequestProvider::NAME)
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