<?php
/**
 *
 *
 * Date: 02.02.17
 * Time: 07:45
 */

namespace Spark\Core\Routing;


use Spark\Routing;
use Spark\Routing\RoutingUtils;
use Spark\Utils\Collections;
use Spark\Utils\Objects;
use Spark\Utils\StringFunctions;

class RoutingDefinition {

    public const D_CONTROLLER_CLASS_NAME = 'controllerClassName';
    public const D_ACTION_METHOD         = 'actionMethod';
    public const D_PATH                  = 'path';

    private $id;

    private $path;
    private $controllerClassName;
    private $actionMethod;
    private $requestMethods = array();
    private $requestHeaders = array();
    private $params = array();
    private $actionMethodParameters = array();
    private $controllerAnnotations = array();

    /**
     * RoutingDefinition constructor.
     * @param $id
     */
    public function __construct() {
        $this->id = mt_rand();
    }

    /**
     * @return string
     */
    public function getId() {
        return $this->id;
    }


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
        return $this->requestHeaders;
    }

    /**
     * @param mixed $requestHeaders
     */
    public function setRequestHeaders($requestHeaders) {
        $this->requestHeaders = Collections::asArray($requestHeaders);
    }

    public function getKey() {
        return RoutingUtils::generateKey($this);
    }

    public function setParams($params = array()) {
        $this->params = Collections::asArray($params);
    }

    public function getParams() {
        return $this->params;
    }

    public function setActionMethodParameters($actionMethodParameters) {
        $this->actionMethodParameters = $actionMethodParameters;
    }

    public function getActionMethodParameters() {
        return $this->actionMethodParameters;
    }

    public function setControllerAnnotations($controllerAnnotations) {
        $this->controllerAnnotations = $controllerAnnotations;
    }

    /**
     * @return mixed
     */
    public function getControllerAnnotations() {
        return $this->controllerAnnotations;
    }


}