<?php
/**
 * Created by PhpStorm.
 * User: primosz67
 * Date: 06.07.14
 * Time: 18:11
 */

namespace Spark\View\Plain;


use Spark\Http\Request;
use Spark\Core\Routing\RequestData;
use Spark\View\ViewHandler;
use Spark\View\ViewModel;

class PlainViewHandler extends ViewHandler {

    const NAME = "plainViewHandler";

    public function isView($viewModel) {
        return $viewModel instanceof PlainViewModel;
    }

    public function handleView($viewModel, RequestData $request) {
        if ($viewModel instanceof PlainViewModel) {
            echo $viewModel->getContent();
        }
    }
}