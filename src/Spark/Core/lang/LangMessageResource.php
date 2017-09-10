<?php
/**
 * Created by PhpStorm.
 * User: primosz67
 * Date: 14.03.15
 * Time: 19:12
 */

namespace Spark\Core\lang;


use Spark\Common\Optional;
use Spark\Config;
use Spark\Core\annotation\Inject;
use Spark\Core\annotation\PostConstruct;
use Spark\Core\provider\BeanProvider;
use Spark\Core\resource\ResourcePath;
use Spark\Http\RequestProvider;
use Spark\Utils\Functions;
use Spark\Utils\UrlUtils;
use Spark\Upload\FileObject;
use Spark\Utils\Collections;
use Spark\Utils\FileUtils;
use Spark\Utils\Objects;
use Spark\Utils\StringUtils;

class LangMessageResource {

    const NAME = "langMessageResource";

    /**
     * @Inject
     * @var Config
     */
    private $config;

    /**
     * @Inject()
     * @var BeanProvider
     */
    private $beanProvider;

    private $messages = array();

    private $filePath;

    public function __construct($filePath = array()) {
        $this->filePath = $filePath;
    }

    /**
     * @PostConstruct()
     */
    public function init() {
        $this->addResources($this->filePath);
    }

    /**
     * @param $code
     */
    public function get($code, $params = array()) {
        if (!$this->hasCode($code)) {
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
        $message = $this->messages[$this->getLang()][$code];

        if (Objects::isNull($message)) {
            $optionalLang = Collections::builder()
                ->addAll(Collections::getKeys($this->messages))
                ->filter(function ($lang) use ($code) {
                    $m = $this->messages[$lang][$code];
                    return Objects::isNotNull($m);
                })
                ->findFirst();


            if ($optionalLang->isPresent()) {
                $message = $this->messages[$optionalLang->get()][$code];
            } else {
                $message = $this->messageErrorCode($code);
            }
        }

        if (Collections::isNotEmpty($params)) {
            foreach ($params as $k => $v) {
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
        return "!" . $code . "!";
    }

    public function addResources($resourcePaths = array()) {
        /** @var LangResourcePath $resourcePath */
        foreach ($resourcePaths as $resourcePath) {
            $paths = $resourcePath->getPaths();

            foreach ($paths as $key => $pathArr) {
                foreach ($pathArr as $path) {
                    $elements = parse_ini_file($this->config->getProperty("src.path") . "" . $path);
                    Collections::addAllOrReplace($this->messages[$key], $elements);
                }
            }
        }
    }

    private function getLang() {
        /** @var LangKeyProvider $langKeyProvider */
        $langKeyProvider = $this->beanProvider->getBean(LangKeyProvider::NAME);

        return Optional::of($langKeyProvider)
            ->map(Functions::get(LangKeyProvider::D_LANG))
            ->orElse($this->getFirstResourceKey());
    }

    private function getFirstResourceKey() {
        $keys = Collections::getKeys($this->messages);

        return Collections::builder($keys)
            ->findFirst()->getOrNull();
    }

} 