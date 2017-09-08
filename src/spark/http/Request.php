<?php

namespace spark\http;


interface Request {


    public function isPost();

    public function getPostData();


    public function getParam($name, $default = null);

    public function getSession();

    public function getFileObject($name);

    public function isFileUploaded();

    public function getHeaders();

    public function getAllParams();

    public function getCookie($key, $def = null);

    public function getBody();

}
