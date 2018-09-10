<?php
/**
 *
 *
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

    public const NAME = 'jsonViewHandler';

    public function isView($viewModel): bool {
        return $viewModel instanceof JsonViewModel;
    }

    public function handleView($viewModel, RequestData $request): void {
        ResponseHelper::setCode(HttpCode::OK);
        ResponseHelper::setContentType(ContentType::APPLICATION_JSON);

        echo JsonUtils::toJson($viewModel->getObj());
//        echo json_encode(, JSON_NUMERIC_CHECK );
        exit;
    }
}