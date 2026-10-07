<?php

namespace Wilkques\Moment;

/**
 * Pure integer calendar math on the proleptic Gregorian calendar (UTC, no time zones).
 */
class Calendar
{
    public static function isLeapYear($year)
    {
        return ($year % 4 == 0 && $year % 100 != 0) || $year % 400 == 0;
    }

    public static function daysInYear($year)
    {
        return self::isLeapYear($year) ? 366 : 365;
    }

    public static function daysInMonth($year, $month)
    {
        $mod = self::mod($month, 12);
        $year += (int) floor($month / 12);

        return $mod === 1 ? (self::isLeapYear($year) ? 29 : 28) : 31 - (($mod % 7) % 2);
    }

    public static function mod($a, $b)
    {
        // fmod, not %: % casts to int, which breaks on 32-bit PHP for millisecond timestamps
        $r = fmod($a, $b);

        return self::int($r < 0 ? $r + $b : $r);
    }

    /**
     * An integral number as an int when it fits (always, on 64-bit PHP), else as the float itself:
     * a float holds every integer up to 2^53 exactly, which covers JavaScript's whole date range
     * even where PHP_INT_MAX is only 2^31 - 1.
     */
    public static function int($n)
    {
        if (is_int($n) || $n !== $n || $n > PHP_INT_MAX || $n < -PHP_INT_MAX) {
            return $n;
        }

        return (int) $n;
    }

    /**
     * JavaScript's unary plus: a number stays itself, a string is trimmed and read as decimal, 0x / 0o / 0b
     * integers or Infinity, '' is 0, anything else is NaN.
     */
    public static function toNumber($v)
    {
        if (is_int($v) || is_float($v)) {
            return $v;
        }
        if (is_bool($v)) {
            return $v ? 1 : 0;
        }
        if ($v === null) {
            return 0;
        }
        if (!is_string($v)) {
            return NAN;
        }
        $s = trim($v, " \t\n\r\x0B\x0C\xC2\xA0");
        if ($s === '') {
            return 0;
        }
        if (preg_match('/^[+-]?(\d+\.?\d*|\.\d+)([eE][+-]?\d+)?$/', $s)) {
            return $s + 0;
        }
        if (preg_match('/^0[xX][0-9a-fA-F]+$/', $s)) {
            return hexdec(substr($s, 2));
        }
        if (preg_match('/^0[oO][0-7]+$/', $s)) {
            return octdec(substr($s, 2));
        }
        if (preg_match('/^0[bB][01]+$/', $s)) {
            return bindec(substr($s, 2));
        }
        if (preg_match('/^[+-]?Infinity$/', $s)) {
            return $s[0] === '-' ? -INF : INF;
        }

        return NAN;
    }

    /** !isNaN(+$v) */
    public static function isNumeric($v)
    {
        $n = self::toNumber($v);

        return !(is_float($n) && is_nan($n));
    }

    /** Math.round: halves go toward +Infinity (PHP's round() sends them away from zero) */
    public static function round($n)
    {
        return floor($n + 0.5);
    }

    /** Truncate toward zero like JavaScript's toInt, keeping large values as floats (see int()). */
    public static function trunc($n)
    {
        return self::int($n < 0 ? ceil($n) : floor($n));
    }

    /** An integral number as a plain digit string, never "1.7E+12". */
    public static function str($n)
    {
        return is_int($n) ? (string) $n : sprintf('%.0f', $n);
    }

    /**
     * Days since 1970-01-01 for a year/month(0-11)/day; month and day may overflow like JS Date.UTC.
     */
    public static function daysFromCivil($y, $m, $d)
    {
        $y += (int) floor($m / 12);
        $m = self::mod($m, 12) + 1;
        $y -= $m <= 2 ? 1 : 0;
        $era = (int) floor($y / 400);
        $yoe = $y - $era * 400;
        $doy = (int) floor((153 * ($m + ($m > 2 ? -3 : 9)) + 2) / 5);
        $doe = $yoe * 365 + (int) floor($yoe / 4) - (int) floor($yoe / 100) + $doy;

        return $era * 146097 + $doe - 719468 + ($d - 1);
    }

