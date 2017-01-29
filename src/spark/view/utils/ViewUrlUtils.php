<?php

namespace spark\view\utils;


use spark\http\Request;
use spark\utils\Objects;
use spark\utils\StringUtils;

class ViewUrlUtils {

    /**
     *  app/view/{namespace}/{module+controlle-sub-dir}/{controller-name}/{action-name}
     *
     * @param  $request
     */
    public static function createFullViewPath(Request $request) {
        return StringUtils::join("/", array(
            __ROOT__."app",
            "view",
            self::createViewPathWithViewName($request)
        ));
    }

    public static function createViewPathWithViewName(Request $request, $viewName = null) {
        $viewName = Objects::isNull($viewName) ? $request->getMethodName() : $viewName;

        return StringUtils::join("/", array(
            $request->getNamespace(),
            ($request->getModuleName().$request->getControllerPrefix()),
            strtolower($request->getControllerName()),
            str_replace("Action", "", $viewName)
        ));
    }


} 