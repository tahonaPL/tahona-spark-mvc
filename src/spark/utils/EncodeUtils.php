<?php

namespace spark\utils;

class EncodeUtils {

    public static function encodeUTF_8($arrayToEncode) {
        $resultArray = array();
        foreach ($arrayToEncode as $key => $value) {
            $resultArray[$key] = utf8_encode($value);
        }
        return $resultArray;
    }

}
