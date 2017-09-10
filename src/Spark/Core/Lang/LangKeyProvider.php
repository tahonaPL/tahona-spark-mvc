<?php


namespace Spark\Core\Lang;


interface LangKeyProvider {

    const NAME ="langKeyProvider";
    const D_LANG ="lang";

    public function getLang();
}

