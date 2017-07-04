<?php
/**
 * Created by PhpStorm.
 * User: primosz67
 * Date: 11.04.15
 * Time: 17:14
 */

namespace spark\core\error;


use ErrorException;
use spark\Container;
use spark\core\annotation\Inject;
use spark\core\provider\BeanProvider;
use spark\Engine;
use spark\http\HttpCode;
use spark\http\Request;
use spark\http\ResponseHelper;
use spark\utils\Collections;
use spark\utils\Objects;

class GlobalErrorHandler {

    const NAME = "globalErrorHandler";
    const EXCEPTION_HANDLER = "handleException";
    const FATAL_HANDLER = "handleFatal";

    /**
     * @var Engine
     */
    private $engine;
    private $exceptionResolvers;

    /**
     * GlobalErrorHandler constructor.
     */
    public function __construct(Engine $engine) {
        $this->engine = $engine;
    }


    /**
     *
     * @param $exception \Exception
     * @throws \Exception
     */
    public function handleException($exception) {

        if (error_reporting() == 0) {
            return;
        }
        if (error_reporting()) {
            $invoke = $this->getHandler();

            $invoke($exception);
            return;
        }
    }

    public function handleFatal($severity, $message, $filename, $lineno) {

        $error = error_get_last();

        if (error_reporting() == 0) {
            return;
        }

        if (error_reporting() && Objects::isNotNull($error)) {
            $severity = $error["type"];
            $filename = $error["file"];
            $lineno = $error["line"];
            $message = $error["message"];

            $exc = new \ErrorException($message, 0, $severity, $filename, $lineno);

            $invoke = $this->getHandler();
            $invoke($exc);
            return $exc;

        }
    }

    public function setup($resolvers=array()) {
        $this->exceptionResolvers = $resolvers;
        set_exception_handler(array($this, self::EXCEPTION_HANDLER));
        set_error_handler(array($this, self::FATAL_HANDLER));
    }

    private function getHandler() {
        return function ($error) {

            $exceptionResolvers = Collections::builder($this->exceptionResolvers)
                ->sort(function ($x, $y) {
                    /** @var ExceptionResolver $x */
                    return $x->getOrder() > $y->getOrder();
                })
                ->get();

            foreach ($exceptionResolvers as $resolver) {
                /** @var ExceptionResolver $resolver */
                $viewModel = $resolver->doResolveException($error);
                if (Objects::isNotNull($viewModel)) {
                    $request = new Request();
                    $this->engine->updateRequest($request);
                    $this->engine->handleViewModel($request, $viewModel);

                    return;
                }
            }

            //Default behavior
            /** @var ErrorException $error */
            ResponseHelper::setCode(HttpCode::$INTERNAL_SERVER_ERROR);
            throw new \Exception($error->getMessage(), $error->getCode(), $error);
        };
    }


} 