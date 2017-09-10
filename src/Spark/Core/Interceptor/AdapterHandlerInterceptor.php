<?php
/**
 * Created by PhpStorm.
 * User: primosz67
 * Date: 05.02.17
 * Time: 11:33
 */

namespace Spark\Core\Interceptor;


use Spark\Http\Request;
use Spark\Http\Response;
use Spark\View\ViewModel;

class AdapterHandlerInterceptor implements HandlerInterceptor {

    public function preHandle(Request $request) {
        return true;
    }

    public function postHandle(Request $request, Response $response) {
        //do nothing
    }
}