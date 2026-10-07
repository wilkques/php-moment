<?php

namespace Wilkques\Moment;

if (!interface_exists('JsonSerializable', false)) {
    require_once __DIR__ . '/JsonSerializable.php';
}

use Wilkques\Moment\Locale\Locale;
use Wilkques\Moment\Locale\Registry;

/**
 * moment.duration(): a length of time kept as milliseconds + days + months (the three
 * parts are independent because a month or a day has no fixed length), bubbled into
 * the years/months/days/hours/minutes/seconds/milliseconds getters.
 */
class Duration implements \JsonSerializable
{
    const ASP_NET = '/^(-|\+)?(?:(\d*)[. ])?(\d+):(\d+)(?::(\d+)(\.\d*)?)?$/';
    const ISO = '/^(-|\+)?P(?:([-+]?[0-9,.]*)Y)?(?:([-+]?[0-9,.]*)M)?(?:([-+]?[0-9,.]*)W)?(?:([-+]?[0-9,.]*)D)?(?:T(?:([-+]?[0-9,.]*)H)?(?:([-+]?[0-9,.]*)M)?(?:([-+]?[0-9,.]*)S)?)?$/';

    protected static $ordering = array('year', 'quarter', 'month', 'week', 'day', 'hour', 'minute', 'second', 'millisecond');

    protected static $thresholds = array('ss' => 44, 's' => 45, 'm' => 45, 'h' => 22, 'd' => 26, 'w' => null, 'M' => 11);
    protected static $rounding = array('Wilkques\\Moment\\Calendar', 'round');

    protected $milliseconds = 0;
    protected $days = 0;
    protected $months = 0;
    protected $data = array();
    protected $valid = true;
    protected $locale;

    /**
     * Duration::create(2, 'hours'), create(array('days' => 1, 'hours' => 2)), create('PT2H'),
     * create('1:30:00'), create(1500) (ms), create($duration), create(array('from' => $a, 'to' => $b))
     */
    public static function create($input = null, $key = null)
    {
        $duration = $input;
        $locale = null;
        $valid = null;

        if ($input instanceof self) {
            $duration = array('ms' => $input->milliseconds, 'd' => $input->days, 'M' => $input->months);
            $locale = $input->locale;
            $valid = $input->valid;
        } elseif (is_int($input) || is_float($input) || (is_string($input) && Calendar::isNumeric($input))) {
            $duration = array(($key !== null && $key !== '' ? $key : 'milliseconds') => Calendar::toNumber($input));
        } elseif (is_string($input) && preg_match(self::ASP_NET, $input, $m)) {
            $sign = $m[1] === '-' ? -1 : 1;
            $duration = array(
                'y' => 0,
                'd' => (int) $m[2] * $sign,
                'h' => (int) $m[3] * $sign,
                'm' => (int) $m[4] * $sign,
                's' => (int) (isset($m[5]) ? $m[5] : 0) * $sign,
                'ms' => (int) self::absRound((isset($m[6]) && $m[6] !== '' ? (float) $m[6] : 0) * 1000) * $sign,
            );
        } elseif (is_string($input) && preg_match(self::ISO, $input, $m)) {
            $m = array_pad($m, 9, ''); // PCRE drops trailing groups that didn't match
            $sign = $m[1] === '-' ? -1 : 1;
            $duration = array(
                'y' => self::parseIso($m[2], $sign), 'M' => self::parseIso($m[3], $sign),
                'w' => self::parseIso($m[4], $sign), 'd' => self::parseIso($m[5], $sign),
                'h' => self::parseIso($m[6], $sign), 'm' => self::parseIso($m[7], $sign),
                's' => self::parseIso($m[8], $sign),
            );
        } elseif ($input === null) {
            $duration = array();
        } elseif (is_array($input) && (array_key_exists('from', $input) || array_key_exists('to', $input))) {
            // a missing from/to means now (like undefined); an explicit null is invalid
            $diff = self::momentsDifference(array_key_exists('from', $input) ? Moment::create($input['from']) : Moment::create(),
                array_key_exists('to', $input) ? Moment::create($input['to']) : Moment::create());
            $duration = array('ms' => $diff[0], 'M' => $diff[1]);
        } elseif (!is_array($input)) {
            // like moment, an unrecognised string is an empty (zero) duration
            $duration = array();
        }

        $ret = new self($duration);
        if ($locale !== null) {
            $ret->locale = $locale;
        }
        if ($valid !== null) {
            $ret->valid = $valid;
        }

        return $ret;
    }

    public static function invalid()
    {
        return self::create(array('millisecond' => NAN));
    }

