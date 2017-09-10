<?php
/**
 * Created by PhpStorm.
 * User: primosz67
 * Date: 14.03.15
 * Time: 19:50
 */

namespace Spark\View\smarty;


use PHPUnit\Runner\Exception;
use Spark\Core\annotation\Inject;
use Spark\Core\annotation\PostConstruct;
use Spark\Core\provider\BeanProvider;
use Spark\Routing;
use Spark\Seo\SeoUrlFactory;
use Spark\Seo\WithSeoUrl;
use Spark\Core\lang\LangMessageResource;
use Spark\Utils\UrlUtils;
use Spark\Utils\Collections;
use Spark\Utils\StringUtils;

class SmartyPlugins {
    const NAME = "smartyPlugins";

    const SEO_OBJECT = "seoObject";

    /**
     * Smarty plugins
     */
    private $definedPlugins;
    /**
     * @Inject
     * @var LangMessageResource
     */
    private $langMessageResource;

    /**
     * @Inject()
     * @var BeanProvider
     */
    private $beanProvider;

    /**
     * @Inject
     * @var Routing
     */
    private $routing;

    /**
     * @PostConstruct()
     */
    private function init() {
        $this->definedPlugins = $this->beanProvider->getByType(SmartyPlugin::class);

        $this->beanProvider = null; //dangerous to have this
    }


    public function path($params, $smarty) {
        $path = $this->getPath($params);

//
//        $path .= $this->handleSeo($params, $path);
//        $path1 = UrlUtils::getPath($path);

        return $path;
    }

    public function invoke($params, $smarty) {
        $method = $params["method"];
        $val = $params["value"];
        return $method($val);
    }


    public function getMessage($params, $smarty) {
        $code = $params["code"];

        if (Collections::hasKey($params, "params")) {
            return $this->langMessageResource->get($code, $params["params"]);
        } else {
            return $this->langMessageResource->get($code);

        }
    }

    /**
     * @param $params
     * @param $path
     * @return string
     */
    private function handleSeo($params) {
        if (Collections::hasKey($params, self::SEO_OBJECT)) {
            $seoObject = $params[self::SEO_OBJECT];
            return SeoUrlFactory::getSeoUrlFromSeoObject($seoObject);
        }
        return "";
    }

    /**
     * @return mixed
     */
    public function getDefinedPlugins() {
        return $this->definedPlugins;
    }

    /**
     * @param $params
     * @return mixed
     */
    private function getPath($params) {
        $path = $params["path"];
        $newPath = $this->routing->resolveRoute($path, Collections::getValueOrDefault($params, "params", array()));

        if (StringUtils::isNotBlank($newPath)) {
            return $newPath;
        }
        return $path;
    }

} 