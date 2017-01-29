<?php

namespace spark\utils;

use spark\common\Optional;
use spark\http\utils\RequestUtils;
use spark\utils\Asserts;
use spark\utils\Collections;
use spark\utils\StringUtils;

class UrlUtils {

    public static $webPage = null;

    public static function isResource($urlName, $array) {
        foreach ($array as $value) {
            if (strpos($urlName, $value) > 0) {
                return true;
            }
        }
        return false;
    }

    /**
     * Returning second part of url: /user/action
     * @param null $host
     * @return mixed
     */
    public static function getPathInfo($host = null) {

        $actualLink = self::getUrl();
        $host = self::removeHttpTags($host);
        $actualLink = self::removeHttpTags($actualLink);

        if (isset($host)) {
            $urlParts = StringUtils::split($actualLink, $host);
            Asserts::checkArgument(Collections::size($urlParts) == 2, "Wrong url setup? Check config. Looking for host: " . $host);

            $urlVal = $urlParts[1];
        } else {
            if (isset($_SERVER["PATH_INFO"])) {
                $urlVal = $_SERVER["PATH_INFO"];
            } else {
                $urlName = str_replace("index.php", "", $_SERVER["SCRIPT_NAME"]);
                $urlVal = str_replace($urlName, "/", $_SERVER["REQUEST_URI"]);
            }
        }
        return str_replace("//", "/", $urlVal);
    }

    public static function cleanPath($viewPath) {
        if (self::hasScheme($viewPath)) {
            $urlParts = StringUtils::split($viewPath, "://");
            return $urlParts[0] . "://" . str_replace("//", "/", $urlParts[1]);
        } else {
            return str_replace("//", "/", $viewPath);
        }
    }

    /**
     * @deprecated use appendParams
     * @param $params
     * @return string
     */
    public static function getParseParamsToQuery($params) {
        $q = "" . http_build_query($params);
        return $q;
    }

    /**
     * @param $url need to be passed ( e.g.from Config (web.page) - tahona.pl)
     * @param $params
     * @return string
     */
    public static function appendParams($url, $params = array()) {
        $paramsQuery = "";
        if (Collections::isNotEmpty($params)) {
            $parsedParams = self::getParseParamsToQuery($params);
            $questionMark = StringUtils::contains($url, "?") ? "" : "?";
            $paramsQuery = $questionMark . $parsedParams;
        }
        return self::cleanPath($url) . $paramsQuery;
    }

    public static function wrapHttpIfNeeded($link) {
        $scheme = "http";
        return self::wrapRequestSchemeIfNeeded($link, $scheme);
    }

    /**
     * @param $host
     * @return mixed
     */
    private static function removeHttpTags($host) {
        return Optional::ofNullable($host)
            ->map(StringUtils::mapReplace("http://", ""))
            ->map(StringUtils::mapReplace("https://", ""))
            ->map(StringUtils::mapReplace("//", "/"))
            ->getOrNull();
    }

    /**
     * build full path with params. If "path" start with https or http returned is "path" value.
     * @param $path
     * @param array $params
     * @return string
     */
    public static function getPath($path, $params = array()) {
        if (strpos($path, "http:") === 0 || StringUtils::startsWith($path, "https")) {
            return $path;
        } else {
            $url = self::getHost();
            return self::appendParams($url . $path, $params);
        }
    }

    /**
     * Get full url
     *
     * @return string
     */
    private static function getUrl() {
        return RequestUtils::getRequestScheme() . "://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"] . "";
    }

    /**
     * build Host based on Config and "current" request scheme if needed
     * @return string
     */
    public static function getHost() {
        $fullUrl = self::getUrl();
        if (StringUtils::contains($fullUrl, self::$webPage)) {
            $parts = StringUtils::split($fullUrl, self::$webPage);
            $prefix = self::removeHttpTags($parts[0]);
            $host = StringUtils::join("", array(
                $prefix, self::$webPage
            ));

        } else {
            $host = self::$webPage;
        }

        return UrlUtils::wrapRequestSchemeIfNeeded($host, RequestUtils::getRequestScheme());
    }

    public static function setWebPage($webPage) {
        self::$webPage = $webPage;
    }

    /**
     * @param $link
     * @param $scheme
     * @return string
     */
    public static function wrapRequestSchemeIfNeeded($link, $scheme) {
        if (self::hasScheme($link)) {
            return $link;
        } else {
            return $scheme . "://" . $link;
        }
    }

    /**
     * @param $link
     * @return bool
     */
    private static function hasScheme($link) {
        return strpos($link, "http://") === 0 || strpos($link, "https://") === 0 || empty($link);
    }

    public static function getCurrentUrl() {
        return self::getUrl();
    }

    /**
     * @param $suffixUrlPart
     * @return string
     */
    private static function removeLastCharacterIfNeeded($suffixUrlPart) {
        $suffix = $suffixUrlPart;
        $lastChar = StringUtils::sub($suffix, -1, 1);
        return $lastChar === "/" ? StringUtils::sub($suffix, 0, strlen($suffix) - 1) : $suffix;
    }

}
