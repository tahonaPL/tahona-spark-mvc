<?php
/**
 * Created by PhpStorm.
 * User: primosz67
 * Date: 01.02.17
 * Time: 21:14
 */

namespace spark\view\utils;


use spark\Controller;
use spark\view\plain\RedirectViewHandler;
use spark\view\redirect\RedirectViewModel;
use spark\view\ViewModel;

class ViewHelper {

    /**
     * @param $url
     * @param array $arr
     * @return ViewModel
     */
    public static function createRedirectView($url, $arr = array()) {
        return new RedirectViewModel($url, $arr);
    }

    /**
     * @param $viewPath
     * @param array $arr
     * @return ViewModel
     */
    public static function view($viewPath, $arr = array()) {
        $viewModel = new ViewModel();
        $viewModel->setViewName($viewPath);
        $viewModel->addAll($arr);
        return $viewModel;
    }

    /**
     * @param Controller $controller
     * @param $viewName
     * @return ViewModel
     */
    public static function local(Controller $controller, $viewName) {
        $viewModel = new ViewModel();
        return $viewModel->setViewName(ViewUrlUtils::createViewPathWithViewName($controller->getRequest(), $viewName));
    }
}