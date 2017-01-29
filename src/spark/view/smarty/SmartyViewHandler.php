<?php
/**
 * Created by PhpStorm.
 * User: primosz67
 * Date: 06.07.14
 * Time: 17:42
 */

namespace spark\view\smarty;

use spark\Config;
use spark\core\di\Inject;
use spark\http\Request;
use spark\utils\Objects;
use spark\view\utils\ViewUrlUtils;
use spark\view\ViewHandler;
use spark\view\ViewModel;

class SmartyViewHandler extends ViewHandler {

    const NAME = "smartyViewHandler";

    const CACHE_ID = "TAHONA_ROCKS";

    private $rootAppPath;
    private $smarty;

    /**
     * @Inject
     * @var SmartyPlugins
     */
    private $smartyPlugins;

    function __construct($rootAppPath) {
        $this->rootAppPath = $rootAppPath;
    }

    public function isView(ViewModel $viewModel) {
        if ($viewModel instanceof ViewModel) {
            return true;
        }
    }

    public function handleView(ViewModel $viewModel, Request $request) {
        $smarty = $this->init();

        $smarty->setCacheId(self::CACHE_ID . "" . $request->getLang());

        foreach ($viewModel->getParams() as $key => $value) {
            $smarty->assign($key, $value, true);
        }

        $viewPath = $viewModel->getViewName();
        if (Objects::isNull($viewPath)) {
            $viewPath = ViewUrlUtils::createFullViewPath($request);
        }
//        $s = new SpeedTester();
//        $s->start();
        $smarty->display($viewPath . '.tpl');
//        $s->displayTime();
    }

    /**
     * @return \spark\Config
     */
    private function getConfig() {
        return $this->getViewHandlerProvider()->getConfig();
    }

    /**
     * @return \Smarty
     */
    private function init() {

        if (Objects::isNull($this->smarty)) {
            $config = $this->getConfig();
            $smarty = new \Smarty();
            $smarty->setCacheDir($this->rootAppPath . "/view/cache");
            $smarty->setCompileDir($this->rootAppPath . "/view/compile");
            $smarty->setTemplateDir($this->rootAppPath . "/view");

            $smarty->registerPlugin("function", "invoke", array($this->smartyPlugins, "invoke"));
            $smarty->registerPlugin("function", "path", array($this->smartyPlugins, "path"));
            $smarty->registerPlugin("function", "message", array($this->smartyPlugins, "getMessage"));

            $smarty->force_compile = $config->getProperty(Config::DEV_SMARTY_FORCE_COMPILE);
            $smarty->compile_check = $config->getProperty("view.cache.compile_check");
            $smarty->caching = $config->getProperty("view.cache.enable");
            $smarty->cache_lifetime = $config->getProperty("view.cache.life_time");

            $smarty->debugging = false;
            $smarty->error_reporting = E_ALL & ~E_NOTICE;

            $this->smarty = $smarty;
        }
        return $this->smarty;
    }
}
