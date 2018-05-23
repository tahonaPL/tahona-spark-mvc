<?php

namespace Spark\Utils;

use Spark\Common\Optional;
use Spark\Http\Request;
use Spark\Http\Utils\RequestUtils;
use Spark\Utils\Asserts;
use Spark\Utils\Collections;
use Spark\Utils\StringUtils;

class UrlUtils {
    const HTTPS = 'https';
    const HTTP = 'http';

    /**
     * @var string - cached url
     */
    private static $url;
    private static $pathInfo;

    private static $scheme;

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
     * @return mixed
     * @internal param null $host
     */
    public static function getPathInfo() {

        if (!empty(self::$pathInfo)) {
            return self::$pathInfo;
        }

        $actualLink = self::getUrl();
        $host = self::getHost();

        if ($host !== null) {
            $urlParts = StringUtils::split($actualLink, $host);
            Asserts::checkArgument(Collections::size($urlParts) >= 2, 'Wrong url setup? Check config. Looking for host: ' . $host);

            $urlVal = $urlParts[1];
        } else {
            if (isset($_SERVER['PATH_INFO'])) {
                $urlVal = $_SERVER['PATH_INFO'];
            } else {
                $urlName = str_replace('index.php', '', $_SERVER['SCRIPT_NAME']);
                $urlVal = str_replace($urlName, '/', $_SERVER['REQUEST_URI']);
            }
        }
        self::$pathInfo = str_replace('//', '/', $urlVal);
        return self::$pathInfo;
    }

    public static function cleanPath($viewPath) {
        if (self::hasScheme($viewPath)) {
            $urlParts = StringUtils::split($viewPath, '://');
            return $urlParts[0] . '://' . str_replace('//', '/', $urlParts[1]);
        }

        return str_replace('//', '/', $viewPath);
    }


    /**
     * @param $url need to be passed ( e.g.from Config (web.page) - tahona.pl)
     * @param $params
     * @return string
     */
    public static function appendParams($url, $params = array()) {
        return self::cleanPath($url) . self::getParamsAsQuery($params);
    }

    public static function getParamsAsQuery($params = []) {
        if (Collections::isNotEmpty($params)) {
            $parsedParams = http_build_query($params);
            if (StringUtils::isNotBlank($parsedParams)) {
                return '?' . $parsedParams;
            }
        }
        return '';
    }


    public static function wrapHttpIfNeeded($link) {
        $scheme = self::HTTP;
        return self::wrapRequestSchemeIfNeeded($link, $scheme);
    }

    /**
     * @param $host
     * @return mixed
     */
    private static function removeHttpTags($host) {

        return Optional::ofNullable($host)
            ->map(StringFunctions::replace('http://', ''))
            ->map(StringFunctions::replace('https://', ''))
            ->map(StringFunctions::replace('//', '/'))
            ->getOrNull();
    }

    /**
     * build full path with params. If "path" start with https or http returned is "path" value.
     */
    public static function getPath(string $path, $params = array()): string {
        if (strpos($path, 'http:') === 0 || StringUtils::startsWith($path, self::HTTPS)) {
            return $path;
        } else {
            $url = self::getSite();
            return self::appendParams($url . $path, $params);
        }
    }

    /**
     * Get full url
     */
    private static function getUrl(): string {
        if (self::$url === null) {
            self::$url = RequestUtils::getSite() . $_SERVER['REQUEST_URI'] . '';
        }

        return self::$url;
    }

    /**
     * build Host based on Config and "current" request scheme if needed
     * @return string
     */
    public static function getHost() {
        return $_SERVER['HTTP_HOST'];
    }


    /**
     * @param $link
     * @param $scheme
     * @return string
     */
    public static function wrapRequestSchemeIfNeeded($link, $scheme) {
        if (self::hasScheme($link)) {
            return $link;
        }

        return $scheme . '://' . $link;
    }

    /**
     * @param $link
     * @return bool
     */
    private static function hasScheme($link) {
        return strpos($link, 'http://') === 0 || strpos($link, 'https://') === 0 || empty($link);
    }

    public static function getCurrentUrl() {
        return self::getUrl();
    }

    public static function getSite(): string {
        return RequestUtils::getSite();
    }

    /**
     * @param $suffixUrlPart
     * @return string
     */
    private static function removeLastCharacterIfNeeded($suffixUrlPart) {
        $suffix = $suffixUrlPart;
        $lastChar = StringUtils::substring($suffix, -1, 1);
        return $lastChar === '/' ? StringUtils::substring($suffix, 0, strlen($suffix) - 1) : $suffix;
    }

}
