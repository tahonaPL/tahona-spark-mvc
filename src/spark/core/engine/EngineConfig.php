<?php
/**
 * Created by PhpStorm.
 * User: primosz67
 * Date: 19.10.16
 * Time: 23:22
 */

namespace spark\core\engine;


use spark\utils\Asserts;
use spark\utils\Collections;
use spark\utils\Objects;

class EngineConfig {


    private $namespace;
    private $hasMultipleNamespaces;

    /**
     * @var $configName - name used for selecting configuration
     */
    private $configName;

    private $beanCache;
    private $rootAppPath;

    private $vendor;


    public function __construct($params = array()) {
        Asserts::notNull($params["root"], "Engine configuration: did you forget root project path('s) field: 'root' e.g 'path'");
        Asserts::notNull($params["name"], "Engine configuration: did you forget about namespace('s) field: 'name' e.g 'spark' ");
        Asserts::notNull($params["configName"], "Engine configuration: did you forget about configuration field: 'configName' e.g 'development'");

        $this->rootAppPath = $params["root"];
        $this->namespace = $params["name"];
        $this->configName = $params["configName"];
        $this->beanCache = Collections::getValue($params, "beanCache", false);

        $this->hasMultipleNamespaces = Objects::isArray($this->namespace);
        $this->beanCache = Collections::getValue($params, "beanCache", false);
        $this->vendor = Collections::getValue($params, "vendor", array());
    }

    public function hasMultipleNamespaces() {
        ;
        return $this->hasMultipleNamespaces;
    }

    public function getRootNamespace() {
        if ($this->hasMultipleNamespaces()) {
            return $this->namespace[0];
        } else {
            return $this->namespace;
        }
    }

    /**
     * @return mixed
     */
    public function getConfigName() {
        return $this->configName;
    }

    public function isBeanCacheEnabled() {
        return $this->beanCache;
    }

    public function getRootAppPath() {
        return $this->rootAppPath;
    }

    public function getNamespaces() {
        if ($this->hasMultipleNamespaces()) {
            return $this->namespace;
        } else {
            return array($this->namespace);
        }
    }

    public function getVendors() {
        return $this->vendor;
    }


}