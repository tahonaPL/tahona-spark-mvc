<?php


namespace spark\core\lang;


interface LangKeyProvider {

    const NAME ="langKeyProvider";
    const D_LANG ="lang";

    public function getLang();
}

