<?php
/**
 * Created by PhpStorm.
 * User: primosz67
 * Date: 14.07.14
 * Time: 00:17
 */

namespace Spark\Core\Annotation;

use Doctrine\Common\Annotations\Annotation\Target;



/**
 * @Annotation
 * @Target({"CLASS"})
 */
final class EnableApcuBeanCache {


    /** @var string */
    public $prefix = "";

    /** @var string */
    public $resetParam = null;

} 