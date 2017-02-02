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
        $collection = Collections::builder();
        $collection = $collection->add("<style>table td {border:1px solid black}</style><table>");

        if ($maxLevel >= 0) {
            if (Objects::isArray($data)) {

                $collection = $collection->add("<tr><td>");
                $collection = $collection->add("<table>");

                foreach ($data as $k => $v) {
                    $parse = Dev::parse($v, $maxLevel - 1);
                    $collection = $collection->add("<tr> <td>$k (key)</td> <td>". $parse."</td></tr>");
                }

                $collection = $collection->add("</table>");
                $collection = $collection->add("</td></tr>");

            } else if (Objects::isNull($data)) {
                $collection = $collection->add("<tr><td>null</td>");

            } else if (!Objects::isPrimitive($data)) {
                $simpleClassName = Objects::getSimpleClassName($data);
                $collection = $collection->add("<tr><td>$simpleClassName</td>");

                $c = Dev::parse($data, $maxLevel - 1);
                $collection = $collection->add("<tr><td>". $c ."</td>");
            }

        } else if (Objects::isPrimitive($data)) {
            $collection = $collection->add("<tr><td>$data</td>");
        }

        $collection = $collection->add("</table>");
        return StringUtils::join("",$collection->get());
    }
}