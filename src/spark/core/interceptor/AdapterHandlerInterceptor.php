<?php
/**
 * Created by PhpStorm.
 * User: primosz67
 * Date: 05.02.17
 * Time: 11:33
 */

namespace spark\core\interceptor;


use spark\http\Request;
use spark\http\Response;
use spark\view\ViewModel;

class AdapterHandlerInterceptor implements HandlerInterceptor {

    public function preHandle(Request $request) {
        return true;
    }

    public function postHandle(Request $request, Response $response) {
        //do nothing
    }
}