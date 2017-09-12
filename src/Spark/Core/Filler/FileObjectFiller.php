<?php


namespace Spark\Core\Filler;


use Spark\Core\Annotation\Inject;
use Spark\Http\RequestProvider;
use Spark\Http\Utils\RequestUtils;
use Spark\Upload\FileObject;
use Spark\Utils\FileUtils;

class FileObjectFiller implements Filler {

    /**
     * @Inject
     * @var RequestProvider
     */
    private $requestProvider;


    public function getValue($name, $type) {

        if ( $type === FileObject::class) {
            return $this->requestProvider->getRequest()
                ->getFileObject($name);
        }
        return null;
    }

    /**
     * @return int
     */
    public function getOrder() {
        return 102;
    }
}