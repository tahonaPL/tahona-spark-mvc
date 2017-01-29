<?php
/**
 * Created by PhpStorm.
 * User: primosz67
 * Date: 19.10.16
 * Time: 22:40
 */

namespace spark\loader;


interface ClassLoader {

    public function registerNamespace($space, $namespaceRootPath = array());

    public function registerNamespaces($vendors = array());

    public function register();
}