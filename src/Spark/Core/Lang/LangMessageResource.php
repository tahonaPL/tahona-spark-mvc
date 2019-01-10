<?php
/**
 *
 *
 * Date: 14.03.15
 * Time: 19:12
 */

namespace Spark\Core\Lang;


use Spark\Common\Collection\FluentIterables;
use Spark\Common\Optional;
use Spark\Config;
use Spark\Core\Annotation\Inject;
use Spark\Core\Annotation\PostConstruct;
use Spark\Core\Provider\BeanProvider;
use Spark\Core\Resource\ResourcePath;
use Spark\Http\RequestProvider;
use Spark\Utils\Functions;
use Spark\Utils\UrlUtils;
use Spark\Upload\FileObject;
use Spark\Utils\Collections;
use Spark\Utils\FileUtils;
use Spark\Utils\Objects;
use Spark\Utils\StringUtils;

class LangMessageResource {

    public const NAME = 'langMessageResource';

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
    private $languages;

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
        }

        return $this->handleMessage($code, $params);
    }

    /**
     * @param $code
     * @param $params
     * @return string
     */
    private function handleMessage($code, $params) {
        $message = $this->messages[$this->getLang()][$code];

        if (Objects::isNull($message)) {
            $optionalLang = Collections::stream($this->languages)
                ->findFirst(function ($lang) use ($code) {
                    return null !== $this->messages[$lang][$code];
                });

            if ($optionalLang->isPresent()) {
                $message = $this->messages[$optionalLang->get()][$code];
            } else {
                $message = $this->messageErrorCode($code);
            }
        }

        if (Collections::isNotEmpty($params)) {
            foreach ($params as $k => $v) {
                $replaceTag = StringUtils::join('', array('{', $k, '}'));
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
        $code = Collections::stream($this->messages)
            ->findFirst(function ($array) use ($code) {
                return Collections::hasKey($array, $code);
            });

        return $code->isPresent();
    }

    /**
     * @param $code
     * @return string
     */
    private function messageErrorCode($code) {
        return '!' . $code . '!';
    }

    public function addResources($resourcePaths = array()) {
        $appPaths = $this->config->getProperty('app.paths');

        /** @var LangResourcePath $resourcePath */
        foreach ($resourcePaths as $resourcePath) {
            $paths = $resourcePath->getPaths();

            foreach ($paths as $key => $pathArr) {
                foreach ($pathArr as $path) {
                    $elements = FluentIterables::of($appPaths)
                        ->findFirst(function ($rootPath) use ($path) {
                            return FileUtils::exist($rootPath . '/src' . $path);
                        })
                        ->map(function ($rootPath) use ($path) {
                            return parse_ini_file($rootPath . '/src' . $path);
                        })
                        ->get();

                    Collections::addAllOrReplace($this->messages[$key], $elements);
                }
            }
        }
        $this->languages = Collections::getKeys($this->messages);
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