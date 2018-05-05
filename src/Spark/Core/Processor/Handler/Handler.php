<?php
/**
 * Date: 05.05.18
 * Time: 10:03
 */

namespace Spark\Core\Processor\Handler;


use ReflectionClass;
use Spark\Config;
use Spark\Container;
use Spark\Core\Annotation\Inject;
use Spark\Routing;
use Spark\Routing\RoutingInfo;

abstract class Handler {

    /**
     * @Inject()
     * @var Container
     */
    private $container;
    /**
     * @Inject()
     * @var Routing
     */
    private $routing;
    /**
     * @Inject()
     * @var Config
     */
    private $config;

    /**
     * @param $class
     * @return bool
     */
    abstract protected function supports(ReflectionClass $class): bool;

    protected function getContainer(): Container {
        return $this->container;
    }

    public function setContainer(Container $container){
        $this->container = $container;
    }

    protected function getRouting(): Routing {
        return $this->routing;
    }

    /**
     * @param RoutingInfo $routing
     */
    public function setRouting(&$routing){
        $this->routing = $routing;
    }

    protected function getConfig(): Config {
        return $this->config;
    }

    /**
     * @param Config $config
     */
    public function setConfig(&$config){
        $this->config = $config;
    }

    public function clear(){
        $this->routing = null;
        $this->container = null;
        $this->config = null;
    }
}