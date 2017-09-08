<?php

namespace spark\view\utils;


use spark\http\Request;
use spark\core\routing\RequestData;
use spark\utils\Objects;
use spark\utils\StringUtils;

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