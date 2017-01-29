<?php
/**
 * Created by PhpStorm.
 * User: primosz67
 * Date: 06.07.14
 * Time: 17:51
 */

namespace spark\view;


use spark\http\Request;
use spark\view\ViewModel;

abstract class ViewHandler {
    private $viewHandlerProvider;

    abstract public function isView(ViewModel $viewModel);

    abstract public function handleView(ViewModel $viewModel, Request $request);

    public function setViewHandlerProvider(ViewHandlerProvider $viewHandlerProvider) {
        $this->viewHandlerProvider = $viewHandlerProvider;
    }

    /**
     * @return ViewHandlerProvider
     */
    protected function getViewHandlerProvider() {
        return $this->viewHandlerProvider;
    }


}