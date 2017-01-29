<?php

namespace spark\http;

use spark\http\utils\RequestUtils;
use spark\utils\UrlUtils;
use spark\upload\FileObject;
use spark\upload\FileObjectFactory;
use spark\utils\Collections;
use spark\utils\Objects;

class Request {

    private $methodName;
    private $controllerClassName;
    private $moduleName;
    private $namespace;

    private $controllerPrefix;
    private $controllerName;
    private $urlParams = array();

    private $hostPath;
    private $securityRoles;

    //headers
    private $headers;

    //url + Get params
    private $cachedGetParams;

    function __construct() {
        $this->headers = new HeadersWrapper(getallheaders());
    }

    public function getMethodName() {
        return $this->methodName;
    }

    public function getControllerClassName() {
        return $this->controllerClassName;
    }

    public function getModuleName() {
        return $this->moduleName;
    }

    public function getControllerName() {
        return $this->controllerName;
    }

    public function setMethodName($methodName) {
        $this->methodName = $methodName;
    }

    public function setControllerClassName($controllerClassName) {
        $this->controllerClassName = $controllerClassName;
    }

    public function setModuleName($moduleName) {
        $this->moduleName = $moduleName;
    }

    public function setControllerName($controllerName) {
        $this->controllerName = $controllerName;
    }

    public function isPost() {
        return RequestUtils::isPost();
    }

    /**
     * @return array
     */
    public function getPostData() {
        return RequestUtils::getPostParams();
    }

    public function getParam($name, $default = null) {
        $param = $this->getParamOrNull($name);
        return $param != null ? $param : $default;
    }

    public function setUrlParams($urlParams) {
        $this->urlParams = $urlParams;
    }

    public function getSession() {
        return RequestUtils::getOrCreateSession();
    }


    /**
     * @param $name
     * @return FileObject
     */
    public function getFileObject($name) {
        $fileData = $this->getFile($name);
        if (Objects::isNotNull($fileData)) {
            return FileObjectFactory::create($fileData);
        }
        return null;
    }

    public function getFile($name) {
        return RequestUtils::getFileParams($name);
    }

    public function isFileUploaded() {
        return RequestUtils::isFile();
    }

    public function setControllerPrefix($controllerPrefix) {
        $this->controllerPrefix = $controllerPrefix;
    }

    public function getControllerPrefix() {
        return $this->controllerPrefix;
    }

    /**
     * instant Redirect
     * @param $path
     */
    public function instantRedirect($path) {
        RequestUtils::redirect(UrlUtils::getPath($path));
    }

    public function setHostPath($hostPath) {
        $this->hostPath = UrlUtils::wrapHttpIfNeeded($hostPath);
    }

    public function setSecurityRoles($securityRoles) {
        $this->securityRoles = $securityRoles;
    }

    /**
     * @return mixed
     */
    public function getSecurityRoles() {
        return $this->securityRoles;
    }

    public function hasSecurityRoles() {
        return false == empty($this->securityRoles);
    }


    public function setNamespace($namespace) {
        $this->namespace = $namespace;
    }

    /**
     * @return \spark\http\HeadersWrapper
     */
    public function getHeaders() {
        return $this->headers;
    }


    /**
     * @return mixed
     */
    public function getNamespace() {
        return $this->namespace;
    }

    public function getLang() {
        return Collections::getValueOrDefault($_COOKIE, "lang", "pl");
    }

    private function getParamOrNull($name) {
        if (isset($this->urlParams[$name])) {
            return $this->urlParams[$name];
        } else {
            return RequestUtils::getParam($name);
        }
    }

    public function getUrlParams() {
        if (Objects::isNull($this->cachedGetParams)) {
            $this->cachedGetParams = Collections::builder()
                ->addAll(RequestUtils::getGetParams())
                ->addAll($this->urlParams)
                ->get();
        }
        return $this->cachedGetParams;

    }

    public function getAllParams() {
        return Collections::builder()
            ->addAll($this->getUrlParams())
            ->addAll($this->getPostData())
            ->get();
    }
}
