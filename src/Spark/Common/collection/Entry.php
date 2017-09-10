<?php


namespace Spark\Common\collection;


class Entry {

    private $key;
    private $value;

    /**
     * Entry constructor.
     * @param $key
     * @param $value
     */
    public function __construct($key, $value) {
        $this->key = $key;
        $this->value = $value;
    }

    /**
     * @return mixed
     */
    public function getKey() {
        return $this->key;
    }

    /**
     * @return mixed
     */
    public function getValue() {
        return $this->value;
    }



}