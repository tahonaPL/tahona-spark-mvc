<?php
/**
 *
 *
 * Date: 06.07.14
 * Time: 17:42
 */

namespace Spark\View;


use Spark\Config;
use Spark\Core\Annotation\Inject;
use Spark\Core\Annotation\PostConstruct;
use Spark\Core\Provider\BeanProvider;
use Spark\Http\Response;
use Spark\Utils\Asserts;
use Spark\Utils\Objects;

class ViewHandlerProvider {

    public const NAME = 'viewHandlerProvider';

    /**
     * @Inject()
     * @var BeanProvider
     */
    private $beanProvider;

    /**
     * @Inject
     * @var ViewHandler
     */
    private $defaultViewHandler;

    /**
     * @var array
     */
    private $handlers;

    /**
     * @PostConstruct()
     */
    public function init() {
        $this->handlers = $this->beanProvider->getByType(ViewHandler::class);
    }

    public function handleView(Response $response, $request) {
        /** @var $viewHandler ViewHandler */
        if ($response instanceof ViewModel) {
            $viewHandler = $this->defaultViewHandler;
            $viewHandler->handleView($response, $request);
        } else {
            $viewHandler = $this->getProvider($response, $request);
            Asserts::notNull($viewHandler, 'ViewHandler not found for response type: ' . Objects::getClassName($response));

            $viewHandler->handleView($response, $request);
        }
    }

    private function getProvider(Response $viewModel, $request) {

        /** @var $handler ViewHandler */
        foreach ($this->handlers as $handler) {
            if ($handler->isView($viewModel)) {
                return $handler;
            }
        }
        return null;
    }


}