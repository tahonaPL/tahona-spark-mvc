<?php
/**
 * Created by PhpStorm.
 * User: primosz67
 * Date: 06.07.14
 * Time: 18:10
 */

namespace spark\view\plain;


use spark\common\exception\UnsupportedOperationException;
use spark\view\ViewModel;

class PlainViewModel extends ViewModel {
    private $content;

    function __construct($content) {
        $this->content = $content;
    }

    public function setContent($content) {
        $this->content = $content;
    }

    /**
     * @return mixed
     */
    public function getContent() {
        return $this->content;
    }

    public function add($key, $value) {
        //do nothing for framework additional elements
    }

    public function addAll($array) {
        //do nothing for framework additional elements
    }

    public function getParams() {
        throw new UnsupportedOperationException();
    }

    protected function filter($param) {
        throw new UnsupportedOperationException();
    }

    public function has($key) {
        throw new UnsupportedOperationException();
    }

    public function get($key) {
        throw new UnsupportedOperationException();
    }

    public function remove($key) {
        throw new UnsupportedOperationException();
    }


}