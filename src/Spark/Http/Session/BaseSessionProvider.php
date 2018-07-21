<?php
/**
 * Date: 21.07.18
 * Time: 11:44
 */

namespace Spark\Http\Session;


use Spark\Http\Session;
use Spark\Utils\Asserts;
use Spark\Utils\Collections;
use Spark\Utils\Objects;

class BaseSessionProvider implements SessionProvider {

    public function getOrCreateSession(): Session {
        //move to sessionUtils or something
        if (!$this->hasSession()) {
            Asserts::checkState(!headers_sent(), 'Session will not be updated if header sent or var_dump');
            Asserts::checkState(session_start(), 'Session could not be start');

            //FIX ME - regenerating session
//            session_regenerate_id();
        }

        if (false === Collections::hasKey($_SESSION, 'spark_session')) {
            $_SESSION['spark_session'] = new SessionImpl();
        }

        return $_SESSION['spark_session'];
    }

    public function getSession(): Session {
        return $this->getOrCreateSession();
    }
    
    public function hasSession(): bool {
        return Objects::isNotNull($_SESSION);
    }
}