<?php
/**
 * Created by PhpStorm.
 * User: primosz67
 * Date: 06.07.14
 * Time: 18:11
 */

namespace spark\view\plain;


use spark\http\Request;
use spark\view\ViewHandler;
use spark\view\ViewModel;

class PlainViewHandler extends ViewHandler {

    const NAME = "plainViewHandler";

    public function isView(ViewModel $viewModel) {
        return $viewModel instanceof PlainViewModel;
    }

    public function handleView(ViewModel $viewModel, Request $request) {
        if ($viewModel instanceof PlainViewModel) {
            echo $viewModel->getContent();
        }
    }
}