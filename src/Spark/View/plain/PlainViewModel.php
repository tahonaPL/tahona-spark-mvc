<?php
/**
 * Created by PhpStorm.
 * User: primosz67
 * Date: 06.07.14
 * Time: 18:10
 */

namespace Spark\View\plain;


use Spark\Common\exception\UnsupportedOperationException;
use Spark\Http\Response;
use Spark\View\ViewModel;

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