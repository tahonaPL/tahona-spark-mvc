<?php
/**
 * Created by PhpStorm.
 * User: primosz67
 * Date: 06.07.14
 * Time: 17:51
 */

namespace Spark\View;


use Spark\Core\Routing\RequestData;

abstract class ViewHandler {

    const CLASS_NAME = "spark\\view\\ViewHandler";

    abstract public function isView($viewModel);

    abstract public function handleView($viewModel, RequestData $request);

}