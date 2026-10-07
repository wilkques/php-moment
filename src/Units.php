<?php

namespace Wilkques\Moment;

class Units
{
    protected static $aliases = array(
        'D' => 'date', 'dates' => 'date', 'date' => 'date',
        'd' => 'day', 'days' => 'day', 'day' => 'day',
        'e' => 'weekday', 'weekdays' => 'weekday', 'weekday' => 'weekday',
        'E' => 'isoWeekday', 'isoweekdays' => 'isoWeekday', 'isoweekday' => 'isoWeekday',
        'DDD' => 'dayOfYear', 'dayofyears' => 'dayOfYear', 'dayofyear' => 'dayOfYear',
        'h' => 'hour', 'hours' => 'hour', 'hour' => 'hour',
        'ms' => 'millisecond', 'milliseconds' => 'millisecond', 'millisecond' => 'millisecond',
        'm' => 'minute', 'minutes' => 'minute', 'minute' => 'minute',
        'M' => 'month', 'months' => 'month', 'month' => 'month',
        'Q' => 'quarter', 'quarters' => 'quarter', 'quarter' => 'quarter',
        's' => 'second', 'seconds' => 'second', 'second' => 'second',
        'gg' => 'weekYear', 'weekyears' => 'weekYear', 'weekyear' => 'weekYear',
        'GG' => 'isoWeekYear', 'isoweekyears' => 'isoWeekYear', 'isoweekyear' => 'isoWeekYear',
        'w' => 'week', 'weeks' => 'week', 'week' => 'week',
        'W' => 'isoWeek', 'isoweeks' => 'isoWeek', 'isoweek' => 'isoWeek',
        'y' => 'year', 'years' => 'year', 'year' => 'year',
    );

    protected static $priorities = array(
        'date' => 9, 'day' => 11, 'weekday' => 11, 'isoWeekday' => 11, 'dayOfYear' => 4,
        'hour' => 13, 'millisecond' => 16, 'minute' => 14, 'month' => 8, 'quarter' => 7,
        'second' => 15, 'weekYear' => 1, 'isoWeekYear' => 1, 'week' => 5, 'isoWeek' => 5, 'year' => 1,
    );

    /** @return string|null canonical unit name, or null when unknown */
    public static function normalize($units)
    {
        if (!is_string($units)) {
            return null;
        }
        if (isset(self::$aliases[$units])) {
            return self::$aliases[$units];
        }
        $lower = strtolower($units);

        return isset(self::$aliases[$lower]) ? self::$aliases[$lower] : null;
    }

    public static function normalizeObject(array $input)
    {
        $out = array();
        foreach ($input as $key => $value) {
            $unit = self::normalize($key);
            if ($unit !== null) {
                $out[$unit] = $value;
            }
        }

        return $out;
    }

    /** Units of an object ordered the way moment().set({...}) applies them. */
    public static function prioritized(array $normalized)
    {
        $units = array_keys($normalized);
        $order = array();
        foreach ($units as $i => $unit) {
            $order[] = array(self::$priorities[$unit], $i, $unit);
        }
        sort($order);

        $out = array();
        foreach ($order as $row) {
            $out[] = $row[2];
        }

        return $out;
    }
}
