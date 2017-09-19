<?php
/**
 *
 *
 * Date: 06.07.14
 * Time: 17:51
 */

namespace Spark\View;


use Spark\Core\Routing\RequestData;

abstract class ViewHandler {

    abstract public function isView($viewModel);

    abstract public function handleView($viewModel, RequestData $request);

}