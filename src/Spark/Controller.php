<?php

namespace Spark;

use Spark\Http\Request;
use Spark\Upload\FileObject;
use Spark\View\Utils\ViewUrlUtils;

class Controller {

    /**
     *
     * @var Request
     */
    private $request;

    /**
     *
     * @var array
     */
    private $responseParams = array();

    public function init($request, $responseParams) {
        $this->request = $request;
        $this->responseParams = $responseParams;
    }

    /**
     * @return Request
     */
    public function getRequest() {
        return $this->request;
    }

    protected function getParam($key, $defaultValue = null) {
        $value = $this->request->getParam($key);

        if (isset($value)) {
            return $value;
        } else if (isset ($this->responseParams[$key])) {
            return $this->responseParams[$key];
        } else {
            return $defaultValue;
        }
    }

    public function isFileUploaded() {
        return $this->request->isFileUploaded();
    }

    /**
     *
     * @deprecated
     */
    public function getFile($name) {
        return $this->request->getFile($name);
    }

    /**
     * @param $name
     * @return FileObject
     */
    public function getFileObject($name) {
        return $this->request->getFileObject($name);
    }

    protected function isPost() {
        return $this->request->isPost();
    }

    public function getViewPath($viewName = null) {
        return ViewUrlUtils::createViewPathWithViewName($this->getRequest(), $viewName);
    }
}
