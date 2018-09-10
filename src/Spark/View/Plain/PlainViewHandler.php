<?php
/**
 *
 *
 * Date: 06.07.14
 * Time: 18:11
 */

namespace Spark\View\Plain;


use Spark\Http\Request;
use Spark\Core\Routing\RequestData;
use Spark\View\ViewHandler;
use Spark\View\ViewModel;

class PlainViewHandler extends ViewHandler {

    public const NAME = 'plainViewHandler';

    public function isView($viewModel): bool {
        return $viewModel instanceof PlainViewModel;
    }

    public function handleView($viewModel, RequestData $request): void {
        if ($viewModel instanceof PlainViewModel) {
            echo $viewModel->getContent();
        }
    }
}