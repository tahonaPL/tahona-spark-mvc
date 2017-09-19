<?php
/**
 *
 *
 * Date: 02.02.17
 * Time: 08:05
 */

namespace Spark\Http;


use Spark\Utils\StringUtils;

class HttpRequestMethod {
    const GET = array("GET", 0);
    const POST = array("POST", 10);
    const PUT = array("PUT", 100);
    const DELETE = array("DELETE", 1000);
    const HEAD = array("HEAD", 1000);
    const CONNECT = array("CONNECT", 5);
    const TRACE = array("TRACE", 6);
    const PATCH = array("PATCH", 7);

    public static function getCode($method) {
        $arr = array(
            self::GET,
            self::POST,
            self::PUT,
            self::DELETE,
            self::HEAD,
            self::CONNECT,
            self::TRACE,
            self::PATCH
        );

        foreach ($arr as $methodeReq) {
            if (StringUtils::equals($methodeReq[0], strtoupper($method))) {
                return $methodeReq[1];
            }
        }
        return 1;
    }
}