<?php

namespace Spark\View\Smarty;

use Spark\Config;
use Spark\Core\Annotation\Inject;
use Spark\Core\Annotation\PostConstruct;
use Spark\Core\Lang\LangKeyProvider;
use Spark\Core\Provider\BeanProvider;
use Spark\Core\Routing\RequestData;
use Spark\Utils\Collections;
use Spark\Utils\Objects;
use Spark\Utils\StringFunctions;
use Spark\Utils\StringUtils;
use Spark\View\Utils\ViewUrlUtils;
use Spark\View\ViewHandler;
use Spark\View\ViewModel;

class SmartyViewHandler extends ViewHandler {

    public const NAME = 'smartyViewHandler';
    public const CACHE_ID = 'spark.smarty.view.cache.id';
    public const COMPILE_CHECK = 'spark.view.cache.compile_check';
    public const CACHE_ENABLED = 'spark.view.cache.enable';
    public const CACHE_LIFE_TIME = 'spark.view.cache.life_time';
    public const DEBUGGING = 'spark.view.cache.debugging';
    public const ERROR_REPORTING = 'spark.view.cache.error.reporting';
    public const FORCE_COMPILE = 'spark.view.cache.force.compile';
    public const MERGE_COMPILED_INCLUDES = 'spark.view.cache.merge.compiled.includes';

    private $rootAppPath;

    /**
     * @var  \Smarty
     */
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
    /**
     * @var string cacheIdPrefix
     */
    private $cacheIdPrefix;

    public function __construct($rootAppPath) {
        $this->rootAppPath = $rootAppPath;
    }

    /**
     * @PostConstruct()
     */
    private function init(): void {
        if (Objects::isNull($this->smarty)) {
            $config = $this->config;
            $this->cacheIdPrefix = $this->config->getProperty(self::CACHE_ID, 'TAHONA_ROCKS');


            $smarty = new \Smarty();
            $smarty->setCacheDir($this->rootAppPath . '/view/cache');
            $smarty->setCompileDir($this->rootAppPath . '/view/compile');

            $appPaths = $this->config->getProperty('app.paths');
            $templatePaths = Collections::stream($appPaths)
                ->map(StringFunctions::concat('/view'))
                ->get();

            $smarty->setTemplateDir($templatePaths);

            $smarty->registerPlugin('function', 'invoke', array($this->smartyPlugins, 'invoke'));
            $smarty->registerPlugin('function', 'path', array($this->smartyPlugins, 'path'));
            $smarty->registerPlugin('function', 'message', array($this->smartyPlugins, 'getMessage'));

            $definedPlugins = $this->smartyPlugins->getDefinedPlugins();
            /** @var SmartyPlugin $plugin */
            foreach ($definedPlugins as $plugin) {
                $smarty->registerPlugin('function', $plugin->getTag(), array($plugin, 'execute'));
            }

//            var_dump($this->smartyPlugins->path(array("path"=>"/admin"), null));

            $smarty->setForceCompile($config->getProperty(self::FORCE_COMPILE, true));
            $smarty->setCompileCheck($config->getProperty(self::COMPILE_CHECK, true));
            $smarty->setCaching($this->getCachingType($config));
            $smarty->setCacheLifetime($config->getProperty(self::CACHE_LIFE_TIME, 1800));
            $smarty->setMergeCompiledIncludes($config->getProperty(self::MERGE_COMPILED_INCLUDES, true));

            $smarty->setDebugging($config->getProperty(self::DEBUGGING, false));
            $smarty->setErrorReporting($config->getProperty(self::ERROR_REPORTING, E_ALL & ~E_NOTICE));

            $this->smarty = $smarty;
        }
    }

    public function isView($viewModel) {
        return $viewModel instanceof ViewModel;
    }

    /**
     * @param $viewModel
     * @param RequestData $request
     * @throws SmartyException
     */
    public function handleView($viewModel, RequestData $request) {
        $this->smarty->setCacheId($this->cacheIdPrefix . '' . $this->getLang());

        /** @var ViewModel $viewModel */
        foreach ($viewModel->getParams() as $key => $value) {
            $this->smarty->assign($key, $value, true);
        }

        $viewPath = $this->getViewPath($viewModel, $request);
        $output = $this->smarty->fetch($viewPath . '.tpl');

        if (StringUtils::contains($output, 'SmartyNoCache')) {
            throw new SmartyException('View render error!');
        }
        echo $output;
    }

    /**
     *
     * @param $viewPath
     * @return string
     */
    private function removePrefix($viewPath): string {
        if (StringUtils::startsWith($viewPath, '/')) {
            return StringUtils::substring($viewPath, 1, StringUtils::length($viewPath));
        }
        return $viewPath;
    }

    private function getLang(): string {
        /** @var LangKeyProvider $langKeyProvider */
        $langKeyProvider = $this->beanProvider->getBean(LangKeyProvider::NAME);
        return $langKeyProvider->getLang();
    }

    /**
     * @param ViewModel $viewModel
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

    /**
     * @param $config
     * @return mixed
     */
    private function getCachingType(Config $config): int {
        $isCaching = $config->getProperty(self::CACHE_ENABLED, false);
        if ($isCaching) {
            return \Smarty::CACHING_LIFETIME_CURRENT;
        }
        return \Smarty::CACHING_OFF;
    }
}
