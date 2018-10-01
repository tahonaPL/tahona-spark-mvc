<?php
/**
 *
 *
 * Date: 09.10.16
 * Time: 20:18
 */

namespace Spark\Core\Event\Annotation;

use Doctrine\Common\Annotations\Annotation\Target;

/**
 * @Annotation
 * @Target({"METHOD"})
 */
final class Subscribe {

    /**
     * Subscription group name for handlers
     *
     * Default value one of:
     *  1. annotation name
     *  2. Method Param class name type if strong typed
     *  3. Method Param field name
     *
     * @var string
     */
    public $name = null;
}