    public static function isDuration($obj)
    {
        return $obj instanceof self;
    }

    protected static function parseIso($value, $sign)
    {
        $res = $value === '' || $value === null ? 0 : (float) str_replace(',', '.', $value);

        return $res * $sign;
    }

    protected static function absRound($n)
    {
        return $n < 0 ? -1 * round(-1 * $n) : round($n);
    }

    // NaN is tested explicitly everywhere below: PHP 5 compares NAN as if it were a number
    protected static function absFloor($n)
    {
        return $n !== $n ? NAN : ($n < 0 ? ceil($n) : floor($n));
    }

    protected static function absCeil($n)
    {
        return $n !== $n ? NAN : ($n < 0 ? floor($n) : ceil($n));
    }

    protected static function momentsDifference(Moment $base, Moment $other)
    {
        if (!($base->isValid() && $other->isValid())) {
            return array(0, 0);
        }
        // compare in the base's zone, like moment's cloneWithOffset
        $other = self::inZoneOf($base, $other);
        if ($base->isBefore($other)) {
            return self::positive($base, $other);
        }
        $res = self::positive($other, $base);

        return array(-$res[0], -$res[1]);
    }

    protected static function inZoneOf(Moment $base, Moment $other)
    {
        $res = $base->copy();
        $res->setInstant($other->valueOf());

        return $res;
    }

    protected static function positive(Moment $base, Moment $other)
    {
        $months = $other->month() - $base->month() + ($other->year() - $base->year()) * 12;
        if ($base->copy()->add($months, 'M')->isAfter($other)) {
            --$months;
        }

        return array($other->valueOf() - $base->copy()->add($months, 'M')->valueOf(), $months);
    }

    public function __construct($duration = array())
    {
        $n = Units::normalizeObject((array) $duration);
        // like moment the amounts are computed even when the duration is invalid (a non-number is NaN),
        // so Moment::add() still moves by what it could read; only the getters and as*() check isValid()
        $get = function ($k) use ($n) {
            // JS reads `normalizedInput.x || 0`: undefined, null, '', 0 and NaN all become 0
            if (!isset($n[$k]) || $n[$k] === null || $n[$k] === '' || (is_float($n[$k]) && is_nan($n[$k]))) {
                return 0;
            }

            return Calendar::toNumber($n[$k]);
        };
        $weeks = !empty($n['week']) ? $get('week') : $get('isoWeek');

        $this->valid = self::isDurationValid($n);
        $this->locale = Registry::get();

        $this->milliseconds = $get('millisecond') + $get('second') * 1e3 + $get('minute') * 6e4 + $get('hour') * 1000 * 60 * 60;
        $this->days = $get('day') + $weeks * 7;
        $this->months = $get('month') + $get('quarter') * 3 + $get('year') * 12;

        $this->bubble();
    }

    protected static function isDurationValid(array $m)
    {
        foreach ($m as $key => $value) {
            if (!(in_array($key, self::$ordering) && ($value === null || Calendar::isNumeric($value)))) {
                return false;
            }
        }
        $hasDecimal = false;
        foreach (self::$ordering as $unit) {
            if (isset($m[$unit]) && $m[$unit]) {
                if ($hasDecimal) {
                    return false;
                }
                if ((float) $m[$unit] !== (float) (int) $m[$unit]) {
                    $hasDecimal = true;
                }
            }
        }

        return true;
    }

    public function isValid()
    {
        return $this->valid;
    }

    protected static function daysToMonths($days)
    {
        return ($days * 4800) / 146097;
    }

    protected static function monthsToDays($months)
    {
        return ($months * 146097) / 4800;
    }

    protected function bubble()
    {
        $ms = $this->milliseconds;
        $days = $this->days;
        $months = $this->months;

        $nan = $ms !== $ms || $days !== $days || $months !== $months;
        if ($nan || !(($ms >= 0 && $days >= 0 && $months >= 0) || ($ms <= 0 && $days <= 0 && $months <= 0))) {
            $ms += self::absCeil(self::monthsToDays($months) + $days) * 864e5;
            $days = 0;
            $months = 0;
        }

        $d = array();
        $d['milliseconds'] = fmod($ms, 1000);
        $seconds = self::absFloor($ms / 1000);
        $d['seconds'] = fmod($seconds, 60);
        $minutes = self::absFloor($seconds / 60);
        $d['minutes'] = fmod($minutes, 60);
        $hours = self::absFloor($minutes / 60);
        $d['hours'] = fmod($hours, 24);

        $days += self::absFloor($hours / 24);
        $monthsFromDays = self::absFloor(self::daysToMonths($days));
        $months += $monthsFromDays;
        $days -= self::absCeil(self::monthsToDays($monthsFromDays));

        $years = self::absFloor($months / 12);
        $months = fmod($months, 12);

        $d['days'] = $days;
        $d['months'] = $months;
        $d['years'] = $years;

        foreach ($d as $k => $v) {
            $d[$k] = $v === $v && $v == (int) $v && abs($v) < PHP_INT_MAX ? (int) $v : $v;
        }
        $this->data = $d;

        return $this;
    }

