<?php

namespace Wilkques\Moment\Timezone;

/**
 * A time zone from moment-timezone's packed data: parallel lists of abbreviations, offsets
 * (minutes, positive WEST of UTC like moment-timezone) and the instant (ms) each span ends.
 */
class Zone
{
    public $name;
    public $abbrs = array();
    public $offsets = array();
    public $untils = array();
    public $population = 0;

    public function __construct($packed = null)
    {
        if ($packed) {
            $this->set(self::unpack($packed));
        }
    }

    public function set(array $unpacked)
    {
        $this->name = $unpacked['name'];
        $this->abbrs = $unpacked['abbrs'];
        $this->untils = $unpacked['untils'];
        $this->offsets = $unpacked['offsets'];
        $this->population = $unpacked['population'];

        return $this;
    }

    public static function charCodeToInt($code)
    {
        if ($code > 96) {
            return $code - 87;
        } elseif ($code > 64) {
            return $code - 29;
        }

        return $code - 48;
    }

    public static function unpackBase60($string)
    {
        $parts = explode('.', $string);
        $whole = $parts[0];
        $fractional = isset($parts[1]) ? $parts[1] : '';
        $multiplier = 1;
        $out = 0;
        $sign = 1;
        $i = 0;

        if ($string !== '' && $string[0] === '-') {
            $i = 1;
            $sign = -1;
        }
        for ($len = strlen($whole); $i < $len; $i++) {
            $out = 60 * $out + self::charCodeToInt(ord($whole[$i]));
        }
        for ($i = 0, $len = strlen($fractional); $i < $len; $i++) {
            $multiplier = $multiplier / 60;
            $out += self::charCodeToInt(ord($fractional[$i])) * $multiplier;
        }

        return $out * $sign;
    }

    protected static function arrayToInt(array $array)
    {
        foreach ($array as $i => $v) {
            $array[$i] = self::unpackBase60($v);
        }

        return $array;
    }

    public static function unpack($string)
    {
        $data = explode('|', $string);
        $offsets = self::arrayToInt(explode(' ', $data[2]));
        $indices = self::arrayToInt(str_split($data[3]));
        $untils = self::arrayToInt(explode(' ', $data[4]));

        $length = count($indices);
        $prev = 0;
        for ($i = 0; $i < $length; $i++) {
            $untils[$i] = round($prev + (isset($untils[$i]) ? $untils[$i] : 0) * 60000);
            $prev = $untils[$i];
        }
        $untils[$length - 1] = INF;
        $untils = array_slice($untils, 0, $length);

        $abbrs = explode(' ', $data[1]);
        $outAbbrs = array();
        $outOffsets = array();
        foreach ($indices as $i => $index) {
            $outAbbrs[$i] = $abbrs[$index];
            $outOffsets[$i] = $offsets[$index];
        }

        return array(
            'name' => $data[0],
            'abbrs' => $outAbbrs,
            'offsets' => $outOffsets,
            'untils' => $untils,
            'population' => (int) (isset($data[5]) ? (float) $data[5] : 0), // "74e5" is 7400000 (PHP < 7.1 casts it to 74)
        );
    }

    protected static function closest($num, array $arr)
    {
        $len = count($arr);
        if ($num !== $num) {
            return $len > 1 ? 1 : 0; // every comparison with NaN is false, which lands the binary search on index 1
        }
        if ($num < $arr[0]) {
            return 0;
        } elseif ($len > 1 && $arr[$len - 1] === INF && $num >= $arr[$len - 2]) {
            return $len - 1;
        } elseif ($num >= $arr[$len - 1]) {
            return -1;
        }

        $lo = 0;
        $hi = $len - 1;
        while ($hi - $lo > 1) {
            $mid = (int) floor(($lo + $hi) / 2);
            if ($arr[$mid] <= $num) {
                $lo = $mid;
            } else {
                $hi = $mid;
            }
        }

        return $hi;
    }

    /** Index of the span containing the instant $timestamp (ms), or null. */
    public function index($timestamp)
    {
        $i = self::closest($timestamp, $this->untils);

        return $i >= 0 ? $i : null;
    }

    /** @return array country codes whose zone list contains this zone */
    public function countries()
    {
        $out = array();
        foreach (Tz::countryMap() as $code => $zones) {
            if (in_array($this->name, $zones)) {
                $out[] = $code;
            }
        }

        return $out;
    }

    /**
     * Offset (minutes, positive west) in effect when the zone's wall clock reads $timestamp,
     * treating the wall time as if it were UTC ms. Gaps and overlaps follow moment-timezone's
     * moveInvalidForward / moveAmbiguousForward settings.
     */
    public function parse($timestamp, $moveAmbiguousForward = null, $moveInvalidForward = null)
    {
        $moveAmbiguousForward = $moveAmbiguousForward === null ? Tz::$moveAmbiguousForward : $moveAmbiguousForward;
        $moveInvalidForward = $moveInvalidForward === null ? Tz::$moveInvalidForward : $moveInvalidForward;

        $max = count($this->untils) - 1;
        for ($i = 0; $i < $max; $i++) {
            $offset = $this->offsets[$i];
            $offsetNext = $this->offsets[$i + 1];

            if ($offset < $offsetNext && $moveAmbiguousForward) {
                $offset = $offsetNext;
            } elseif ($offset > $offsetNext && $moveInvalidForward) {
                $offset = $offsetNext;
            }

            if ($timestamp < $this->untils[$i] - ($offset * 60000)) {
                return $this->offsets[$i];
            }
        }

        return $this->offsets[$max];
    }

    public function abbr($timestamp)
    {
        $i = $this->index($timestamp);

        return $i === null ? null : $this->abbrs[$i];
    }

    /** @deprecated use utcOffset() */
    public function offset($timestamp)
    {
        return $this->utcOffset($timestamp);
    }

    /** Offset in minutes, positive west of UTC, at the instant $timestamp (ms). */
    public function utcOffset($timestamp)
    {
        $i = $this->index($timestamp);

        return $i === null ? null : $this->offsets[$i];
    }
}
