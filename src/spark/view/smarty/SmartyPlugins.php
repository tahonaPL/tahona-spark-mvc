<?php
/**
 * Created by PhpStorm.
 * User: primosz67
 * Date: 14.03.15
 * Time: 19:50
 */

namespace spark\view\smarty;


use PHPUnit\Runner\Exception;
use spark\core\annotation\Inject;
use spark\core\annotation\PostConstruct;
use spark\core\provider\BeanProvider;
use spark\Routing;
use spark\seo\SeoUrlFactory;
use spark\seo\WithSeoUrl;
use spark\core\lang\LangMessageResource;
use spark\utils\UrlUtils;
use spark\utils\Collections;
use spark\utils\StringUtils;

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

        $this->beanProvider = null; //dangereous to have this
    }


    public function path($params, $smarty) {
        $path = $this->getPath($params);
        $path .= $this->handleSeo($params, $path);
        $path1 = UrlUtils::getPath($path);

        return $path1;
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