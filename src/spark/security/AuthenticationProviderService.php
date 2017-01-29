<?php
/**
 * Created by PhpStorm.
 * User: primosz67
 * Date: 25.06.14
 * Time: 08:36
 */

namespace spark\security;


use spark\common\IllegalArgumentException;
use spark\http\utils\CookieUtils;
use spark\http\Session;
use spark\http\utils\RequestUtils;
use spark\core\service\ServiceHelper;
use spark\utils\Collections;

class AuthenticationProviderService extends ServiceHelper {

    const NAME = "authenticationProviderService";

    const LOGGED_USER_SESSION_KEY = "spark_loggedUser";

    /**
     *  Authenticate User
     * @param $userName
     * @param array $roles
     * @param null $additionalDataObject
     */
    public function createUser($userName, $roles = array(), $additionalDataObject = null) {
        $authUser = new AuthUser($userName, $roles, $additionalDataObject);
        $this->authenticateUser($authUser);
    }

    /**
     *  Authenticate User
     * @param AuthUser $authUser
     */
    public function authenticateUser(AuthUser $authUser) {
        /** @var $session Session */
        $session = RequestUtils::getOrCreateSession();
        $session->add(self::LOGGED_USER_SESSION_KEY, $authUser);
    }

    public function isLogged() {
        /** @var $session Session */
        $session = RequestUtils::getSession();
        return $session->has(self::LOGGED_USER_SESSION_KEY);
    }

    public function  removeUser() {
        /** @var $session Session */
        $session = RequestUtils::getSession();
        $session->remove(self::LOGGED_USER_SESSION_KEY);
        CookieUtils::removeCookie(RequestUtils::SESSION_NAME);
    }

    /**
     * @return AuthUser
     */
    public function getAuthUser() {
        if ($this->isLogged()) {
            $session = RequestUtils::getOrCreateSession();
            return $session->get(self::LOGGED_USER_SESSION_KEY);

        } else {
            throw new IllegalArgumentException("No auth user in session");
        }
    }

    /**
     * @param array $pathAccessRoles - array of roles on Path  (check:  Request->roles)
     * @return bool
     */
    public function hasUserAccess($pathAccessRoles = array()) {
        if (empty($pathAccessRoles)) {
            return true;

        } else if ($this->isLogged()) {
            $authUser = $this->getAuthUser();
            $userRoles = $authUser->getRoles();

            foreach ($pathAccessRoles as $pathRole) {
                if (in_array($pathRole, $userRoles)) {
                    return true;
                }
            }
        }
        return false;
    }
}