<?php

namespace Spark\Core\Service;

use Spark\Utils\FilterUtils;

class PropertyHelper {

    private $properties = array();

    public function __construct($array = null) {
        if (!is_null($array)) {
            $this->addAll($array);
        }
    }

    /**
     * @param $key
     * @param $value
     * @return $this PropertyHelper
     */
    public function add($key, $value) {
        $filteredKey = $this->filter($key);
        $filteredValue = $this->filter($value);

        $this->properties[$filteredKey] = $filteredValue;
        return $this;
    }

    /**
     * @param $array
     * @return $this PropertyHelper
     */
    public function addAll($array) {
        foreach ($array as $key => $value) {
            $this->add($key, $value);
        }
        return $this;
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

    /**
     *
     * @param $key
     * @return $this PropertyHelper
     */
    public function remove($key) {
        unset($this->properties[$key]);
        return $this;
    }

}
