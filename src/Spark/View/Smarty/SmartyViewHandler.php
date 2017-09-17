<?php
/**
 * Created by PhpStorm.
 * User: primosz67
 * Date: 06.07.14
 * Time: 17:42
 */

namespace Spark\View\Smarty;

use Spark\Config;
use Spark\Core\Annotation\Inject;
use Spark\Core\Lang\LangKeyProvider;
use Spark\Core\Provider\BeanProvider;
use Spark\Http\Request;
use Spark\Core\Routing\RequestData;
use Spark\Utils\Objects;
use Spark\Utils\StringUtils;
use Spark\View\Utils\ViewUrlUtils;
use Spark\View\ViewHandler;
use Spark\View\ViewModel;

class SmartyViewHandler extends ViewHandler {

    const NAME            = "smartyViewHandler";
    const CACHE_ID        = "spark.smarty.view.cache.id";
    const COMPILE_CHECK   = "spark.view.cache.compile_check";
    const CACHE_ENABLED   = "spark.view.cache.enable";
    const CACHE_LIFE_TIME = "spark.view.cache.life_time";
    const DEBUGGING       = "spark.view.cache.debugging";
    const ERROR_REPORTING = "spark.view.cache.error.reporting";
    const FORCE_COMPILE   = "spark.view.cache.force.compile";

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

    /**
     * @Inject
     * @var BeanProvider
     */
    private $beanProvider;


    public function __construct($rootAppPath) {
        $this->rootAppPath = $rootAppPath;
    }

    public function isView($viewModel) {
        return $viewModel instanceof ViewModel;
    }

    public function handleView($viewModel, RequestData $request) {
        $smarty = $this->init();

        $smarty->setCacheId($this->config->getProperty(self::CACHE_ID, "TAHONA_ROCKS") . "" . $this->getLang());

        /** @var ViewModel $viewModel */
        foreach ($viewModel->getParams() as $key => $value) {
            $smarty->assign($key, $value, true);
        }

        $viewPath = $this->getViewPath($viewModel, $request);
        $smarty->display($viewPath . '.tpl');
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
            foreach ($definedPlugins as $plugin) {
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

    /**
     * @return string
     */
    private function getLang() {
        /** @var LangKeyProvider $langKeyProvider */
        $langKeyProvider = $this->beanProvider->getBean(LangKeyProvider::NAME);
        return $langKeyProvider->getLang();
    }

    /**
     * @param $viewModel
     * @param RequestData $request
     * @return string
     */
    private function getViewPath($viewModel, RequestData $request): string {
        $viewPath = $viewModel->getViewName();
        if (Objects::isNull($viewPath)) {
            $viewPath = ViewUrlUtils::createFullViewPath($request);
        }

        return $this->removePrefix($viewPath);
    }
}
