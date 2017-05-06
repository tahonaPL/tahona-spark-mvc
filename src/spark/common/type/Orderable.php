<?php
/**
 * Created by PhpStorm.
 * User: primosz67
 * Date: 21.03.17
 * Time: 14:12
 */

namespace spark\common\type;


interface Orderable {

    /**
     * @return int
     */
    public function getOrder();


}