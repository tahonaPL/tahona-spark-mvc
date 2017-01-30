<?php
/**
 * Created by PhpStorm.
 * User: primosz67
 * Date: 06.07.14
 * Time: 17:42
 */

namespace spark\view;


use spark\common\IllegalArgumentException;
use spark\Config;
use spark\core\di\Inject;
use spark\core\provider\BeanProvider;
use spark\Services;
use spark\utils\Asserts;
use spark\utils\Collections;
use spark\utils\Objects;
use spark\view\ViewModel;

class ViewHandlerProvider {

    const NAME = "viewHandlerProvider";

    /**
     * @Inject()
     * @var BeanProvider
     */
    private $beanProvider;

    /**
     * @Inject()
     * @var Config
     */
    private $config;


    public function handleView($viewModel, $request) {
        /** @var $viewHandler ViewHandler */
        if (Objects::getClassName($viewModel) === ViewModel::CLASS_NAME) {
            $viewHandler = $this->beanProvider->getBean("defaultViewHandler");
            $viewHandler->handleView($viewModel, $request);
        } else {

            $viewHandler = $this->getProvider($viewModel, $request);
            Asserts::checkNotNull($viewHandler, "ViewHandler not found for viewModelType: ".Objects::getClassName($viewModel));

            $viewHandler->handleView($viewModel, $request);
        }

    }

    private function getProvider(ViewModel $viewModel, $request) {
        $handlers = $this->beanProvider->getByType(ViewHandler::CLASS_NAME);

        /** @var $handler ViewHandler */
        foreach ($handlers as $handler) {
            if ($handler->isView($viewModel)) {
                return $handler;
            }
        }
        return null;
    }


}