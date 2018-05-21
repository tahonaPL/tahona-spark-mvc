<?php

namespace Spark\View;

use Spark\Core\Service\Properties;
use Spark\Core\Service\PropertyHelper;
use Spark\Http\Response;
use Spark\Utils\UrlUtils;
use Spark\View\Redirect\RedirectViewModel;

/**
 * Description of ViewModel
 *
 * @author primosz67
 */
class ViewModel implements Response {
    use Properties;

    private $viewName;

    public function __construct($params=[]) {
        $this->addAll($params);
    }


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
