<?php
/**
 * Created by PhpStorm.
 * User: primosz67
 * Date: 14.07.14
 * Time: 00:17
 */

namespace spark\core\annotation;

use Doctrine\Common\Annotations\Annotation\Target;



/**
 * @Annotation
 * @Target({"CLASS"})
 */
final class SmartyViewConfiguration {

    /** @var string */
    public $cacheId = "TAHONA_ROCKS";

    /** @var boolean */
    public $forceCompile = true;
    /** @var boolean */
    public $compileCheck = true;
    /** @var boolean */
    public $caching = false;
    /** @var int */
    public $cacheLifetime = 1800;
    /** @var boolean */
    public $debugging = false;

    /** @var int */
    public $errorReporting = E_ALL & ~E_NOTICE;


} 