<?php
/**
 * Created by PhpStorm.
 * User: crownclown67
 * Date: 24.05.17
 * Time: 21:31
 */

namespace Spark\View\Smarty;


interface SmartyPlugin {

    public function getTag();
    public function execute($params, $smarty);
}