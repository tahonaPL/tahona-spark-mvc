<?php
/**
 * Date: 28.04.18
 * Time: 22:17
 */

namespace Spark\Core\Processor\Loader;


use Spark\Common\Optional;
use Spark\Utils\FileUtils;
use Spark\Utils\StringFunctions;
use Spark\Utils\StringUtils;

class StaticClassContextLoader implements ContextLoader {

    private $dataLoader;

    private const CLS = 'DataLoader';

    public function __construct() {

        if ($this->hasData()) {
            $this->dataLoader = StaticClassFactory::getObject(self::CLS);
        }
    }

    public function hasData(): bool {
        return StaticClassFactory::isExist(self::CLS);
    }

    public function getContainer() {
        return $this->dataLoader['container'];
    }

    public function getRoute() {
        return $this->dataLoader['route'];
    }

    public function getConfig() {
        return $this->dataLoader['config'];
    }

    public function getExceptionResolvers() {
        return $this->dataLoader['exceptionResolvers'];
    }

    public function clear() {
        StaticClassFactory::removeClass(self::CLS);
    }

    public function save($config, $container, $route, $exceptionResolvers) {
        $all = [
            'container' => $container,
            'route' => $route,
            'config' => $config,
            'exceptionResolvers' => $exceptionResolvers
        ];

        $this->dataLoader = StaticClassFactory::createClass(self::CLS, $all);
    }
}