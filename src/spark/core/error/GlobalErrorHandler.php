<?php
/**
 * Created by PhpStorm.
 * User: primosz67
 * Date: 11.04.15
 * Time: 17:14
 */

namespace spark\core\error;


class GlobalErrorHandler {

    const EXCEPTION_HANDLER = "handleException";
    const FATAL_HANDLER = "handleFatal";

    /**
     * @var \Closure
     */
    private $handler;

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
            $invoke = $this->handler;
            $exec = new \Exception($exception->getMessage(), $exception->getCode(), $exception);
            $invoke($exception);

            throw $exec;
            return;
        }
    }

    public function handleFatal($severity, $message, $filename, $lineno) {

        $error = error_get_last();

        if ($error !== NULL) {
            $severity = $error["type"];
            $filename = $error["file"];
            $lineno = $error["line"];
            $message = $error["message"];
        }

        if (error_reporting() == 0) {
            return;
        }
        if (error_reporting()) {
            $exc = new \ErrorException($message, 0, $severity, $filename, $lineno);

            $invoke = $this->handler;
            $invoke($exc);
            return $exc;

        }
    }

    public function setup() {
        set_exception_handler(array($this, self::EXCEPTION_HANDLER));
        set_error_handler(array($this, self::FATAL_HANDLER));
    }

    public function setHandler(\Closure $handler) {
        $this->handler = $handler;
    }
} 