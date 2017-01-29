<?php
/**
 * Created by PhpStorm.
 * User: primosz67
 * Date: 09.10.14
 * Time: 19:25
 */

namespace spark\http;


class HttpCode {

    public static $OK; //200
    public static $CREATED; //201
    public static $BAD_REQUEST; //400
    public static $NOT_FOUND; //404
    public static $UNSUPPORTED_MEDIA_TYPE; //415

    private $code;
    private $message;

    public static function init() {
        self::$OK = new HttpCode(200, "OK");
        self::$CREATED = new HttpCode(201, "Created");
        self::$BAD_REQUEST = new HttpCode(400, "Bad Request");
        self::$NOT_FOUND = new HttpCode(404, "Not Found");
        self::$UNSUPPORTED_MEDIA_TYPE = new HttpCode(415, "Unsupported Media Type");
    }

    private function __construct($code, $message) {
        $this->code = $code;
        $this->message = $message;
    }

    /**
     * @return mixed
     */
    public function getCode() {
        return $this->code;
    }

    /**
     * @return mixed
     */
    public function getMessage() {
        return $this->message;
    }
}

HttpCode::init();