    /**
     * @return array(year, month0, day)
     */
    public static function civilFromDays($z)
    {
        $z += 719468;
        $era = (int) floor($z / 146097);
        $doe = $z - $era * 146097;
        $yoe = (int) floor(($doe - floor($doe / 1460) + floor($doe / 36524) - floor($doe / 146096)) / 365);
        $y = $yoe + $era * 400;
        $doy = $doe - (365 * $yoe + (int) floor($yoe / 4) - (int) floor($yoe / 100));
        $mp = (int) floor((5 * $doy + 2) / 153);
        $d = $doy - (int) floor((153 * $mp + 2) / 5) + 1;
        $m = $mp < 10 ? $mp + 3 : $mp - 9;

        return array($m <= 2 ? $y + 1 : $y, $m - 1, $d);
    }

    /** ms since epoch for UTC fields (like Date.UTC). */
    public static function utcMs($y, $m, $d, $h = 0, $i = 0, $s = 0, $ms = 0)
    {
        return (self::daysFromCivil($y, $m, $d) * 86400 + $h * 3600 + $i * 60 + $s) * 1000 + $ms;
    }

    /**
     * Split ms-since-epoch into UTC fields.
     *
     * @return array(year, month0, date, hour, minute, second, ms, weekday)
     */
    public static function fields($ms)
    {
        $days = (int) floor($ms / 86400000);
        $rem = $ms - $days * 86400000;
        $c = self::civilFromDays($days);

        return array(
            $c[0], $c[1], $c[2],
            (int) floor($rem / 3600000),
            (int) floor(($rem % 3600000) / 60000),
            (int) floor(($rem % 60000) / 1000),
            (int) ($rem % 1000),
            self::mod($days + 4, 7),
        );
    }

    /**
     * Days since the epoch of createUTCDate(y, m, d), including its quirk: for years 0-99 JS builds the date
     * in year y+400 and then only swaps the year back, so a day that overflows out of year y lands in the
     * same month and day of year y, not in the neighbouring year.
     */
    public static function createUtcDays($y, $m, $d)
    {
        if ($y >= 0 && $y < 100) {
            $c = self::civilFromDays(self::daysFromCivil($y + 400, $m, $d));

            return self::daysFromCivil($y, $c[1], $c[2]);
        }

        return self::daysFromCivil($y, $m, $d);
    }

    public static function firstWeekOffset($year, $dow, $doy)
    {
        $fwd = 7 + $dow - $doy;
        $fwdlw = (7 + self::mod(self::createUtcDays($year, 0, $fwd) + 4, 7) - $dow) % 7;

        return -$fwdlw + $fwd - 1;
    }

    public static function weeksInYear($year, $dow, $doy)
    {
        return (self::daysInYear($year) - self::firstWeekOffset($year, $dow, $doy)
            + self::firstWeekOffset($year + 1, $dow, $doy)) / 7;
    }

    /** @return array(week, year) */
    public static function weekOfYear($year, $dayOfYear, $dow, $doy)
    {
        $week = (int) floor(($dayOfYear - self::firstWeekOffset($year, $dow, $doy) - 1) / 7) + 1;

        if ($week < 1) {
            $y = $year - 1;

            return array($week + self::weeksInYear($y, $dow, $doy), $y);
        }
        if ($week > self::weeksInYear($year, $dow, $doy)) {
            return array($week - self::weeksInYear($year, $dow, $doy), $year + 1);
        }

        return array($week, $year);
    }

    /** @return array(year, dayOfYear) */
    public static function dayOfYearFromWeeks($year, $week, $weekday, $dow, $doy)
    {
        $localWeekday = (7 + $weekday - $dow) % 7;
        $dayOfYear = 1 + 7 * ($week - 1) + $localWeekday + self::firstWeekOffset($year, $dow, $doy);

        if ($dayOfYear <= 0) {
            return array($year - 1, self::daysInYear($year - 1) + $dayOfYear);
        }
        if ($dayOfYear > self::daysInYear($year)) {
            return array($year + 1, $dayOfYear - self::daysInYear($year));
        }

        return array($year, $dayOfYear);
    }
}
