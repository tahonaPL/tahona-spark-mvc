<?php
/**
 * Created by PhpStorm.
 * User: primosz67
 * Date: 04.02.17
 * Time: 12:22
 */

namespace Spark\Core\Lang;


use Spark\Core\Resource\ResourcePath;
use Spark\Utils\Asserts;
use Spark\Utils\Collections;


class LangResourcePath extends ResourcePath {


    /**
     * @param $lang
     * @param $path
     * @return $this
     */
    public function addPath($lang, $path) {
        Asserts::notNull($lang);
        Asserts::notNull($path);

        if (!Collections::hasKey($this->paths, $lang)) {
            $this->paths[$lang] = [];
        }

        Collections::addAll($this->paths[$lang], [$path]);
        return $this;
    }

}