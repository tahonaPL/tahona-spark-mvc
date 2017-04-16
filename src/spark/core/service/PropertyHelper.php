<?php

namespace spark\core\service;

use spark\utils\FilterUtils;

class PropertyHelper {

    private $properties = array();

    public function __construct($array = null) {
        if (false == is_null($array)) {
            $this->addAll($array);
        }
    }

    public function add($key, $value) {
        $filteredKey = $this->filter($key);
        $filteredValue = $this->filter($value);

        $this->properties[$filteredKey] = $filteredValue;
    }

    public function addAll($array) {
        foreach ($array as $key => $value) {
            $this->add($key, $value);
        }
    }

    public function getParams() {
        return $this->properties;
    }

    protected function filter($param) {
        return FilterUtils::filterVariable($param);
    }

    public function has($key) {
        return isset($this->properties[$key]);
    }

    public function get($key) {
        return $this->properties[$key];
    }

    public function remove($key) {
        unset($this->properties[$key]);
    }

}
