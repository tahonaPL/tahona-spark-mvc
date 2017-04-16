<?php
/**
 * Created by PhpStorm.
 * User: primosz67
 * Date: 05.02.17
 * Time: 11:33
 */

namespace spark\core\interceptor;


use spark\http\Request;
use spark\view\ViewModel;

interface HandlerInterceptor {
    const CLASS_NAME = "spark\\core\\interceptor\\HandlerInterceptor";

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
     * @param $viewModel
     *
     */
    public function postHandle(Request $request, ViewModel $viewModel);


}