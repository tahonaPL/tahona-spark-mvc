<?php

namespace spark\view;

use spark\core\service\PropertyHelper;
use spark\http\Response;
use spark\utils\UrlUtils;
use spark\view\redirect\RedirectViewModel;

/**
 * Description of ViewModel
 *
 * @author primosz67
 */
class ViewModel extends PropertyHelper implements Response {

    private $viewName;

    /**
     * @param $name
     * @return $this ViewModel
     */
    public function setViewName($name) {
        $this->viewName = $name;
        return $this;
    }

    public function getViewName() {
        return $this->viewName;
    }


    /**
     * @deprecated
     * @param $url
     * @param array $arr
     * @return ViewModel
     */
    public static function createRedirectView($url, $arr = array()) {
        return new RedirectViewModel($url, $arr);
    }

    public static function createWithView($viewName) {
        $viewModel = new ViewModel();
        $viewModel->setViewName($viewName);
        return $viewModel;
    }
}
