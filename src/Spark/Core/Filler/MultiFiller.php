<?php
/**
 * Date: 06.09.18
 * Time: 05:12
 */

namespace Spark\Core\Filler;


use Spark\Common\Type\Orderable;

interface MultiFiller extends Orderable {

    public function filter(array $parameters): array;

}