<?php
/**
 *
 * 
 * Date: 24.05.17
 * Time: 21:31
 */

namespace Spark\View\Smarty;


interface SmartyPlugin {

    public function getTag(): string;
    public function execute(array $params, $smarty);
}