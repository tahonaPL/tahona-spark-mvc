<?php
/**
 * Date: 02.06.18
 * Time: 15:53
 */

namespace Spark\Utils;


use Spark\Common\Collection\FluentIterables;

class RandomUtils {

    public static function nextInt($minInclusive = 0, $maxInclusive = PHP_INT_MAX): int {
        return random_int($minInclusive, $maxInclusive);
    }

    public static function nextBoolean(): bool {
        return (bool)random_int(0, 1) === 1;
    }

    public static function nextFloat(float $minInclusive = 0.0, float $maxInclusive = PHP_INT_MAX): float {
        return (float)random_int($minInclusive, $maxInclusive);
    }

    public static function random(array $arr) {
        if (Collections::isNotEmpty($arr)) {
            $keys = Collections::getKeys($arr);
            return $arr[$keys[random_int(0, Collections::size($keys) - 1)]];
        }
        return null;
    }

    public static function randomElements(int $resultCount, array $arr): array {
        if ($resultCount === 0) {
            return [];
        }
        --$resultCount;

        $elementsCount = \count($arr) - 1;
        $array = FluentIterables::of(Collections::range(0, $resultCount))
            ->map(function () use ($arr, $elementsCount) {
                $indexToRemove = random_int(0, $elementsCount);
                $result = $arr[$indexToRemove];
                return $result;
            })
            ->convertToMap(function ($obj) {
                if (Objects::isPrimitive($obj)) {
                    return $obj;
                }
                return spl_object_hash($obj);
            })
            ->getList();

        return $array;
    }
}