<?php
/**
 * Created by PhpStorm.
 * User: primosz67
 * Date: 06.07.14
 * Time: 18:11
 */

namespace spark\view\redirect;


use spark\core\annotation\Inject;
use spark\http\Request;
use spark\Routing;
use spark\utils\Collections;
use spark\utils\StringUtils;
use spark\utils\UrlUtils;
use spark\view\ViewHandler;

class RedirectViewHandler extends ViewHandler {

    const NAME = "redirectViewHandler";

    /**
     * @Inject
     * @var Routing
     */
    private $routing;


    public function isView($viewModel) {
        return $viewModel instanceof RedirectViewModel;
    }

    /**
     * @param RedirectViewModel $viewModel
     * @param Request $request
     */
    public function handleView($viewModel, Request $request) {
        if ($this->isView($viewModel)) {

            $redirect = $viewModel->getUrl();
            if (StringUtils::isNotBlank($redirect)) {

                $resolved = $this->routing->resolveRoute($redirect, $viewModel->getParams());
                if (StringUtils::isNotBlank($resolved)) {
                    $request->instantRedirect($resolved);
                } else {

                    if (Collections::isNotEmpty($viewModel->getParams())) {
                        $redirect = UrlUtils::appendParams($redirect, $viewModel->getParams());
                    }

                    $request->instantRedirect($redirect);
                }
            }
        }
    }
}