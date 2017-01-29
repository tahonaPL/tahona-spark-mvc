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
        $redirect = $this->redirect;
        $isRedirect = $this->isRedirect();
        if ($isRedirect) {
            $redirect = UrlUtils::appendParams($redirect, $this->getParams());
        }
        return $redirect;
    }


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
