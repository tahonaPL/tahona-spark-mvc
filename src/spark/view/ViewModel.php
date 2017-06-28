<?php

namespace spark\view;

use spark\core\service\PropertyHelper;
use spark\utils\UrlUtils;

/**
 * Description of ViewModel
 *
 * @author primosz67
 */
class ViewModel extends PropertyHelper {

    const CLASS_NAME = "spark\\view\\ViewModel";
    private $redirect;
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
     * @param mixed $redirect
     */
    public function setRedirect($redirect) {
        $this->redirect = $redirect;
    }

    public function getRedirect() {
        $isRedirect = $this->isRedirect();
        if ($isRedirect) {
            return UrlUtils::appendParams($this->redirect, $this->getParams());
        }
        return $this->redirect;
    }

    /**
     * @deprecated
     * @param $url
     * @param array $arr
     * @return ViewModel
     */
    public static function createRedirectView($url, $arr = array()) {
        $viewModel = new ViewModel();
        $viewModel->addAll($arr);
        $viewModel->setRedirect($url);
        return $viewModel;
    }

    public function isRedirect() {
        return false == empty($this->redirect);
    }

    public static function createWithView($viewName) {
        $viewModel = new ViewModel();
        $viewModel->setViewName($viewName);
        return $viewModel;
    }
}
