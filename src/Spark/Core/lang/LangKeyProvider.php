<?php


namespace Spark\Core\lang;


interface LangKeyProvider {

    const NAME ="langKeyProvider";
    const D_LANG ="lang";

    public function getLang();
}

