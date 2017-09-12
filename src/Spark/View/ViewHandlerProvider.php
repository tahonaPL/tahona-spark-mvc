<?php
/**
 * Created by PhpStorm.
 * User: primosz67
 * Date: 06.07.14
 * Time: 17:42
 */

namespace Spark\View;


use Spark\Common\IllegalArgumentException;
use Spark\Config;
use Spark\Core\Annotation\Inject;
use Spark\Core\Provider\BeanProvider;
use Spark\Container;
use Spark\Http\Response;
use Spark\Utils\Asserts;
use Spark\Utils\Collections;
use Spark\Utils\Objects;
use Spark\View\ViewModel;

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
        $handlers = $this->beanProvider->getByType(ViewHandler::class);

        /** @var $handler ViewHandler */
        foreach ($handlers as $handler) {
            if ($handler->isView($viewModel)) {
                return $handler;
            }
        }
        return null;
    }


}