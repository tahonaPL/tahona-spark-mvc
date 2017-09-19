<?php
/**
 *
 *
 * Date: 05.02.17
 * Time: 11:33
 */

namespace Spark\Core\Interceptor;


use Spark\Http\Request;
use Spark\Http\Response;
use Spark\View\ViewModel;

interface HandlerInterceptor {

    /**
     *
     * true if the execution chain should proceed with the next interceptor or the handler itself. Else, DispatcherServlet assumes that this interceptor has already dealt with the response itself.
     *
     * @param Request $request
     * @return boolean
     */
    public function preHandle(Request $request);

    /**
     * @param Request $request
     * @param $response
     *
     */
    public function postHandle(Request $request, Response $response);


}