    // ------------------------------------------------------------------ getters

    public function get($units)
    {
        $units = Units::normalize($units);

        return $this->valid ? $this->{$units . 's'}() : NAN;
    }

    public function milliseconds() { return $this->valid ? $this->data['milliseconds'] : NAN; }
    public function seconds() { return $this->valid ? $this->data['seconds'] : NAN; }
    public function minutes() { return $this->valid ? $this->data['minutes'] : NAN; }
    public function hours() { return $this->valid ? $this->data['hours'] : NAN; }
    public function days() { return $this->valid ? $this->data['days'] : NAN; }
    public function months() { return $this->valid ? $this->data['months'] : NAN; }
    public function years() { return $this->valid ? $this->data['years'] : NAN; }

    public function weeks()
    {
        $days = $this->days();

        return is_float($days) && is_nan($days) ? NAN : (int) self::absFloor($days / 7);
    }

    /** @internal ms/days/months parts for Moment::add() */
    public function parts()
    {
        return array($this->milliseconds, $this->days, $this->months);
    }

    // ------------------------------------------------------------------ conversions

    public function toUnit($units)
    {
        if (!$this->valid) {
            return NAN;
        }
        $units = Units::normalize($units);
        $ms = $this->milliseconds;

        if ($units === 'month' || $units === 'quarter' || $units === 'year') {
            $days = $this->days + $ms / 864e5;
            $months = $this->months + self::daysToMonths($days);
            switch ($units) {
                case 'month': return $months;
                case 'quarter': return $months / 3;
                default: return $months / 12;
            }
        }

        $days = $this->days + Calendar::round(self::monthsToDays($this->months));
        switch ($units) {
            case 'week': return $days / 7 + $ms / 6048e5;
            case 'day': return $days + $ms / 864e5;
            case 'hour': return $days * 24 + $ms / 36e5;
            case 'minute': return $days * 1440 + $ms / 6e4;
            case 'second': return $days * 86400 + $ms / 1000;
            case 'millisecond': return floor($days * 864e5) + $ms;
        }

        throw new \InvalidArgumentException('Unknown unit ' . $units);
    }

    public function asMilliseconds() { return $this->toUnit('ms'); }
    public function asSeconds() { return $this->toUnit('s'); }
    public function asMinutes() { return $this->toUnit('m'); }
    public function asHours() { return $this->toUnit('h'); }
    public function asDays() { return $this->toUnit('d'); }
    public function asWeeks() { return $this->toUnit('w'); }
    public function asMonths() { return $this->toUnit('M'); }
    public function asQuarters() { return $this->toUnit('Q'); }
    public function asYears() { return $this->toUnit('y'); }

    public function valueOf()
    {
        return $this->asMilliseconds();
    }

    public function copy()
    {
        return self::create($this);
    }

    /**
     * Like moment: the three amounts and the bubbled fields become positive, except days (moment's
     * abs() leaves data.days alone) and nothing is bubbled again.
     */
    public function abs()
    {
        $this->milliseconds = abs($this->milliseconds);
        $this->days = abs($this->days);
        $this->months = abs($this->months);
        foreach (array('milliseconds', 'seconds', 'minutes', 'hours', 'months', 'years') as $k) {
            $this->data[$k] = abs($this->data[$k]);
        }

        return $this;
    }

    public function add($input, $value = null)
    {
        return $this->addSubtract($input, $value, 1);
    }

    public function subtract($input, $value = null)
    {
        return $this->addSubtract($input, $value, -1);
    }

    protected function addSubtract($input, $value, $direction)
    {
        $other = self::create($input, $value);
        $this->milliseconds += $direction * $other->milliseconds;
        $this->days += $direction * $other->days;
        $this->months += $direction * $other->months;

        return $this->bubble();
    }

    // ------------------------------------------------------------------ locale / output

    public function locale($key = null)
    {
        if ($key === null) {
            return $this->locale->abbr;
        }
        $locale = Registry::get($key);
        if ($locale) {
            $this->locale = $locale;
        }

        return $this;
    }

    public function localeData()
    {
        return $this->locale;
    }

