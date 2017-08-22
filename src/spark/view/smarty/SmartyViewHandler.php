<?php
/**
 * Created by PhpStorm.
 * User: primosz67
 * Date: 06.07.14
 * Time: 17:42
 */

namespace spark\view\smarty;

use spark\Config;
use spark\core\annotation\Inject;
use spark\http\Request;
use spark\utils\Objects;
use spark\utils\StringUtils;
use spark\view\utils\ViewUrlUtils;
use spark\view\ViewHandler;
use spark\view\ViewModel;

class SmartyViewHandler extends ViewHandler {

    const NAME = "smartyViewHandler";
    const CACHE_ID = "spark.smarty.view.cache.id";
    const COMPILE_CHECK = "view.cache.compile_check";
    const CACHE_ENABLED = "view.cache.enable";
    const CACHE_LIFE_TIME = "view.cache.life_time";
    const DEBUGGING = "view.cache.debugging";
    const ERROR_REPORTING = "view.cache.error.reporting";
    const FORCE_COMPILE = "view.cache.force.compile";

    private $rootAppPath;
    private $smarty;

    /**
     * @Inject
     * @var SmartyPlugins
     */
    private $smartyPlugins;
    /**
     * @Inject()
     * @var Config
     */
    private $config;

    public function __construct($rootAppPath) {
        $this->rootAppPath = $rootAppPath;
    }

    public function isView($viewModel) {
        return $viewModel instanceof ViewModel;
    }

    public function handleView($viewModel, Request $request) {
        $smarty = $this->init();

        $smarty->setCacheId($this->config->getProperty(self::CACHE_ID, "TAHONA_ROCKS") . "" . $request->getLang());

        foreach ($viewModel->getParams() as $key => $value) {
            $smarty->assign($key, $value, true);
        }

        $viewPath = $viewModel->getViewName();
        if (Objects::isNull($viewPath)) {
            $viewPath = ViewUrlUtils::createFullViewPath($request);
        }

        $viewPath = $this->removePrefix($viewPath);


//        $s = new SpeedTester();
//        $s->start();
        $smarty->display($viewPath . '.tpl');
//        $s->displayTime();
    }

    /**
     * @return \Smarty
     */
    private function init() {

        if (Objects::isNull($this->smarty)) {
            $config = $this->config;
            $smarty = new \Smarty();
            $smarty->setCacheDir($this->rootAppPath . "/view/cache");
            $smarty->setCompileDir($this->rootAppPath . "/view/compile");
            $smarty->setTemplateDir($this->rootAppPath . "/view");

            $smarty->registerPlugin("function", "invoke", array($this->smartyPlugins, "invoke"));
            $smarty->registerPlugin("function", "path", array($this->smartyPlugins, "path"));
            $smarty->registerPlugin("function", "message", array($this->smartyPlugins, "getMessage"));

            $definedPlugins = $this->smartyPlugins->getDefinedPlugins();
            /** @var SmartyPlugin $plugin */
            foreach($definedPlugins as $plugin) {
                $smarty->registerPlugin("function", $plugin->getTag(), array($plugin, "execute"));
            }

//            var_dump($this->smartyPlugins->path(array("path"=>"/admin"), null));

            $smarty->force_compile = $config->getProperty(self::FORCE_COMPILE, true);
            $smarty->compile_check = $config->getProperty(self::COMPILE_CHECK, true);
            $smarty->caching = $config->getProperty(self::CACHE_ENABLED, false);
            $smarty->cache_lifetime = $config->getProperty(self::CACHE_LIFE_TIME, 1800);

            $smarty->debugging = $config->getProperty(self::DEBUGGING, false);
            $smarty->error_reporting = $config->getProperty(self::ERROR_REPORTING, E_ALL & ~E_NOTICE);

            $this->smarty = $smarty;
        }
        return $this->smarty;
    }

    /**
     *
     * @param $viewPath
     * @return string
     */
    private function removePrefix($viewPath) {
        if (StringUtils::startsWith($viewPath, "/")) {
            return StringUtils::substring($viewPath, 1, StringUtils::length($viewPath));
        }
        return $viewPath;
    }
}
