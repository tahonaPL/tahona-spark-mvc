<?php
/**
 * Created by PhpStorm.
 * User: primosz67
 * Date: 06.07.14
 * Time: 18:10
 */

namespace Spark\View\Redirect;


use Spark\Http\Response;

class RedirectViewModel implements Response {

    private $params = array();
    private $url;


    public function __construct($url, $array = array()) {
        $this->params = $array;
        $this->url = $url;
    }

    /**
     * @return array
     */
    public function getParams() {
        return $this->params;
    }

    /**
     * @return mixed
     */
    public function getUrl() {
        return $this->url;
    }


}