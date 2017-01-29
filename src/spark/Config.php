<?php

namespace spark;

use spark\common\IllegalArgumentException;
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
    const DEV_SMARTY_FORCE_COMPILE = "dev.smartyForceCompile";
    const DEV_XDEBUG = "dev.xdebug";

    const MAIL_FROM_TITLE_KEY = "mail.from.title";
    const MAIL_FROM_EMAIL_KEY = "mail.from.email";

    const WEB_PAGE = "web.page";

    private $mode;
    private $config = array();

    public function __construct($config) {
        $this->config = $config;
    }

    private $cache = array();

    /**
     * Make sure that property will not be replace in the code!
     *
     * @param type $property String only like $config->getProperty("db.user");
     */
    public function getProperty($property) {
        return $this->cache[$property];
    }

    public function hasProperty($property) {
        return Collections::hasKey($this->cache, $property);
    }

    public function setMode($mode) {
        $this->mode = $mode;

        $this->rebuildConfig();
    }

    /**
     * @return mixed
     */
    public function getMode() {
        return $this->mode;
    }

    /**
     * @param $property
     * @return bool
     */
    private function isPropertyCached($property) {
        return Collections::hasKey($this->cache, $property);
    }

    private function rebuildConfig() {
        $properties = $this->config[$this->mode];
        $prefix = "";
        $this->cacheProperty($prefix, $properties);
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
