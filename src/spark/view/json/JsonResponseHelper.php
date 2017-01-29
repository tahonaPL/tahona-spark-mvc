<?php
/**
 * Created by PhpStorm.
 * User: primosz67
 * Date: 23.08.14
 * Time: 16:42
 */

namespace spark\view\json;


class JsonResponseHelper {

    public static function responseError() {
        return array("RESPONSE" => "ERROR");
    }

    public static function responseOK() {
        return array("RESPONSE" => "OK");
    }
}