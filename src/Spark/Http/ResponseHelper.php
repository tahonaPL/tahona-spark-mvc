<?php
/**
 *
 *
 * Date: 09.10.14
 * Time: 19:24
 */

namespace Spark\Http;


use Spark\Common\Data\ContentType;

class ResponseHelper {

    /**
     * @param int $code
     */
    public static function setCode(int $code) {
        http_response_code($code);
    }

    public static function setContentType($contentType) {
        header('Content-Type: ' . $contentType);
    }

    public static function setHeader($headerKey, $headerValue) {
        header($headerKey . ":" . $headerValue);
    }

} 