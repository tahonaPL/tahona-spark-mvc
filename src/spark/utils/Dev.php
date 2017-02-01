<?php
/**
 * Created by PhpStorm.
 * User: primosz67
 * Date: 01.02.17
 * Time: 11:11
 */

namespace spark\utils;


use spark\tools\html\HtmlUtils;

class Dev {

    //TODO
    public static function dumpSimple($data, $maxLevel = 10) {
        echo self::parse($data, $maxLevel);
    }

    /**
     * @param $data
     * @param $maxLevel
     * @return string
     * @throws \spark\common\IllegalStateException
     */
    private static function parse($data, $maxLevel) {
        $r = HtmlUtils::builder();
        $element = $r->tag("table");

        if ($maxLevel >= 0) {
            if (Objects::isArray($data)) {
                foreach ($data as $k => $v) {
                    $element->tag("tr")->tag("td", $k . "(key)")->end();
                    $element->tag("tr")->tag("td", Dev::parse($v, $maxLevel - 1))->end();
                }
            } else if (Objects::isNull($data)) {
                $element->tag("tr")
                    ->tag("td", "null")
                    ->end();
            } else if (!Objects::isPrimitive($data)) {
                $simpleClassName = Objects::getSimpleClassName($data);
                $element->tag("tr")->tag("td", $simpleClassName . "(object)")->end();
                $element->tag("tr")->tag("td", Dev::parse((array)$data, $maxLevel - 1))->end();
            }
        } else if (Objects::isPrimitive($data)) {
            $element->tag("tr")
                ->tag("td", $data)
                ->end();
        }

        return $element->end()
            ->get();
    }
}