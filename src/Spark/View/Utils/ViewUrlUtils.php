<?php

namespace Spark\View\Utils;

use Spark\Http\Request;
use Spark\Core\Routing\RequestData;
use Spark\Utils\Objects;
use Spark\Utils\StringUtils;

class ViewUrlUtils {

    /**
     *  app/view/{namespace}/{module+controlle-sub-dir}/{controller-name}/{action-name}
     *
     * @param  $request
     * @return string
     */
    public static function createFullViewPath(RequestData $request): string {
        return StringUtils::replace(StringUtils::join('/', array(
            self::createViewPathWithViewName($request)
        )), "\\", '/');
    }

    public static function createViewPathWithViewName(RequestData $request, $viewName = null): string {
        $viewName = Objects::isNull($viewName) ? $request->getMethodName() : $viewName;

        return StringUtils::join('/', array(
            strtolower($request->getNamespace()),
            strtolower($request->getModuleName()),
            strtolower($request->getControllerName()),
            str_replace('Action', '', $viewName)
        ));
    }
}