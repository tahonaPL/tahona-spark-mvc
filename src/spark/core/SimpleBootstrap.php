<?php
/**
 * Created by PhpStorm.
 * User: primosz67
 * Date: 29.11.14
 * Time: 12:28
 */

namespace spark\core;

use spark\Config;
use spark\security\AuthenticationProviderService;
use spark\security\exception\AccessDeniedException;
use spark\utils\StringUtils;


/**
 * Roles related class
 * Class SimpleBootstrap
 * @package spark\core
 */
class SimpleBootstrap extends Bootstrap {

    public function init() {
        parent::init();

        $authenticationService = $this->getAuthenticationService();
        $request = $this->getRequest();

        $config = $this->getConfig();

        if ($config->getProperty(Config::SECURITY_PARAM)) {
            if (false === $authenticationService->hasUserAccess($request->getSecurityRoles())) {
                throw new AccessDeniedException();
            }
        }
    }

    public function after() {
        parent::after();
        $this->addLoggedUserToView();
    }

    /**
     * @return AuthenticationProviderService
     */
    private function getAuthenticationService() {
        return $this->get(AuthenticationProviderService::NAME);
    }

    private function addLoggedUserToView() {
        $authenticationService = $this->getAuthenticationService();
        $viewModel = $this->getViewModel();

        if (false == $viewModel->isRedirect() && $authenticationService->isLogged()) {
            $loggedUser = $authenticationService->getAuthUser();
            $viewModel->add("loggedUser", $loggedUser);
        }
    }

} 