    public static function relativeTimeThreshold($threshold, $limit = null)
    {
        if (!array_key_exists($threshold, self::$thresholds)) {
            return false;
        }
        if (func_num_args() < 2) {
            return self::$thresholds[$threshold];
        }
        self::$thresholds[$threshold] = $limit;
        if ($threshold === 's') {
            self::$thresholds['ss'] = $limit - 1;
        }

        return true;
    }

    /** Get the rounding callable, or set it (any callable taking a float). */
    public static function relativeTimeRounding($fn = null)
    {
        if ($fn === null) {
            return self::$rounding;
        }
        if (is_callable($fn)) {
            self::$rounding = $fn;

            return true;
        }

        return false;
    }

    protected static function round($n)
    {
        return call_user_func(self::$rounding, $n);
    }

    /**
     * humanize(), humanize(true) adds the "in"/"ago" suffix, humanize(true, array('s' => 40))
     * overrides thresholds for this call.
     */
    public function humanize($withSuffix = false, $thresholds = null)
    {
        if (!$this->valid) {
            return $this->locale->invalidDate();
        }
        if (is_array($withSuffix)) {
            $thresholds = $withSuffix;
            $withSuffix = false;
        }
        $th = self::$thresholds;
        if (is_array($thresholds)) {
            $th = array_merge($th, $thresholds);
            if (isset($thresholds['s']) && !isset($thresholds['ss'])) {
                $th['ss'] = $thresholds['s'] - 1;
            }
        }

        $output = $this->relativeTime(!$withSuffix, $th);
        if ($withSuffix) {
            $output = $this->locale->pastFuture($this->directionValue(), $output);
        }

        return $this->locale->postformat($output);
    }

    protected function relativeTime($withoutSuffix, array $th)
    {
        $d = $this->copy()->abs();
        $seconds = self::round($d->toUnit('s'));
        $minutes = self::round($d->toUnit('m'));
        $hours = self::round($d->toUnit('h'));
        $days = self::round($d->toUnit('d'));
        $months = self::round($d->toUnit('M'));
        $weeks = self::round($d->toUnit('w'));
        $years = self::round($d->toUnit('y'));

        $a = null;
        if ($seconds <= $th['ss']) {
            $a = array('s', $seconds);
        } elseif ($seconds < $th['s']) {
            $a = array('ss', $seconds);
        } elseif ($minutes <= 1) {
            $a = array('m');
        } elseif ($minutes < $th['m']) {
            $a = array('mm', $minutes);
        } elseif ($hours <= 1) {
            $a = array('h');
        } elseif ($hours < $th['h']) {
            $a = array('hh', $hours);
        } elseif ($days <= 1) {
            $a = array('d');
        } elseif ($days < $th['d']) {
            $a = array('dd', $days);
        }
        if ($a === null && $th['w'] !== null) {
            if ($weeks <= 1) {
                $a = array('w');
            } elseif ($weeks < $th['w']) {
                $a = array('ww', $weeks);
            }
        }
        if ($a === null) {
            if ($months <= 1) {
                $a = array('M');
            } elseif ($months < $th['M']) {
                $a = array('MM', $months);
            } elseif ($years <= 1) {
                $a = array('y');
            } else {
                $a = array('yy', $years);
            }
        }

        $number = isset($a[1]) && $a[1] ? $a[1] : 1;

        return $this->locale->relativeTime(Calendar::int($number), (bool) $withoutSuffix, $a[0], $this->directionValue() > 0);
    }

    /** the duration's value for "is it positive?" checks: NaN counts as 0 (it compares false in JS; PHP 5 says true) */
    protected function directionValue()
    {
        $v = $this->valueOf();

        return is_float($v) && is_nan($v) ? 0 : $v;
    }

    protected static function sign($x)
    {
        return ($x > 0) - ($x < 0) ?: $x;
    }

