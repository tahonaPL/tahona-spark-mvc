<?php
/**
 * Created by PhpStorm.
 * User: crownclown67
 * Date: 08.06.17
 * Time: 21:01
 */

namespace Spark\Cache\Service;


use Spark\Cache\Cache;
use Spark\Common\Optional;
use Spark\Core\Annotation\Inject;
use Spark\Utils\Collections;
use Spark\Utils\DateUtils;
use Spark\Utils\Objects;
use Spark\Utils\StringFunctions;
use Spark\Utils\StringUtils;

class CacheService {
    const NAME = "cacheService";

    /**
     * @Inject()
     * @var CacheProvider
     */
    private $cacheProvider;

    private $localCache = array();

    private $cacheDefinitions = array();

    public function addDefinition($className, $methodName, $cache, $key, $time = null) {
        $definition = [];
        $definition["cache"] = $cache;
        $definition["key"] = $key;

        if (Objects::isNotNull($time)) {
            $definition["time"] = $time;
        }

        $key = $className . "#" . $methodName;
        $this->cacheDefinitions[$key] = $definition;
    }

    public function getCached($methodName, $arguments) {

        if ($this->isCacheable($methodName)) {
            $definition = $this->getDefinition($methodName);
            $key = $methodName.$this->getKey($arguments, $definition);

            if (Collections::hasKey($this->localCache, $key)) {
                /** @var CachedResult $cachedResult */
                $cachedResults = $this->localCache[$key];
            } else {
                /** @var CachedResult $cachedResults */
                $cacheName = $definition["cache"];
                $cacheInstance = $this->cacheProvider->getCache($cacheName);
                $cachedResults = $cacheInstance->get($key);

                $this->localCache[$key] = $cachedResults;
            }

            if (Objects::isNotNull($cachedResults) && $cachedResults && $this->isNotExpired($definition, $cachedResults)) {
//                echo DateUtils::format($cachedResults->getCreateDate(), "Y-m-d H:i:s");
                return $cachedResults->getResults();
            }
        }

        return null;
    }

    private function buildKey($key, $arguments = array()) {
        $cleared = Optional::of($key)
            ->map(StringFunctions::replace("{", " "))
            ->map(StringFunctions::replace("}", ""))
            ->map(StringFunctions::trim())
            ->get();

        $splitted = StringUtils::split($cleared, " ");

        $key = "";
        foreach ($splitted as $val) {
            $accessor = StringUtils::split($val, ".");
            $size = Collections::size($accessor);

            $objIndex = $accessor[0];
            $obj = Collections::getValueOrDefault($arguments, $objIndex, $objIndex);

            if ($size >= 2) {
                $subList = Collections::subList($accessor, 1, $size-1);
                foreach ($subList as $property) {
                    $obj = Objects::invokeGetMethod($obj, $property);
                }
            }

            $key .= $obj;
        }

        return $key;
    }

    public function cache($methodName, $arguments, $result) {
        $definition = $this->getDefinition($methodName);
        $key = $methodName.$this->getKey($arguments, $definition);

        $cacheName = $definition["cache"];
        $this->cacheProvider->getCache($cacheName)->put($key, new CachedResult(DateUtils::now(), $result));
        return $result;
    }

    /**
     * @param $methodName
     * @return array|null
     */
    private function getDefinition($methodName) {
        return Collections::getValue($this->cacheDefinitions, $methodName);
    }

    /**
     * @param $arguments
     * @param $definition
     * @return string
     */
    private function getKey($arguments, $definition) {
        return $this->buildKey($definition["key"], $arguments);
    }

    private function isNotExpired($definition, CachedResult $cachedResults) {
        $time = Collections::getValue($definition, "time");
        if (Objects::isNotNull($time)) {
            $expireDate = $cachedResults->getCreateDate()->modify("+ $time minutes");

            return DateUtils::isAfter($expireDate, DateUtils::now());
        }
        return true;
    }

    public function isCacheable($cacheMethodName) {
        return Collections::hasKey($this->cacheDefinitions, $cacheMethodName);
    }
}