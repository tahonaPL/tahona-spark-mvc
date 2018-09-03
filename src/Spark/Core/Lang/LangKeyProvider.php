<?php


namespace Spark\Core\Lang;


interface LangKeyProvider {

    public const NAME = 'langKeyProvider';
    public const D_LANG = 'lang';

    public function getLang() : string ;
}

