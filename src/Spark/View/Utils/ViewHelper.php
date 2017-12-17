<?php
/**
 *
 *
 * Date: 01.02.17
 * Time: 21:14
 */

namespace Spark\View\Utils;


use Spark\Controller;
use Spark\Http\Request;
use Spark\View\Plain\RedirectViewHandler;
use Spark\View\Redirect\RedirectViewModel;
use Spark\View\ViewModel;

class ViewHelper {

    /**
     * @param $url
     * @param array $arr
     * @return RedirectViewModel
     */
    public static function createRedirectView($url, $arr = array()): RedirectViewModel {
        return new RedirectViewModel($url, $arr);
    }

    /**
     * @param $viewPath
     * @param array $arr
     * @return ViewModel
     */
    public static function view($viewPath, $arr = array()): ViewModel {
        return self::create()
            ->setViewName($viewPath)
            ->addAll($arr);
    }

    /**
     * @param Controller $controller
     * @param $viewName
     * @return ViewModel
     */
    public static function local(Controller $controller, $viewName): ViewModel {
        return self::localByRequest($controller->getRequest(), $viewName);
    }

    public static function localByRequest(Request $request, $viewName) : ViewModel{
        return self::create()
            ->setViewName(ViewUrlUtils::createViewPathWithViewName($request, $viewName));
    }


    public static function create(): ViewModel {
        return new ViewModel();
    }
}