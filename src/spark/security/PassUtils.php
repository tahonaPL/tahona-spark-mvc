<?php
/**
 * Created by PhpStorm.
 * User: primosz67
 * Date: 21.06.15
 * Time: 20:44
 */

namespace spark\security;


use spark\utils\Asserts;
use spark\utils\ValidatorUtils;
use spark\utils\StringUtils;

class PassUtils {

    private static $STRING_NUMBERS_TEMPLATE = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    private static $NUMBERS_TEMPLATE = '0123456789';

    const BLOWFISH_SALT_PREFIX = "$2a$12$";

    public static function  genCode($length = 8) {
        $characters = self::$STRING_NUMBERS_TEMPLATE;
        return self::randomStringFromCharacterSet($length, $characters);
    }

    public static function genNumericCode($length = 8) {
        $characters = self::$NUMBERS_TEMPLATE ;
        return self::randomStringFromCharacterSet($length, $characters);
    }

    public static function generatePassword($pass, $salt) {
        Asserts::checkState(strlen($salt) >= 64, "Salt should be longer");
        Asserts::checkState(ValidatorUtils::checkLength($pass, 8), "Pass should be longer than 8");

        return self::passwordHash($pass.$salt, $salt);
    }

    private static function passwordHash($saltedPassword, $salt) {
        return crypt($saltedPassword, self::BLOWFISH_SALT_PREFIX.$salt."$");
    }

    public static function generateSalt($length=64) {
        Asserts::checkState($length> 63, "Salt needs to be longer than 63 letters.");
        return bin2hex(mcrypt_create_iv($length, MCRYPT_RAND));
    }

    public static function verify($password, $salt, $hash) {

        return StringUtils::equals(self::generatePassword($password, $salt), $hash);
//        return password_verify($password.$salt, $hash);
    }

    /**
     * @param $length
     * @param $characters
     * @return string
     */
    private static function randomStringFromCharacterSet($length, $characters) {
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }


}