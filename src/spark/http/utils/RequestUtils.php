<?php

namespace spark\http\utils;

use Doctrine\Common\Collections\Collection;
use spark\common\Optional;
use spark\http\utils\CookieUtils;
use spark\http\Session;
use spark\utils\Collections;
use spark\utils\FilterUtils;
use spark\utils\Functions;
use spark\utils\Objects;
use spark\utils\Predicates;

class RequestUtils {

    const SESSION_NAME = "SESSID"; //AUTOMATICALLY IS PHPSESSID
    const POST_REQUEST_METHOD = 'POST';

    public static function isPost() {
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

    public static function isFile() {
        return false == empty($_FILES);
    }

    public static function getFileParams($name) {
        if (isset($_FILES[$name])) {
            return $_FILES[$name];
        }
        return null;
    }

    public static function getOrCreateSession() {
        //move to sessionUtils or something
        if (false == isset($_SESSION)) {
            session_name(self::SESSION_NAME);
            session_start();
        }

        if (false === Collections::hasKey($_SESSION, "spark_session")) {
            $_SESSION["spark_session"] = new Session();
        }
        return $_SESSION["spark_session"];
    }


    public static function getSession() {
        if (CookieUtils::hasCookie(self::SESSION_NAME)) {
            return self::getOrCreateSession();
        } else {
            return new Session();

        }
    }

    public static function redirect($url, $statusCode = 303) {
        if (false == self::hasHttpPrefix($url) || false == self::hasHttpsPrefix($url)) {
            $url = RequestUtils::getRequestScheme() . "://" . $url;
        }

        header('Location: ' . $url, true, $statusCode);
        die();
    }

    /**
     * @param $url
     * @return bool
     */
    private static function hasHttpPrefix($url) {
        return self::hasPrefix($url, "http");
    }

    private static function hasHttpsPrefix($url) {
        return self::hasPrefix($url, "https");
    }

    /**
     * @param $url
     * @param $prefix
     * @return bool
     */
    private static function hasPrefix($url, $prefix) {
        return strpos($url, $prefix) >= 0
        || strpos($url, "http") >= 0;
    }

    public static function isSSL() {
        if (isset($_SERVER['HTTPS'])) {
            if ('on' == strtolower($_SERVER['HTTPS']))
                return true;
            if ('1' == $_SERVER['HTTPS'])
                return true;
        } elseif (isset($_SERVER['SERVER_PORT']) && ('443' == $_SERVER['SERVER_PORT'])) {
            return true;
        }
        return false;
    }

    public static function getRequestScheme() {
        $isSSL = self::isSSL();
        if ($isSSL) {
            return "https";
        } else {
            return "http";
        }
    }

    public static function setCookie($key, $value) {
        setcookie($key, $value);
    }

    public static function getRequestIp() {
        if (Collections::hasKey($_SERVER, "HTTP_CLIENT_IP")) {
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
}
