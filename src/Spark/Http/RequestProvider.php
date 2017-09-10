<?php
/**
 * Created by PhpStorm.
 * User: primosz67
 * Date: 03.04.15
 * Time: 02:47
 */

namespace Spark\Http;


use Spark\Core\Routing\RequestData;
use Spark\Http\Request;

class RequestProvider {

    const NAME = "requestProvider";

    /**
     * @var RequestData
     */
    private $request;

    /**
     * @param \Spark\Http\Request $request
     */
    public function setRequest($request) {
        $this->request = $request;
    }

    /**
     * @return \Spark\Http\Request
     */
    public function getRequest() {
        return $this->request;
    }





} 