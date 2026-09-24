<?php

declare(strict_types=1);

namespace app\core\helpers;

use DateTime;

class Date
{

    public static function getDaysBetween2Dates(DateTime $date1, DateTime $date2, bool $absolute = true): bool|int
    {
        $interval = $date2->diff($date1);
        // if we have to take in account the relative position (!$absolute) and the relative position is negative,
        // we return negative value otherwise, we return the absolute value
        return (!$absolute and $interval->invert) ? -$interval->days : $interval->days;
    }

    public static function formatDate(string $date, string $format_from = 'm-d-Y', string $format_to = 'Y-m-d'): string
    {
        $d = DateTime::createFromFormat($format_from, $date);
        return $d->format($format_to);
    }

    public static function isValidDateDB(string $date): bool
    {
        $d = DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') == $date;
    }

    public static function isHoliday(string $date): bool
    {
        $year = date('Y', strtotime($date));

        $holidays = [
            self::observed_date(date('Y-m-d', strtotime("$year-01-01"))),
            date('Y-m-d', strtotime("january $year third monday")),
            date('Y-m-d', strtotime("february $year third monday")),
            date('Y-m-d', strtotime("last monday of May $year")),
            self::observed_date(date('Y-m-d', strtotime("$year-07-04"))),
            date('Y-m-d', strtotime("september $year first monday")),
            date('Y-m-d', strtotime("october $year second monday")),
            self::observed_date(date('Y-m-d', strtotime("$year-11-11"))),
            date('Y-m-d', strtotime("november $year fourth thursday")),
            self::observed_date(date('Y-m-d', strtotime("$year-12-25"))),
        ];

        return in_array($date, $holidays);
    }

    public static function observed_date(string $holiday): int|string
    {
        $day = date("N", strtotime($holiday));

        return match ($day) {
            '6' => date('Y-m-d', strtotime('-1 day', strtotime($holiday))),
            '7' => date('Y-m-d', strtotime('+1 day', strtotime($holiday))),
            default => $holiday,
        };
    }

}