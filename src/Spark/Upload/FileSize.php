<?php
/**
 *
 *
 * Date: 22.06.15
 * Time: 20:18
 */

namespace Spark\Upload;


use Spark\Utils\MathUtils;

class FileSize {

    const KB = 1024;
    const MB = 1048576;

    public static function getMB(FileObject $fo, $decimal = 2) {
        $size = $fo->getSize();
        return self::getSizeAsMB($size, $decimal);
    }

    public static function getKB(FileObject $fo, $decimal = 2): float {
        $size = $fo->getSize();
        return (float) MathUtils::formatNumber($size / self::KB, $decimal);
    }

    /**
     * @param $decimal
     * @param $size
     */
    public static function getSizeAsMB($size, $decimal = 2): float {
        $floatSize = (float)$size;
        return (float)MathUtils::formatNumber($floatSize / self::MB, $decimal);
    }

    public static function getSizeAsKB($size) {
        return (float)MathUtils::formatNumber(($size / self::KB), 2);
    }
} 