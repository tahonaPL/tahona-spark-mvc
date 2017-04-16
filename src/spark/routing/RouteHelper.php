<?php
/**
 * Created by PhpStorm.
 * User: primosz67
 * Date: 07.12.14
 * Time: 13:01
 */

namespace spark\routing;


use spark\common\IllegalArgumentException;
use spark\Routing;
use spark\utils\Asserts;
use spark\utils\Collections;
use spark\utils\Objects;
use spark\utils\StringUtils;


/**
 * Class RouteHelper
 *
 *
 *
        $p =  RouteHelper::create()
            ->withRoles(array("myRole"))
            ->withController("tahona.base.controller.MainController")

            ->base("/")
            ->withMethod("indexAction")

            ->newPath("index.php")
            ->withMethod("indexAction", array("params"))

            ->newPath("home.php")
            ->withMethod("indexAction")

            ->newPath("/delete/{id}")
            ->withMethod("deleteAction", array("id"))

            ->base("/home")
            ->newPath("")
            ->withRoles(array())
            ->withController("tahona/defaults/controller/MainController")
            ->withMethod("indexAction")

            ->build();
 *
 *
 *
 * @package spark\routing
 */
class RouteHelper {

    private $paths = array();

    private $basePath;
    private $path;

    private $controller;
    private $method;
    private $params;
    private $roles;

    /**
     * @return RouteHelper
     */
    public static function create() {
        return new RouteHelper();
    }

    /**
     * @param $controllerPackage
     * @return $this
     */
    public function withController($controllerPackage) {
        $controllerPackage= StringUtils::replace($controllerPackage, "/", "\\");
        $this->controller = StringUtils::replace($controllerPackage, ".", "\\");
        return $this;
    }

    /**
     * @param $method
     * @param array $params
     * @return $this
     */
    public function withMethod($method, $params = array()) {
        $this->method = $method;
        $this->params = $params;
        return $this;
    }

    public function withRoles(array $roles) {
        Asserts::isArray($roles);
        $this->roles = $roles;
        return $this;
    }

    /**
     * @param $basePath
     * @return $this
     */
    public function base($basePath) {
        if (Objects::isNotNull($this->path)) {
            $this->buildPath();
        }

        $this->clearValues();

        $this->basePath = $basePath;
        return $this;
    }

    /**
     * @param $path
     * @return $this
     */
    public function newPath($path) {
        if (Objects::isNotNull($this->path)) {
            $this->buildPath();
        }

        $this->path = $path;
        return $this;
    }

    private function buildPath() {
        $path = $this->buildFullPath($this->basePath);

        if (Collections::hasElement($this->paths, $path))  {
            throw new IllegalArgumentException("Routing already exist: ". $path);
        }

        $arr = array();
        if (Collections::isNotEmpty($this->roles)) {
            $arr[Routing::ROLES] = $this->roles;
        }

        $arr[Routing::METHOD_NAME] = $this->method;
        $arr[Routing::CONTROLLER_NAME] = $this->controller;

        if (Collections::isNotEmpty($this->params)) {
            $arr[Routing::PARAMS] = $this->params;
        }

        $this->paths[$path] = $arr;
    }

    public function build() {
        $this->buildPath();
        $this->clearValues();
        return $this->paths;
    }

    private function clearValues() {
        $this->path = null;
//        $this->controller = null;
//        $this->method = null;

        $this->params = array();
//        $this->roles = array();

    }

    /**
     * @param $basePath
     * @return mixed
     */
    private function buildFullPath($basePath) {
        return StringUtils::replace($basePath.$this->path, "//", "/");
    }

}