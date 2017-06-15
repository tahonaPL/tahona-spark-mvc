<?php

namespace spark;

use spark\common\IllegalArgumentException;
use spark\utils\Asserts;
use spark\utils\Collections;
use spark\utils\Objects;
use spark\utils\StringUtils;

/**
 *
 * App configuration as basic property provider with cache.
 *
 * Class Config
 * @package spark
 */
class Config {

    const SECURITY_PARAM = "security.enabled";
    const ERROR_HANDLING_ENABLED ="error.errorHandling" ;

    const DEV = "dev";
    const DEV_ENABLED = "dev.enable";
    const DEV_XDEBUG = "dev.xdebug";

    const MAIL_FROM_TITLE_KEY = "mail.from.title";
    const MAIL_FROM_EMAIL_KEY = "mail.from.email";

    const WEB_PAGE = "web.page";
    const APCU_CACHE_ENABLED = "";


    private $cache = array();

    /**
     * Make sure that property will not be replace in the code!
     *
     * @param $property String only like $config->getProperty("db.user");
     */
    public function getProperty($property, $default = null) {
        return Collections::getValueOrDefault($this->cache, $property, $default);
    }

    public function hasProperty($property) {
        return Collections::hasKey($this->cache, $property);
    }

    public function set($code, $value) {
        $this->cache[$code] = $value;
    }

    public function add($code, $value = array()) {
        Asserts::checkArray($value, "Value must be array");

        if (isset($this->cache[$code])) {
            $this->cache[$code] = Collections::builder($this->cache[$code])
                ->addAll($value)
                ->get();
        } else {
            $this->cache[$code]= $value;
        }

    }

    /**
     * @param $property
     * @return bool
     */
    private function isPropertyCached($property) {
        return Collections::hasKey($this->cache, $property);
    }


    /**
     * @param $prefix
     * @param $properties
     */
    private function cacheProperty($prefix, $properties) {
        if (Objects::isArray($properties)) {
            foreach ($properties as $key => $prop) {
                $joined = StringUtils::join(".", array($prefix, $key), true);
                $this->cacheProperty($joined, $prop);
            }
        }
        //save parent
        $this->cache[$prefix] = $properties;
    }


}
