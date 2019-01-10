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

    private const FILE_NAME = 'DataLoader.php';

    public function __construct() {

        if ($this->hasData()) {
            $this->dataLoader = $this->createDataLoader();
        }

    }

    public function hasData(): bool {
        return FileUtils::exist($this->getFilePath());
    }

    public function getContainer() {
        return $this->dataLoader->getContainer();
    }

    public function getRoute() {
        return $this->dataLoader->getRoute();
    }

    public function getConfig() {
        return $this->dataLoader->getConfig();
    }

    public function getExceptionResolvers() {
        return $this->dataLoader->getExceptionResolvers();
    }

    public function clear() {
        FileUtils::removeFile('/app/src/context/' . self::FILE_NAME);
    }

    public function save($config, $container, $route, $exceptionResolvers) {
        $fileTemplate = $this->getFileTemplate();

//        $content = Optional::of($fileTemplate)
//            ->map(StringFunctions::replace('{123_CONTAINER}', StringUtils::replace(serialize($container), "'", "\'")))
//            ->map(StringFunctions::replace('{123_ROUTE}', StringUtils::replace(serialize($route), "'", "\'")))
//            ->map(StringFunctions::replace('{123_CONFIG}', StringUtils::replace(serialize($config), "'", "\'")))
//            ->map(StringFunctions::replace('{123_EXCEPTIONS}', StringUtils::replace(serialize($exceptionResolvers), "'", "\'")))
//            ->get();
//
//

        $all = [
            'container' => $container,
            'route' => $route,
            'config' => $config,
            'exceptionResolvers'=>$exceptionResolvers
        ];

        $content = Optional::of($fileTemplate)
            ->map(StringFunctions::replace('{123_ALL}', StringUtils::replace(serialize($all), "'", "\'")))
            ->get();

        FileUtils::writeToFile($content, $this->getFilePath(), true);

        $this->dataLoader = $this->createDataLoader();
    }

    /**
     * @return string
     */
    public function getFileTemplate(): string {
        $fileTemplate = '<?php
namespace context;


class DataLoader {

    private $all;
    private $container;
    private $route;
    private $config;
    private $exceptionResolvers;

    public function __construct() {
        $this->all = unserialize(\'{123_ALL}\');
    }


    public function getContainer() {
        return $this->all[\'container\'];
    }

    public function getRoute() {
        return $this->all[\'route\'];
    }

    public function getConfig() {
        return $this->all[\'config\'];
    }

    public function getExceptionResolvers() {
        return $this->all[\'exceptionResolvers\'];
    }
}';
        return $fileTemplate;
    }

    /**
     * @return string
     */
    public function getFilePath(): string {
        $fileName = self::FILE_NAME;
        return __ROOT__ . "app/src/context/$fileName";
    }

    /**
     * @return \context\DataLoader
     */
    public function createDataLoader(): \context\DataLoader {
        return new \context\DataLoader();
    }
}