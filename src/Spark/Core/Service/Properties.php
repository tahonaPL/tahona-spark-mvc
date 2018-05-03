<?php

namespace Spark\Core\Service;

use Spark\Utils\FilterUtils;
use Spark\View\ViewModel;

trait Properties {

    private $properties = array();


    public function add($key, $value): self {
        $filteredKey = $this->filter($key);
        $filteredValue = $this->filter($value);

        $this->properties[$filteredKey] = $filteredValue;
        return $this;
    }

    public function addAll($array): self {
        foreach ($array as $key => $value) {
            $this->add($key, $value);
        }
        return $this;
    }

    public function getParams(): array {
        return $this->properties;
    }

    protected function filter($param) {
        return FilterUtils::filterVariable($param);
    }

    public function has($key): bool {
        return isset($this->properties[$key]);
    }

    public function get($key) {
        return $this->properties[$key];
    }


    public function remove($key): self {
        unset($this->properties[$key]);
        return $this;
    }

}
