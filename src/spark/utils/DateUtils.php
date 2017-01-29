<?php

namespace spark\utils;

use DateTime;
use spark\utils\Asserts;

class DateUtils {

    /**
     * @return DateTime
     */
    public static function now() {
        return new DateTime();
    }

    /**
     *  $date is after $afterDate
     *
     * @param DateTime $date
     * @param DateTime $afterDate
     * @return bool
     */
    public static function isAfter(DateTime $date, DateTime $afterDate) {
        return $date > $afterDate;
    }

    public static function isAfterEqual(DateTime $date, DateTime $afterDate) {
        return $date >= $afterDate;
    }

    /**
     * $date is before $beforeDate
     *
     * @param DateTime $date
     * @param DateTime $beforeDate
     * @return bool
     */
    public static function isBefore(DateTime $date, DateTime $beforeDate) {
        return $date < $beforeDate;
    }

    public static function isBeforeEqual(DateTime $date, DateTime $beforeDate) {
        return $date <= $beforeDate;
    }


    public static function format(DateTime $date, $format) {
        Asserts::notNull($date, "Date cannot be null.");
        Asserts::notNull($format, "Date Format cannot be null.");
        return $date->format($format);
    }

    public static function toDate($value, $format) {
        return DateTime::createFromFormat($format, $value);
    }

    public static function getDaysBetween(DateTime $from, DateTime $to) {
        $diff = $to->getTimestamp() - $from->getTimestamp();
        return floor($diff / (24 * 60 * 60));
    }


}