    public function toISOString()
    {
        if (!$this->valid) {
            return $this->locale->invalidDate();
        }
        $seconds = abs($this->milliseconds) / 1000;
        $days = abs($this->days);
        $months = abs($this->months);
        $total = $this->asSeconds();

        if (!$total || is_nan($total)) {
            return 'P0D';
        }

        $minutes = self::absFloor($seconds / 60);
        $hours = self::absFloor($minutes / 60);
        $seconds = fmod($seconds, 60);
        $minutes = fmod($minutes, 60);
        $years = self::absFloor($months / 12);
        $months = fmod($months, 12);

        $s = $seconds === $seconds && $seconds != 0 ? preg_replace('/\.?0+$/', '', self::toFixed3($seconds)) : '';

        $totalSign = $total < 0 ? '-' : '';
        $ymSign = self::sign($this->months) !== self::sign($total) ? '-' : '';
        $daysSign = self::sign($this->days) !== self::sign($total) ? '-' : '';
        $hmsSign = self::sign($this->milliseconds) !== self::sign($total) ? '-' : '';

        // JS truthiness: 0 and NaN leave a part out (PHP counts NaN as true)
        $t = function ($x) {
            return $x === $x && $x != 0;
        };

        return $totalSign . 'P'
            . ($t($years) ? $ymSign . $this->num($years) . 'Y' : '')
            . ($t($months) ? $ymSign . $this->num($months) . 'M' : '')
            . ($t($days) ? $daysSign . $this->num($days) . 'D' : '')
            . ($t($hours) || $t($minutes) || $t($seconds) ? 'T' : '')
            . ($t($hours) ? $hmsSign . $this->num($hours) . 'H' : '')
            . ($t($minutes) ? $hmsSign . $this->num($minutes) . 'M' : '')
            . ($t($seconds) ? $hmsSign . $s . 'S' : '');
    }

    protected function num($n)
    {
        return self::jsNumber($n);
    }

    /** Number.prototype.toFixed(3) for a non-negative number: exact decimal value, ties go up */
    protected static function toFixed3($x)
    {
        $long = sprintf('%.53f', $x);
        $dot = strpos($long, '.');
        $head = substr($long, 0, $dot + 4);
        $rest = substr($long, $dot + 4);
        if ($rest !== '' && $rest[0] >= '5') {
            // add one unit in the last place of $head
            $digits = str_split(str_replace('.', '', $head));
            for ($i = count($digits) - 1; $i >= 0; $i--) {
                if ($digits[$i] === '9') {
                    $digits[$i] = '0';
                } else {
                    $digits[$i]++;
                    break;
                }
            }
            if ($i < 0) {
                array_unshift($digits, '1');
            }
            $head = implode('', array_slice($digits, 0, count($digits) - 3)) . '.' . implode('', array_slice($digits, -3));
        }

        return $head;
    }

    /** A number as JavaScript prints it: the shortest digits that read back the same, no exponent in 1e-6 .. 1e21 */
    public static function jsNumber($n)
    {
        if (is_int($n)) {
            return (string) $n;
        }
        if ($n !== $n) {
            return 'NaN';
        }
        if (is_infinite($n)) {
            return $n > 0 ? 'Infinity' : '-Infinity';
        }
        if ($n == 0) {
            return '0';
        }
        $sign = $n < 0 ? '-' : '';
        $abs = abs($n);
        for ($p = 1; $p <= 17; $p++) {
            $s = sprintf('%.' . ($p - 1) . 'e', $abs);
            if ((float) $s === (float) $abs) {
                break;
            }
        }
        preg_match('/^(\d)(?:\.(\d+))?e([+-]\d+)$/', $s, $m);
        $digits = rtrim($m[1] . (isset($m[2]) ? $m[2] : ''), '0');
        $digits = $digits === '' ? '0' : $digits;
        $k = strlen($digits);
        $e = (int) $m[3] + 1; // position of the decimal point relative to the first digit

        if ($k <= $e && $e <= 21) {
            return $sign . $digits . str_repeat('0', $e - $k);
        }
        if (0 < $e && $e <= 21) {
            return $sign . substr($digits, 0, $e) . '.' . substr($digits, $e);
        }
        if (-6 < $e && $e <= 0) {
            return $sign . '0.' . str_repeat('0', -$e) . $digits;
        }
        $exp = $e - 1;

        return $sign . $digits[0] . ($k > 1 ? '.' . substr($digits, 1) : '') . 'e' . ($exp < 0 ? '-' : '+') . abs($exp);
    }

    public function toJSON()
    {
        return $this->toISOString();
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return $this->toISOString();
    }

    public function toString()
    {
        return (string) $this->toISOString();
    }

    public function __toString()
    {
        return (string) $this->toISOString();
    }

    /**
     * moment.js spells these as duration.as(), duration.clone() and
     * duration.lang(); "as" and "clone" are reserved words in PHP 5, so they are served here
     * (toUnit() and copy() are the plain methods).
     */
    public function __call($name, $args)
    {
        switch ($name) {
            case 'as':
                return $this->toUnit($args[0]);
            case 'clone':
                return $this->copy();
            case 'lang':
                Moment::deprecate('lang', 'duration.lang is deprecated. Use locale instead.');

                return $args ? $this->locale($args[0]) : $this->localeData();
        }

        throw new \BadMethodCallException('Call to undefined method ' . __CLASS__ . '::' . $name . '()');
    }
}
