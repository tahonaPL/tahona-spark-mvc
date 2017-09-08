<?php
/**
 * Created by PhpStorm.
 * User: primosz67
 * Date: 06.07.14
 * Time: 17:51
 */

namespace spark\view;


use spark\core\routing\RequestData;

abstract class ViewHandler {

    const CLASS_NAME = "spark\\view\\ViewHandler";

    abstract public function isView($viewModel);

    abstract public function handleView($viewModel, RequestData $request);

}