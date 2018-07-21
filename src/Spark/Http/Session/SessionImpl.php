<?php
/**
 * Date: 21.07.18
 * Time: 15:11
 */

namespace Spark\Http\Session;


use Spark\Core\Service\PropertyHelper;
use Spark\Http\Session;

class SessionImpl implements Session {


    private $data;

    public function __construct() {
        $this->data = new PropertyHelper();
    }

    public function add($key, $value): Session {
        $this->data->add($key, $value);
        return $this;
    }

    public function addAll(array $array): Session {
        $this->data->addAll($array);
        return $this;
    }

    public function getParams(): array {
        return $this->data->getParams();
    }

    public function has($key): bool {
        return $this->data->has($key);
    }

    public function get($key) {
        return $this->data->get($key);
    }

    public function remove($key): Session {
        $this->remove($key);
        return $this;
    }
}