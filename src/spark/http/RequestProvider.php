<?php
/**
 * Created by PhpStorm.
 * User: primosz67
 * Date: 03.04.15
 * Time: 02:47
 */

namespace spark\http;


use spark\http\Request;

class RequestProvider {

    const NAME = "requestProvider";

    /**
     * @var Request
     */
    private $request;

    /**
     * @param \spark\http\Request $request
     */
    public function setRequest($request) {
        $this->request = $request;
    }

    /**
     * @return \spark\http\Request
     */
    public function getRequest() {
        return $this->request;
    }




} 