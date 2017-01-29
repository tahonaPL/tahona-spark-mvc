<?php
/**
 * Created by PhpStorm.
 * User: primosz67
 * Date: 23.08.15
 * Time: 07:47
 */

namespace spark\http;


class HeadersWrapper {

    private $headers;

    function __construct($headers = array()) {
        $this->headers = $headers;
    }

    /**
     * @return array
     */
    public function getCookie() {
        return $this->headers["Cookie"];
    }

    /**
     * @return string
     */
    public function getContentType() {
        return $this->headers["Content-Type"];
    }

    /**
     * @return string
     */
    public function getConnectionType() {
        return $this->headers["Connection"];
    }

    /**
     * @return string
     */
    public function getAgent() {
        return $this->headers["User-Agent"];
    }

    /**
     * @return mixed
     */
    public function getAcceptLanguage() {
        return $this->headers["Accept-Language"];
    }

    /**
     * @return mixed
     */
    public function getAcceptEncoding() {
        return $this->headers["Accept-Encoding"];
    }

    /**
     * @return mixed
     */
    public function getAccept() {
        return $this->headers["Accept"];
    }

    public function getHeader($key) {
        return $this->headers[$key];
    }

} 