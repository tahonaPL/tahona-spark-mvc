<?php
/**
 * Created by PhpStorm.
 * User: primosz67
 * Date: 14.03.15
 * Time: 19:12
 */

namespace spark\lang;


use spark\core\di\Inject;
use spark\http\RequestProvider;
use spark\utils\UrlUtils;
use spark\upload\FileObject;
use spark\utils\Collections;
use spark\utils\FileUtils;
use spark\utils\Objects;
use spark\utils\StringUtils;

class LangMessageResource {

    const NAME = "langMessageResource";

    /**
     * @Inject
     * @var RequestProvider
     */
    private $requestProvider;

    private $messages = array();

    function __construct($filePath = array()) {
        foreach ($filePath as $key => $pathArr) {
            foreach ($pathArr as $path) {
                $elements = parse_ini_file(FileUtils::getAbsolutePath($path));
                Collections::addAllOrReplace($this->messages[$key], $elements);
            }
        }
    }

    /**
     * @param $code
     */
    public function get($code, $params = array()) {
        if ( false === $this->hasCode($code)) {
            return $this->messageErrorCode($code);
        } else {
            return $this->handleMessage($code, $params);
        }
    }

    /**
     * @param $code
     * @param $params
     * @return string
     */
    private function handleMessage($code, $params) {
        $request = $this->requestProvider->getRequest();
        $message = $this->messages[$request->getLang()][$code];

        if (Objects::isNull($message)) {
            $optionalLang = Collections::builder()
                ->addAll(Collections::getKeys($this->messages))
                ->filter(function ($lang) use ($code) {
                    $m = $this->messages[$lang][$code];
                    return Objects::isNotNull($m);
                })
                ->findFirst();


            if ($optionalLang->isPresent()) {
                $message =  $this->messages[$optionalLang->get()][$code];
            } else {
                $message = $this->messageErrorCode($code);
            }
        }

        if (Collections::isNotEmpty($params)) {
            foreach($params as $k => $v) {
                $replaceTag = StringUtils::join("", array("{", $k, "}"));
                $message = StringUtils::replace($message, $replaceTag, $v);
            }
            return $message;
        }

        return $message;
    }

    /**
     * @param $code
     * @return bool
     */
    private function hasCode($code) {
        $codes = Collections::builder($this->messages)
            ->filter(function ($array) use ($code) {
                return Collections::hasKey($array, $code);
            })->get();

        return Collections::isNotEmpty($codes);
    }

    /**
     * @param $code
     * @return string
     */
    private function messageErrorCode($code) {
        return "!".$code."!";
    }

} 