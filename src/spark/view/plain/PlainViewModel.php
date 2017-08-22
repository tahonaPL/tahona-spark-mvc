<?php
/**
 * Created by PhpStorm.
 * User: primosz67
 * Date: 06.07.14
 * Time: 18:10
 */

namespace spark\view\plain;


use spark\common\exception\UnsupportedOperationException;
use spark\http\Response;
use spark\view\ViewModel;

class PlainViewModel implements Response {
    private $content;

    public function __construct($content) {
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
}