<?php
/**
 * Created by PhpStorm.
 * User: primosz67
 * Date: 20.10.16
 * Time: 00:38
 */

namespace spark\core\engine;


use spark\core\Bootstrap;
use spark\core\SimpleBootstrap;
use spark\http\Request;
use spark\utils\FileUtils;
use spark\utils\StringUtils;

class EngineFactory {

    /**
     *
     * @param Request $request
     * @param EngineConfig $engineConfig
     * @return Bootstrap
     */
    public static function getBootstrap($request, EngineConfig $engineConfig) {
        $fullPath = EngineFactory::getBootstrapFilePath($request, $engineConfig);
        $directoryPath = $engineConfig->getRootAppPath() . "/src/" . $fullPath . ".php";

        if (FileUtils::isFileExist($directoryPath)) {
            $className = self::getBootstrapClass($fullPath);
            return new $className();
        } else {
            return new SimpleBootstrap();
        }
    }

    /**
     * @param $filePath
     * @return string
     */
    private static function getBootstrapClass($filePath) {
        return StringUtils::replace($filePath, "/", "\\");
    }

    /**
     *
     * Move this to apc_cache
     * @param Request $request
     * @param EngineConfig $engineConfig
     * @return string
     */
    public static function getBootstrapFilePath(Request $request, EngineConfig $engineConfig) {
        $moduleName = $request->getModuleName();
        $array = explode("/", $moduleName);
        $count = count($array);

        $rootAppPath = $engineConfig->getRootAppPath();

        if ($count >= 2) {
            for (; 0 < $count; $count--) {

                $moduleName = StringUtils::replace($moduleName, $array[$count - 1], "");
                $filePath = $request->getNamespace() . "/" . $moduleName . "/Bootstrap";
                $filePath = StringUtils::replace($filePath, "//", "/");
                $fullPath = $rootAppPath . "/src/" . $filePath . ".php";
                if (file_exists($fullPath)) {
                    break;
                }
            }

            return $filePath;
        } else {
            return $request->getNamespace() . "/" . $moduleName . "/Bootstrap";
        }
    }
}