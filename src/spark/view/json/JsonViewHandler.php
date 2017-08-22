<?php
/**
 * Created by PhpStorm.
 * User: primosz67
 * Date: 06.07.14
 * Time: 18:01
 */

namespace spark\view\json;


use spark\common\data\ContentType;
use spark\http\ResponseHelper;
use spark\http\Request;
use spark\view\ViewHandler;
use spark\view\ViewModel;

class JsonViewHandler extends ViewHandler {

    const NAME = "jsonViewHandler";

    public function isView($viewModel) {
        return $viewModel instanceof JsonViewModel;
    }

    public function handleView($viewModel, Request $request) {
        ResponseHelper::setContentType(ContentType::$APPLICATION_JSON);
        echo json_encode($viewModel->getParams(), JSON_NUMERIC_CHECK );
        exit;
    }
}