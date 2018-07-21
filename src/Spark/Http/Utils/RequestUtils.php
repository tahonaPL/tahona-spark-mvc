<?php

namespace Spark\Http\Utils;

use Doctrine\Common\Collections\Collection;
use Spark\Common\Optional;
use Spark\Http\Utils\CookieUtils;
use Spark\Http\Session;
use Spark\Utils\Asserts;
use Spark\Utils\Collections;
use Spark\Utils\FilterUtils;
use Spark\Utils\Functions;
use Spark\Utils\Objects;
use Spark\Utils\Predicates;

class RequestUtils {

    public const POST_REQUEST_METHOD = 'POST';

    public const HTTPS = 'https';
    public const HTTP = 'http';

    private static $isSSL;

    public static function isPost(): bool {
        return $_SERVER['REQUEST_METHOD'] === self::POST_REQUEST_METHOD;
    }

    public static function getParam($name, $escape = true) {
        if (isset($_POST[$name])) {
            $param = $_POST[$name];
            return $escape ? FilterUtils::filterVariable($param) : $param;
        }

        if (isset($_GET[$name])) {
            $param = urldecode($_GET[$name]);
            return $escape ? FilterUtils::filterVariable($param) : $param;
        }
        return null;
    }

    /**
     * @return array
     */
    public static function getPostParams() {
        $params = $_POST;
        return self::filterParams($params);
    }

    public static function getAllFilesParams() {
        return $_FILES;
    }

    public static function isFile(): bool {
        return false == empty($_FILES);
    }

    public static function getFileParams($name) {
        if (isset($_FILES[$name])) {
            return $_FILES[$name];
        }
        return null;
    }

    public static function redirect($url, $statusCode = 303): void {
        if (false === self::hasHttpPrefix($url) || false === self::hasHttpsPrefix($url)) {
            $url = RequestUtils::getRequestScheme() . '://' . $url;
        }

        header('Location: ' . $url, true, $statusCode);
        die();
    }

    /**
     * @param $url
     * @return bool
     */
    private static function hasHttpPrefix($url): bool {
        return self::hasPrefix($url, self::HTTP);
    }

    private static function hasHttpsPrefix($url): bool {
        return self::hasPrefix($url, self::HTTPS);
    }

    /**
     * @param $url
     * @param $prefix
     * @return bool
     */
    private static function hasPrefix($url, $prefix): bool {
        return strpos($url, $prefix) >= 0
            || strpos($url, self::HTTP) >= 0;
    }

    public static function isSSL(): bool {
        if (Objects::isNull(self::$isSSL)) {
            self::$isSSL = self::isForwardedHttps()
                || self::isHttpsOn()
                || self::isRequestSchemeHttps()
                || self::isServerPort433();
        }
        return self::$isSSL;

    }

    public static function getRequestScheme() {
        $isSSL = self::isSSL();
        if ($isSSL) {
            return self::HTTPS;
        } else {
            return self::HTTP;
        }
    }

    public static function setCookie($key, $value) {
        setcookie($key, $value);
    }

    public static function getRequestIp() {
        if (Collections::hasKey($_SERVER, 'HTTP_CLIENT_IP')) {
            return $_SERVER['HTTP_CLIENT_IP'];
        } else if (Collections::hasKey($_SERVER, 'HTTP_X_FORWARDED_FOR'))
            $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        else if (Collections::hasKey($_SERVER, 'HTTP_X_FORWARDED'))
            $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
        else if (Collections::hasKey($_SERVER, 'HTTP_FORWARDED_FOR'))
            $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
        else if (Collections::hasKey($_SERVER, 'HTTP_FORWARDED'))
            $ipaddress = $_SERVER['HTTP_FORWARDED'];
        else if (Collections::hasKey($_SERVER, 'REMOTE_ADDR'))
            $ipaddress = $_SERVER['REMOTE_ADDR'];
        else
            $ipaddress = 'UNKNOWN';

        return $ipaddress;
    }

    public static function getGetParams() {
        return self::filterParams($_GET);
    }

    /**
     * @param $params
     * @return array
     */
    protected static function filterParams($params) {
        $variables = array();
        foreach ($params as $key => $variable) {
            $variables[$key] = FilterUtils::filterVariable($variable);
        }

        return $variables;
    }

    public static function getMethod() {
        return $_SERVER['REQUEST_METHOD'];
    }

    public static function getHeaders() {
        if (!\function_exists('getallheaders')) {
            return self::getallheaders();
        }
        return \getallheaders();
    }

    /**
     * Workaround for FastCGI
     * @return array
     */
    private static function getallheaders() {
        if (!\is_array($_SERVER)) {
            return array();
        }

        $headers = array();
        foreach ($_SERVER as $name => $value) {
            if (0 === strpos($name, 'HTTP_')) {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }
        return $headers;
    }

    public static function getBody() {
        $entityBody = stream_get_contents(fopen('php://input', 'r'));
        return $entityBody ? $entityBody : null;
    }

    public static function getSite(): string {
        return RequestUtils::getRequestScheme() . '://' . $_SERVER['HTTP_HOST'];
    }

    /**
     * @return bool
     */
    private static function isForwardedHttps(): bool {
        return (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && Collections::getValue($_SERVER, 'HTTP_X_FORWARDED_PROTO') === self::HTTPS);
    }

    /**
     * @return bool
     */
    private static function isHttpsOn(): bool {
        return (isset($_SERVER['HTTPS']) && ('on' === strtolower($_SERVER['HTTPS']) || '1' == $_SERVER['HTTPS']));
    }

    /**
     * @return bool
     */
    private static function isServerPort433(): bool {
        return (isset($_SERVER['SERVER_PORT']) && ('443' == $_SERVER['SERVER_PORT']));
    }

    private static function isRequestSchemeHttps() {
        return isset($_SERVER['REQUEST_SCHEME']) && $_SERVER['REQUEST_SCHEME'] === self::HTTPS;
    }
}
