<?php
/**
 * Created by PhpStorm.
 * User: primosz67
 * Date: 22.06.15
 * Time: 20:18
 */

namespace spark\upload;


use spark\utils\MathUtils;

class FileSize {

    const KB = 1024;
    const MB = 1048576;

    public static function  getMB(FileObject $fo, $decimal = 2) {
        $size = $fo->getSize();
        return self::getSizeAsMB($size, $decimal);
    }

    public static function  getKB(FileObject $fo, $decimal = 2) {
        $size = $fo->getSize();
        return MathUtils::formatNumber($size / self::KB, $decimal);
    }

    /**
     * @param $decimal
     * @param $size
     */
    public static function getSizeAsMB($size, $decimal = 2) {
        return (float) MathUtils::formatNumber(($size / self::MB), $decimal);
    }
} 