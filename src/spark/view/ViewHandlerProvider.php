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
use spark\core\annotation\Inject;
use spark\core\provider\BeanProvider;
use spark\Container;
use spark\http\Response;
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


    public function handleView(Response $response, $request) {
        /** @var $viewHandler ViewHandler */
        if ($response instanceof ViewModel) {
            $viewHandler = $this->beanProvider->getBean("defaultViewHandler");
            $viewHandler->handleView($response, $request);
        } else {
            $viewHandler = $this->getProvider($response, $request);
            Asserts::notNull($viewHandler, "ViewHandler not found for response type: ".Objects::getClassName($response));

            $viewHandler->handleView($response, $request);
        }

    }

    private function getProvider(Response $viewModel, $request) {
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