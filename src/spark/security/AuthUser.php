<?php
/**
 * Created by PhpStorm.
 * User: primosz67
 * Date: 25.06.14
 * Time: 08:38
 */

namespace spark\security;


class AuthUser {

    private $username;
    private $roles = array();

    private $additionalObject;

    function __construct($username, $roles = array(), $additionalObject = null) {
        $this->username = $username;
        $this->roles = $roles;
        $this->additionalObject = $additionalObject;
    }

    /**
     * @return array
     */
    public function getRoles() {
        return $this->roles;
    }

    public function hasRole($role) {
        return in_array($role, $this->roles);
    }

    /**
     * @return mixed
     */
    public function getAdditionalObject() {
        if (is_null($this->additionalObject)) {
            return null;
        } else {
            return clone $this->additionalObject;
        }
    }

    /**
     * syntactic sugar
     * @return mixed
     */
    public function getData() {
        return $this->getAdditionalObject();
    }

    /**
     * @deprecated
     * @return mixed
     */
    public function getUsername() {
        return $this->username;
    }


    public function getLogin(){
        return $this->username;
    }



} 