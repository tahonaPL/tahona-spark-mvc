<?php
/**
 * Created by PhpStorm.
 * User: primosz67
 * Date: 22.07.14
 * Time: 19:54
 */

namespace spark\utils;


interface Comparator {

    /**
     * @param $object1
     * @param $object2
     * @return boolean
     */
    public function compare($object1, $object2);

} 