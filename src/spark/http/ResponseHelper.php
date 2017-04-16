<?php
/**
 * Created by PhpStorm.
 * User: primosz67
 * Date: 09.10.14
 * Time: 19:24
 */

namespace spark\http;


use spark\common\data\ContentType;

class ResponseHelper {

    /**
     * @param HttpCode $code
     */
    public static function setCode(HttpCode $code) {
        http_response_code($code->getCode());
    }

    public static function setContentType(ContentType $contentType) {
        header('Content-Type: ' . $contentType->getType());
    }

    public static function setHeader($headerKey, $headerValue) {
        header($headerKey.":".$headerValue);
    }

} 