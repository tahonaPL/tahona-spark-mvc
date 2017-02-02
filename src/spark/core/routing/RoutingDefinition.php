<?php
/**
 * Created by PhpStorm.
 * User: primosz67
 * Date: 02.02.17
 * Time: 07:45
 */

namespace spark\core\routing;


use spark\Routing;
use spark\routing\RoutingUtils;
use spark\utils\Collections;
use spark\utils\Objects;
use spark\utils\StringFunctions;

class RoutingDefinition {

    const D_CONTROLLER_CLASS_NAME = "controllerClassName";
    const D_ACTION_METHOD = "actionMethod";
    private $path;
    private $controllerClassName;
    private $actionMethod;
    private $requestMethods;
    private $requestHeaders;

    /**
     * @return mixed
     */
    public function getPath() {
        return $this->path;
    }

    /**
     * @param mixed $path
     */
    public function setPath($path) {
        $this->path = $path;
    }

    /**
     * @return mixed
     */
    public function getControllerClassName() {
        return $this->controllerClassName;
    }

    /**
     * @param mixed $controllerClassName
     */
    public function setControllerClassName($controllerClassName) {
        $this->controllerClassName = $controllerClassName;
    }

    /**
     * @return mixed
     */
    public function getActionMethod() {
        return $this->actionMethod;
    }

    /**
     * @param mixed $actionMethod
     */
    public function setActionMethod($actionMethod) {
        $this->actionMethod = $actionMethod;
    }

    /**
     * @return mixed
     */
    public function getRequestMethods() {
        if (Objects::isArray($this->requestMethods)) {
            return $this->requestMethods;
        }
        return array($this->requestMethods);
    }

    /**
     * @param mixed $requestMethod
     */
    public function setRequestMethods($requestMethods = array()) {
        $this->requestMethods = Collections::builder(Collections::asArray($requestMethods))
            ->map(StringFunctions::upper())
            ->get();;
    }

    /**
     * @return array of headers
     */
    public function getRequestHeaders() {
        if (Objects::isArray($this->requestHeaders)) {
            return $this->requestHeaders;
        }

        return array($this->requestHeaders);
    }

    /**
     * @param mixed $requestHeaders
     */
    public function setRequestHeaders($requestHeaders) {
        $this->requestHeaders = $requestHeaders;
    }

    public function getKey() {
        return RoutingUtils::generateKey($this);
    }


}