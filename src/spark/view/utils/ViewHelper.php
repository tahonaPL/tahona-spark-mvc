<?php
/**
 * Created by PhpStorm.
 * User: primosz67
 * Date: 01.02.17
 * Time: 21:14
 */

namespace spark\view\utils;


use spark\view\ViewModel;

class ViewHelper {

    /**
     * @param $url
     * @param array $arr
     * @return ViewModel
     */
    public static function createRedirectView($url, $arr= array()) {
        $viewModel = new ViewModel();
        $viewModel->addAll($arr);
        $viewModel->setRedirect($url);
        return $viewModel;
    }
}