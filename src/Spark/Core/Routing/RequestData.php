<?php


namespace Spark\Core\Routing;

use Spark\Common\Optional;
use Spark\Http\HeadersWrapper;
use Spark\Http\Session;
use Spark\Http\Session\SessionProvider;
use Spark\Http\Utils\CookieUtils;
use Spark\Http\Utils\RequestUtils;
use Spark\Utils\UrlUtils;
use Spark\Upload\FileObject;
use Spark\Upload\FileObjectFactory;
use Spark\Utils\Collections;
use Spark\Utils\Objects;
use Spark\Http\Request;

class RequestData implements Request {

    private $routeDefinition;

    private $methodName;
    private $controllerClassName;
    private $moduleName;
    private $namespace;

    private $controllerName;
    private $urlParams = array();

    private $hostPath;

    //headers
    private $headers;

    /**
     * @var SessionProvider
     */
    private $sessionProvider;

    //url + Get params
    private $cachedGetParams;

    public function __construct(SessionProvider $sessionProvider) {
        $this->headers = new HeadersWrapper(RequestUtils::getHeaders());
        $this->sessionProvider = $sessionProvider;
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

    /**
     * For /Spark/core/test/controller/xxx/EngineController the module name is
     * core/test/xxx  (controller is removed)
     *
     * @param $moduleName
     */
    public function setModuleName($moduleName) {
        $this->moduleName = $moduleName;
    }

    /**
     *  Only prefix of Controller class e.g: for IndexController will be "Index".
     *
     * @param $controllerName
     */
    public function setControllerName($controllerName) {
        $this->controllerName = $controllerName;
    }

    public function isPost(): bool {
        return RequestUtils::isPost();
    }

    /**
     * @return array
     */
    public function getPostData() {
        return Collections::builder(RequestUtils::getPostParams())
            ->addAll(RequestUtils::getAllFilesParams())
            ->get();
    }

    public function getParam(string $name, $default = null) {
        $param = $this->getParamOrNull($name);
        return Objects::isNotNull($param) ? $param : $default;
    }

    public function setUrlParams($urlParams) {
        $this->urlParams = $urlParams;
    }

    public function getSession(): Session {
        return $this->sessionProvider->getOrCreateSession();
    }


    public function getFileObject(string $name): FileObject {
        $fileData = $this->getFile($name);
        if (Objects::isNotNull($fileData)) {
            return FileObjectFactory::create($fileData);
        }
        return null;
    }

    public function getFile(string $name) {
        return RequestUtils::getFileParams($name);
    }

    public function isFileUploaded(): bool {
        return RequestUtils::isFile();
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

    public function setNamespace($namespace) {
        $this->namespace = $namespace;
    }

    /**
     * @return \Spark\Http\HeadersWrapper
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

    private function getParamOrNull($name) {
        if (isset($this->urlParams[$name])) {
            return $this->urlParams[$name];
        }
        return RequestUtils::getParam($name);

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

    public function getCookie($key, $def = null) {
        return Optional::ofNullable(CookieUtils::getCookieValue($key))->orElse($def);
    }

    public function getBody() {
        return RequestUtils::getBody();
    }

    public function setRouteDefinition($routeDefinition) {
        $this->routeDefinition = $routeDefinition;
    }

    /**
     * @return RoutingDefinition
     */
    public function getRouteDefinition() {
        return $this->routeDefinition;
    }

}