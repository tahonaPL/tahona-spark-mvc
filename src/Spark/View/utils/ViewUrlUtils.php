<?php

namespace Spark\View\utils;


use Spark\Http\Request;
use Spark\Core\routing\RequestData;
use Spark\Utils\Objects;
use Spark\Utils\StringUtils;

class ViewUrlUtils {

    /**
     *  app/view/{namespace}/{module+controlle-sub-dir}/{controller-name}/{action-name}
     *
     * @param  $request
     */
    public static function createFullViewPath(RequestData $request) {
        return StringUtils::join("/", array(
            self::createViewPathWithViewName($request)
        ));
    }

    public static function createViewPathWithViewName(RequestData $request, $viewName = null) {
        $viewName = Objects::isNull($viewName) ? $request->getMethodName() : $viewName;

        return StringUtils::join("/", array(
            $request->getNamespace(),
            ($request->getModuleName()),
            strtolower($request->getControllerName()),
            str_replace("Action", "", $viewName)
        ));
    }


} 