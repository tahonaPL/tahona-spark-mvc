<?php
/**
 * Created by PhpStorm.
 * User: primosz67
 * Date: 29.07.14
 * Time: 21:56
 */

namespace spark\common\data;


use spark\utils\StringUtils;

class ContentType {

    const APPLICATION_PDF = "application/pdf";
    const APPLICATION_JSON = "application/json";
    const IMAGE_JPEG = "image/jpeg";
    const IMAGE_PNG = "image/png";
    const IMAGE = "image/";

    public static $IMAGE_PNG;
    /**
     * @var ContentType
     */
    public static $IMAGE;
    /**
     * @var ContentType
     */
    public static $IMAGE_JPEG;
    public static $APPLICATION_PDF;
    public static $APPLICATION_JSON;

    private $type;

    function __construct($type) {
        $this->type = $type;

    }

    public static function init() {
        self::$IMAGE_PNG = new ContentType(ContentType::IMAGE_PNG);
        self::$IMAGE_JPEG = new ContentType(ContentType::IMAGE_JPEG);
        self::$IMAGE = new ContentType(ContentType::IMAGE);
        self::$APPLICATION_PDF = new ContentType(ContentType::APPLICATION_PDF);
        self::$APPLICATION_JSON = new ContentType(ContentType::APPLICATION_JSON);
    }

    public function getType() {
        return $this->type;
    }

    public function isContentType($contentType) {
        return StringUtils::contains($contentType, $this->getType());
    }
}

ContentType::init();