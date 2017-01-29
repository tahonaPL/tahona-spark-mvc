<?php
/**
 * Created by PhpStorm.
 * User: primosz67
 * Date: 18.11.15
 * Time: 22:31
 */

namespace spark\seo;


use spark\utils\Asserts;
use spark\utils\Collections;
use spark\utils\Objects;
use spark\utils\StringUtils;


class SeoUrlFactory {

    const SEO_CLASS = "WithSeoUrl";

    public static function getSeo($parts = array()) {
        $params = Collections::builder($parts)
            ->map(StringUtils::mapReplace(StringUtils::SPACE, "-"))
            ->map(StringUtils::mapTrim())
            ->map(StringUtils::mapEscapeSpecialChar())
            ->get();

        return StringUtils::join("/", $params);
    }

    public static function getSeoUrlFromSeoObject($seoObject) {
        Asserts::checkArgument(false == Objects::isArray($seoObject), "Object or string needed");
        Asserts::notNull($seoObject);

        $class_uses = class_uses($seoObject);

        $anyMatch = Collections::anyMatch($class_uses, function ($value) {
            return StringUtils::contains($value, self::SEO_CLASS);
        });

        if ($anyMatch) {
            return "/".$seoObject->getSeoUrl();
        } else {
            return "";
        }
    }
}