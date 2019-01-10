<?php
/**
 * Date: 10.01.19
 * Time: 09:09
 */

namespace Spark\Core\Processor\Loader;


use Spark\Common\Optional;
use Spark\Utils\FileUtils;
use Spark\Utils\StringFunctions;
use Spark\Utils\StringUtils;

class StaticClassFactory {


    public static function createClass($name, $all) {
        $content = Optional::of(self::FILE_TEMPLATE)
            ->map(StringFunctions::replace('{CLASS_NAME}', $name))
            ->map(StringFunctions::replace('{OBJECT}',
                StringUtils::replace(serialize($all), "'", "\'")))
            ->get();

        FileUtils::writeToFile($content, self::getFilePath($name), true);

        return $all;
    }


    private const FILE_TEMPLATE = '<?php
namespace context;


class {CLASS_NAME} {

    private $object;

    public function __construct() {
        $this->object = unserialize(\'{OBJECT}\');
    }
    
    public function getObject() {
        return $this->object;
    }
}';

    private static function getFilePath($name) {
        return __ROOT__ . "app/src/context/$name.php";

    }

    public static function isExist($name) {
        return file_exists(self::getFilePath($name));
    }

    public static function removeClass($name) {
        return FileUtils::removeFile(self::getFilePath($name));
    }

    public static function getObject($name) {
        $className = "\context\\$name";
        $wrapper = new $className;
        return $wrapper->getObject();
    }

}