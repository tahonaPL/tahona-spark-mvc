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
    private $rootAppPath;

    public function __construct($rootAppPath, $namespaces = array()) {
        Asserts::notNull($rootAppPath, "Engine configuration: did you forget root project path('s) field: 'root' e.g 'path'");

        $this->rootAppPath = $rootAppPath;
        $this->namespace = $namespaces;

        $this->hasMultipleNamespaces = Objects::isArray($this->namespace);
    }

    public function hasMultipleNamespaces() {
        return $this->hasMultipleNamespaces;
    }

    public function getRootNamespace() {
        if ($this->hasMultipleNamespaces()) {
            return $this->namespace[0];
        } else {
            return $this->namespace;
        }
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


}