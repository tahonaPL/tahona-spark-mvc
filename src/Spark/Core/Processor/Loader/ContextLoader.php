<?php
/**
 * Date: 29.04.18
 * Time: 00:14
 */

namespace Spark\Core\Processor\Loader;

interface ContextLoader {
    public function hasData(): bool;

    public function getContainer();

    public function getRoute();

    public function getConfig();

    public function getExceptionResolvers();

    public function clear();

    public function save($config, $container, $route, $exceptionResolvers);
}