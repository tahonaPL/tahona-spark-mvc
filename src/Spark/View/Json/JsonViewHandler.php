<?php
/**
 * Created by PhpStorm.
 * User: primosz67
 * Date: 06.07.14
 * Time: 18:01
 */

namespace Spark\View\Json;


use Spark\Common\Data\ContentType;
use Spark\Http\HttpCode;
use Spark\Http\ResponseHelper;
use Spark\Http\Request;
use Spark\Core\Routing\RequestData;
use Spark\Utils\JsonUtils;
use Spark\View\ViewHandler;
use Spark\View\ViewModel;

class JsonViewHandler extends ViewHandler {

    const NAME = "jsonViewHandler";

    public function isView($viewModel) {
        return $viewModel instanceof JsonViewModel;
    }

    public function handleView($viewModel, RequestData $request) {
        ResponseHelper::setCode(HttpCode::$OK);
        ResponseHelper::setContentType(ContentType::$APPLICATION_JSON);

        echo JsonUtils::toJson($viewModel->getObj());
//        echo json_encode(, JSON_NUMERIC_CHECK );
        exit;
    }
}