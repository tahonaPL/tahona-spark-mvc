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

    public function handleException($severity, $message, $filename, $lineno) {
        if (error_reporting() == 0) {
            return;
        }
        if (error_reporting() & $severity) {
            $exc = new \ErrorException($message, 0, $severity, $filename, $lineno);
            $invoke = $this->handler;
            $invoke($exc);
        }
    }

    function handleFatal() {
        $errfile = "unknown file";
        $errstr = "shutdown";
        $errno = E_CORE_ERROR;
        $errline = 0;

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
        }
    }

    public function setup() {
        set_error_handler(array($this, self::EXCEPTION_HANDLER));
//        register_shutdown_function(array($this, self::FATAL_HANDLER));
    }

    public function setHandler(\Closure $handler) {
        $this->handler = $handler;
    }
} 