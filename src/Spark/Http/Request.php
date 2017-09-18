<?php

namespace Spark\Http;


use Spark\Upload\FileObject;

interface Request {

    public function isPost(): bool;

    public function getPostData();

    public function getParam(string $name, $default = null);

    public function getSession();

    public function getFileObject(string $name): FileObject;

    public function isFileUploaded(): bool;

    public function getHeaders();

    public function getAllParams();

    public function getCookie($key, $default = null);

    public function getBody();

}
