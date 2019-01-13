<?php
/**
 * Date: 29.04.18
 * Time: 00:14
 */

namespace Spark\Core\Processor\Loader;

use Spark\Config;
use Spark\Container;
use Spark\Routing;

interface ContextLoader {
    public function hasData(): bool;

    public function getContext($contextLoaderType): Context;

    public function clear();

    public function save(Config $config, Container $container, Routing $route, array $exceptionResolvers);
}