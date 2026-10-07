<?php

namespace Wilkques\Moment;

if (!interface_exists('JsonSerializable', false)) {
    require_once __DIR__ . '/JsonSerializable.php';
}

use DateTime;
use DateTimeZone;
use Wilkques\Moment\Locale\Locale;
use Wilkques\Moment\Locale\Registry;
use Wilkques\Moment\Timezone\Tz;
use Wilkques\Moment\Timezone\Zone;

/**
 * A mutable date-time with moment.js semantics. Mutators change and return $this;
 * use copy() (moment's clone()) to keep the original. Internally a moment is a UTC
 * millisecond timestamp plus a zone: fixed offset ("utc"), a named zone, or the
 * default PHP time zone ("local"). On 32-bit PHP the timestamp is a whole-number float.
 */
class Moment implements \JsonSerializable
{

    const MODE_LOCAL = 'local';
    const MODE_OFFSET = 'offset';
    const MODE_ZONE = 'zone';

    protected $ms;
    protected $mode = self::MODE_LOCAL;
    protected $offsetSeconds = 0;
    /** @var Zone|null */
    protected $zone;
    protected $zoneName;
    protected $locale;
    protected $strict = false;

    /** @var mixed what the moment was created from (moment's _i) and the format it was parsed with (_f) */
    protected $input;
    protected $format;
    /** @var array parsing flags (see parsingFlags()) */
    protected $pf;
    /** @var array|null the date parts the input was read into (moment's _a) */
    protected $parsedArray;
    /** @var int|null the UTC offset in minutes the input string carried (moment's _tzm) */
    protected $tzm;

    protected static $nowOverride;

    const VERSION = '2.30.1';
    const ISO_8601 = 'ISO_8601';
    const RFC_2822 = 'RFC_2822';

    /** moment.HTML5_FMT */
    public static $HTML5_FMT = array(
        'DATETIME_LOCAL' => 'YYYY-MM-DDTHH:mm',
        'DATETIME_LOCAL_SECONDS' => 'YYYY-MM-DDTHH:mm:ss',
        'DATETIME_LOCAL_MS' => 'YYYY-MM-DDTHH:mm:ss.SSS',
        'DATE' => 'YYYY-MM-DD',
        'TIME' => 'HH:mm',
        'TIME_SECONDS' => 'HH:mm:ss',
        'TIME_MS' => 'HH:mm:ss.SSS',
        'WEEK' => 'GGGG-[W]WW',
        'MONTH' => 'YYYY-MM',
    );

    /** moment.defaultFormat / defaultFormatUtc: what format() uses without an argument */
    public static $defaultFormat = 'YYYY-MM-DDTHH:mm:ssZ';
    public static $defaultFormatUtc = 'YYYY-MM-DDTHH:mm:ss[Z]';

    /** @var callable|null moment.calendarFormat: function(Moment $this, Moment $startOfToday) returns a calendar key */
    public static $calendarFormat;

    /** @var callable|null moment.parseTwoDigitYear: function(string $input) returns the full year */
    public static $parseTwoDigitYear;

    /** @var callable|null moment.createFromInputFallback: function(string $input, bool $useUtc) returns a parse result or null */
    public static $createFromInputFallback;

    /** @var callable|null moment.deprecationHandler: function(string $name, string $message) */
    public static $deprecationHandler;

    /** moment.suppressDeprecationWarnings */
    public static $suppressDeprecationWarnings = false;

    /** Kept for parity with moment.momentProperties; PHP's clone copies every property. */
    public static $momentProperties = array();

    protected static $deprecated = array();

    /**
     * @var callable|null moment.updateOffset: function(Moment $m, bool $keepTime), called after a moment's
     * fields or instant were changed (moment-timezone's own handling is built in; this is for extra hooks)
     */
    public static $updateOffset;

    /** @var bool true while the construction-time updateOffset call runs */
    protected static $updating = false;

    /** @var bool true while one public operation makes several internal changes and reports them once */
    protected $quiet = false;

    /** @var array name => callable added with macro() (moment.fn.name = ...) */
    protected static $macros = array();

    // ------------------------------------------------------------------ creation

    /**
     * @param mixed $input null (now), int/float ms, Moment, DateTime, array(y, m0, d, h, i, s, ms), string
     */
    public static function create($input = null, $format = null, $locale = null, $strict = false)
    {
        $nullInput = func_num_args() > 0 && $input === null;
        if (Tz::$defaultZone !== null && !($input instanceof self)) {
            return self::build($input, $format, $locale, $strict, self::MODE_ZONE, Tz::$defaultZone->name, $nullInput);
        }

        return self::build($input, $format, $locale, $strict, self::MODE_LOCAL, null, $nullInput);
    }

    /** moment.utc(...) */
    public static function createUtc($input = null, $format = null, $locale = null, $strict = false)
    {
        return self::build($input, $format, $locale, $strict, self::MODE_OFFSET, null, func_num_args() > 0 && $input === null);
    }

    /** moment.tz(input, zone): wall-clock inputs (arrays, strings without offset) are read in $zone. */
    public static function createInZone($input, $zone, $format = null, $locale = null, $strict = false)
    {
        return self::build($input, $format, $locale, $strict, self::MODE_ZONE, $zone, $input === null);
    }

    /** moment.unix(seconds) */
    public static function fromUnix($seconds)
    {
        return self::create($seconds * 1000);
    }

    /** moment.invalid($flags): an invalid moment; $flags (parsing flags) default to userInvalidated */
    public static function invalid($flags = null)
    {
        $m = new self();
        $m->mode = self::MODE_OFFSET;
        $m->input = NAN; // moment makes it createUTC(NaN)
        $m->locale = Registry::get();
        $m->pf = self::defaultFlags();
        if ($flags !== null) {
            $m->pf = array_merge($m->pf, $flags);
        } else {
            $m->pf['userInvalidated'] = true;
        }
        $m->touch(null, true);

        return $m;
    }

    /** moment's default parsing flags */
    public static function defaultFlags()
    {
        return array(
            'empty' => false, 'unusedTokens' => array(), 'unusedInput' => array(), 'overflow' => -2, 'charsLeftOver' => 0,
            'nullInput' => false, 'invalidEra' => null, 'invalidMonth' => null, 'invalidFormat' => false,
            'userInvalidated' => false, 'iso' => false, 'parsedDateParts' => array(), 'era' => null, 'meridiem' => null,
            'rfc2822' => false, 'weekdayMismatch' => false,
        );
    }

    public static function isMoment($obj)
    {
        return $obj instanceof self;
    }

    public static function now()
    {
        return self::$nowOverride !== null ? self::$nowOverride : Calendar::int(floor(microtime(true) * 1000));
    }

    /** Freeze (int ms) or release (null) "now", for tests. */
    public static function setNow($ms)
    {
        self::$nowOverride = $ms;
    }

    protected static function build($input, $format, $locale, $strict, $mode, $zone, $nullInput = false)
    {
        if ($format === true || $format === false) {
            $strict = $format;
            $format = null;
        }
        if ($locale === true || $locale === false) {
            $strict = $locale;
            $locale = null;
        }

        $m = new self();
        $m->mode = $mode;
        $m->strict = $strict;
        $m->input = $input;
        $m->format = $format;
        $m->pf = self::defaultFlags();
        $m->locale = $locale !== null ? (Registry::get($locale) ?: Registry::get()) : Registry::get();

        $zoneKnown = true;
        if ($mode === self::MODE_ZONE && !$m->setZoneByName($zone)) {
            $zoneKnown = false;
            // moment-timezone only logs an unknown zone and carries on in UTC
            $m->mode = self::MODE_OFFSET;
            $m->offsetSeconds = 0;
        }

        if ($nullInput || ($input === '' && $format === null)) {
            $m->mode = self::MODE_OFFSET; // moment makes it createUTC(NaN)
            $m->pf['nullInput'] = true;
            $m->touch(null, true);
            $m->touch(null, true);
            if ($mode === self::MODE_ZONE && $zoneKnown) {
                $m->touch(null); // tz() on the invalid moment
            }

            return $m;
        }
        if (is_array($input) && !$input) {
            $input = null; // an empty array or object means "now", like undefined
        }

        if ($input instanceof self) {
            $keepLocale = $m->locale;
            foreach (array('ms', 'mode', 'offsetSeconds', 'zone', 'zoneName', 'locale', 'pf', 'parsedArray', 'tzm') as $prop) {
                $m->$prop = $input->$prop;
            }
            if ($locale !== null) {
                $m->locale = $keepLocale;
            }
            if ($mode === self::MODE_ZONE && $m->setZoneByName($zone)) {
                $m->mode = self::MODE_ZONE;
            }
            $m->touch(null, true); // moment builds a copy of the moment first, then the result
        } elseif ($input instanceof InvalidDate) {
            $m->ms = null; // moment(new Date(NaN))
        } elseif ($input instanceof DateTime) {
            $m->ms = $input->getTimestamp() * 1000 + (int) ((int) $input->format('u') / 1000);
        } elseif ($input === null && $format === null) {
            $m->ms = self::now();
        } elseif (is_int($input) || is_float($input)) {
            $m->ms = is_nan($input) || is_infinite($input) || abs($input) > 8.64e15 ? null : Calendar::int(round($input));
        } elseif (is_array($input)) {
            $m->fromArray(self::isList($input) ? $input : self::objectToParts($input));
        } elseif (is_string($input) || $input === null) {
            $result = Parser::parse($m, (string) $input, $format);
            $m->pf = Parser::$flags !== null ? Parser::$flags : $m->pf;
            if ($result !== null && $m->pf['overflow'] >= 0) {
                $result = null; // a date part overflowed (31 Feb): invalid, though the string itself was recognised
            }
            $m->parsedArray = Parser::$parsedArray;
            if (Parser::$format !== null) {
                $m->format = Parser::$format;
            }
            if ($result !== null) {
                $m->tzm = isset($result['tzm']) ? $result['tzm'] : null;
                $m->ms = isset($result['ms']) ? $result['ms']
                    : (!empty($result['host']) ? self::hostResolve($result['wall'])
                    : ($result['tzm'] !== null
                        ? $result['wall'] - $result['tzm'] * 60000
                        : $m->resolve($result['wall'], null)));
            }
        }
        $m->touch(null, true);
        if ($mode === self::MODE_ZONE && $m->mode === self::MODE_ZONE) {
            // moment.tz() builds strings and objects through parseWithZoneNow (two more moments when parts were parsed),
            // adds the zone's offset to wall-clock parts, then tz() announces the zone it just set
            if ((is_string($input) || (is_array($input) && !self::isList($input))) && $m->parsedArray !== null) {
                $m->touch(null, true);
                $m->touch(null, true);
            }
            if ($m->needsOffset()) {
                $m->touch(false);
            }
            $m->touch(null);
        }

        return $m;
    }

    /** a wall-clock time (as UTC-style ms) read in the host's local zone, like a bare `new Date(...)` */
    protected static function hostResolve($wall)
    {
        $local = new self();
        $local->mode = self::MODE_LOCAL;

        return $local->resolve($wall, null);
    }

    protected static function isList(array $a)
    {
        return array_keys($a) === range(0, count($a) - 1);
    }

    /** moment({year: 2024, month: 0, day: 5, ...}) as the parts list of an array input */
    protected static function objectToParts(array $o)
    {
        $n = Units::normalizeObject($o);
        $dayOrDate = isset($n['day']) ? $n['day'] : (isset($n['date']) ? $n['date'] : null);
        $parts = array();
        foreach (array(isset($n['year']) ? $n['year'] : null, isset($n['month']) ? $n['month'] : null, $dayOrDate,
            isset($n['hour']) ? $n['hour'] : null, isset($n['minute']) ? $n['minute'] : null,
            isset($n['second']) ? $n['second'] : null, isset($n['millisecond']) ? $n['millisecond'] : null) as $v) {
            $parts[] = $v === null || $v === '' || $v === 0 || $v === '0' ? ($v === null ? null : 0) : (int) $v;
        }

        return $parts;
    }

    protected function fromArray(array $a)
    {
        $now = null;
        $parts = array();
        for ($i = 0; $i < 7; $i++) {
            $parts[$i] = null;
            if (isset($a[$i])) {
                // moment maps the array through parseInt(x, 10): '2024/01/05' reads as 2024, 'Jan' and '' as NaN
                if (is_string($a[$i])) {
                    if (!preg_match('/^[\s\x{FEFF}\x{A0}]*([+-]?\d+)/u', $a[$i], $m)) {
                        return; // new Date(NaN, ...) is an invalid date
                    }
                    $n = Calendar::toNumber($m[1]);
                } else {
                    $n = Calendar::toNumber($a[$i]);
                }
                if (is_float($n) && (is_nan($n) || is_infinite($n))) {
                    return;
                }
                $parts[$i] = (int) $n;
            }
        }
        for ($i = 0; $i < 3 && $parts[$i] === null; $i++) {
            if ($now === null) {
                $now = Calendar::fields(self::now() + $this->offsetAt(self::now()) * 1000);
            }
            $parts[$i] = $now[$i];
        }
        for (; $i < 7; $i++) {
            $parts[$i] = $parts[$i] === null ? ($i === 2 ? 1 : 0) : $parts[$i];
        }

        list($y, $mo, $d, $h, $mi, $s, $ms) = $parts;
        $this->parsedArray = $parts;
        $this->pf['overflow'] = Parser::overflowOf($parts);
        $bad = $mo < 0 || $mo > 11 || $d < 1 || $d > Calendar::daysInMonth($y, $mo)
            || $h < 0 || $h > 24 || ($h === 24 && ($mi || $s || $ms))
            || $mi < 0 || $mi > 59 || $s < 0 || $s > 59 || $ms < 0 || $ms > 999;
        if ($bad) {
            return;
        }

        $this->ms = $this->resolve(Calendar::utcMs($y, $mo, $d, $h, $mi, $s, $ms), null);
        if (abs($this->ms) > 8.64e15) {
            $this->ms = null; // beyond the range of a JS Date
        }
    }

    /** @internal set the instant without touching the zone */
    public function setInstant($ms)
    {
        $this->ms = $ms;

        return $this;
    }

    /** @internal whether strings are read as UTC (utc() and tz() moments) rather than local time */
    public function isUseUtc()
    {
        return $this->mode !== self::MODE_LOCAL;
    }

    /** @internal */
    public function isStrict()
    {
        return $this->strict;
    }

    /** @internal wall-clock "now" in this moment's zone, as UTC-style ms */
    public function currentWall()
    {
        return self::now() + $this->offsetAt(self::now()) * 1000;
    }

    public function copy()
    {
        $copy = clone $this;
        $copy->touch(null, true);

        return $copy;
    }

    // ------------------------------------------------------------------ zone plumbing

    /** @return bool false when the zone name is unknown */
    protected function setZoneByName($name)
    {
        $zone = Tz::zone($name);
        if ($zone === null) {
            return false;
        }
        $this->zone = $zone;
        $this->zoneName = $zone->name;

        return true;
    }

    /** @return Zone|null the zone behind this moment: its own, or the host's for "local" moments */
    protected function zoneObject()
    {
        if ($this->mode === self::MODE_ZONE) {
            return $this->zone;
        }
        if ($this->mode === self::MODE_LOCAL) {
            return Tz::zone(date_default_timezone_get());
        }

        return null;
    }

    /** UTC offset in seconds in effect at the instant $ms. */
    protected function offsetAt($ms)
    {
        if ($this->mode === self::MODE_OFFSET) {
            return $this->offsetSeconds;
        }
        $zone = $this->zoneObject();
        if ($zone === null) {
            // the host zone isn't in moment-timezone's data: ask PHP
            $d = new DateTime('@' . Calendar::str(floor($ms / 1000)));
            $tz = new DateTimeZone(date_default_timezone_get());

            return $tz->getOffset($d);
        }

        return (int) round(-$zone->utcOffset($ms) * 60);
    }

    /**
     * Wall-clock ms (the wall time written as if it were UTC) to an instant, following
     * moment-timezone: keep the current offset when still valid; otherwise Zone::parse()
     * (a time inside a spring-forward gap moves forward, an ambiguous time takes the earlier
     * instant, unless Tz::$moveInvalidForward / $moveAmbiguousForward say otherwise).
     */
    protected function resolve($wall, $currentOffset)
    {
        if ($this->mode === self::MODE_OFFSET) {
            return $wall - $this->offsetSeconds * 1000;
        }
        $zone = $this->zoneObject();
        if ($zone === null) {
            return $this->resolveByOffsets($wall, $currentOffset);
        }

        if ($currentOffset !== null) {
            $o = $zone->utcOffset($wall - $currentOffset * 1000);
            $ts = $wall + $o * 60000;
            if ($zone->utcOffset($ts) == $o) {
                return Calendar::int(round($ts));
            }
        }

        // a local moment follows JS Date (earlier of two, forward over a gap), not Tz's settings
        $local = $this->mode === self::MODE_LOCAL;

        return Calendar::int(round($wall + $zone->parse($wall, $local ? false : null, $local ? true : null) * 60000));
    }

    /** Fallback for a host zone moment-timezone doesn't know, using only offsetAt(). */
    protected function resolveByOffsets($wall, $currentOffset)
    {
        if ($currentOffset !== null) {
            $o = $this->offsetAt($wall - $currentOffset * 1000);
            $ts = $wall - $o * 1000;
            if ($this->offsetAt($ts) === $o) {
                return $ts;
            }
        }

        $before = $this->offsetAt($wall - 86400000);
        $after = $this->offsetAt($wall + 86400000);
        $tsBefore = $wall - $before * 1000;
        $tsAfter = $wall - $after * 1000;
        $okBefore = $this->offsetAt($tsBefore) === $before;
        $okAfter = $this->offsetAt($tsAfter) === $after;

        if ($okBefore && $okAfter) {
            return min($tsBefore, $tsAfter);
        }

        return $okAfter ? $tsAfter : $tsBefore;
    }

    protected function wall()
    {
        return $this->ms + $this->offsetAt($this->ms) * 1000;
    }

    protected function setWall($wall)
    {
        $old = $this->offsetAt($this->ms);
        $this->ms = $this->resolve($wall, $old);
        $this->announce(true, $old);

        return $this;
    }

    /** @return array(year, month0, date, hour, minute, second, ms, weekday) */
    protected function parts()
    {
        return Calendar::fields($this->wall());
    }

    protected function part($i)
    {
        if (!$this->isValid()) {
            return NAN;
        }
        $p = $this->parts();

        return $p[$i];
    }

    /** Replace some wall-clock parts (index => value); overflow rolls over like JS Date. */
    protected function setParts(array $replace, $keepTime = true)
    {
        if (!$this->isValid()) {
            return $this;
        }
        $p = $this->parts();
        foreach ($replace as $i => $v) {
            $n = Calendar::toNumber($v);
            if (is_float($n) && (is_nan($n) || is_infinite($n))) {
                return $this; // moment ignores a value that isn't a number
            }
            $p[$i] = (int) $n;
        }
        $wall = Calendar::utcMs($p[0], $p[1], $p[2], $p[3], $p[4], $p[5], $p[6]);
        if ($keepTime) {
            return $this->setWall($wall);
        }

        // minutes, seconds and milliseconds move the instant by the difference, keeping today's offset
        $this->ms = Calendar::int($wall - $this->offsetAt($this->ms) * 1000);
        $this->touch(false);

        return $this;
    }

    // ------------------------------------------------------------------ validity / values

    public function isValid()
    {
        return $this->ms !== null;
    }

    /** Milliseconds since the Unix epoch (NAN when invalid). */
    public function valueOf()
    {
        return $this->ms === null ? NAN : $this->ms;
    }

    public function unixSeconds()
    {
        return $this->ms === null ? NAN : Calendar::int(floor($this->ms / 1000));
    }

    /** A DateTime (UTC) for this instant, milliseconds included; null when invalid (JS has an "Invalid Date" object). */
    public function toDate()
    {
        if (!$this->isValid()) {
            return new InvalidDate();
        }
        $sec = floor($this->ms / 1000);
        $rem = $this->ms - $sec * 1000;
        $d = DateTime::createFromFormat('U.u', Calendar::str($sec) . '.' . sprintf('%06d', $rem * 1000));

        if (!$d) {
            // PHP 5.3 can't parse a negative "U": read the UTC fields instead (years 1-9999 only)
            $f = Calendar::fields($this->ms);
            if ($f[0] >= 1 && $f[0] <= 9999) {
                $d = DateTime::createFromFormat('Y-n-j G:i:s.u', sprintf('%04d-%d-%d %d:%02d:%02d.%06d', $f[0], $f[1] + 1, $f[2], $f[3], $f[4], $f[5], $f[6] * 1000), new \DateTimeZone('UTC'));
            }
        }

        return $d ?: new DateTime('@' . Calendar::str($sec));
    }

    /** array(year, month0, date, hour, minute, second, ms); NaN for each when invalid */
    public function toArray()
    {
        return array($this->part(0), $this->part(1), $this->part(2), $this->part(3), $this->part(4), $this->part(5), $this->part(6));
    }

    public function toObject()
    {
        return array('years' => $this->part(0), 'months' => $this->part(1), 'date' => $this->part(2), 'hours' => $this->part(3),
            'minutes' => $this->part(4), 'seconds' => $this->part(5), 'milliseconds' => $this->part(6));
    }

    // ------------------------------------------------------------------ getters / setters


    public function year($value = null)
    {
        if (func_num_args() === 0 || $value === null) {
            return $this->part(0);
        }
        if (!$this->isValid() || !is_numeric($value)) {
            return $this;
        }
        $p = $this->parts();
        $date = ($p[2] === 29 && $p[1] === 1 && !Calendar::isLeapYear((int) $value)) ? 28 : $p[2];

        return $this->setParts(array(0 => $value, 2 => $date));
    }

    public function month($value = null)
    {
        if (func_num_args() === 0 || $value === null) {
            return $this->part(1);
        }
        if (!$this->isValid()) {
            return $this;
        }
        if (is_string($value)) {
            if (ctype_digit($value)) {
                $value = (int) $value;
            } else {
                $value = $this->locale->monthsParse($value);
                if ($value === null) {
                    return $this;
                }
            }
        }

        return $this->setMonthClamped((int) $value);
    }

    protected function setMonthClamped($month)
    {
        $p = $this->parts();
        $date = $p[2] < 29 ? $p[2] : min($p[2], Calendar::daysInMonth($p[0], $month));

        return $this->setParts(array(1 => $month, 2 => $date));
    }

    public function date($value = null)
    {
        return func_num_args() === 0 || $value === null ? $this->part(2) : $this->setParts(array(2 => $value));
    }

    public function hour($value = null)
    {
        return func_num_args() === 0 || $value === null ? $this->part(3) : $this->setParts(array(3 => $value));
    }

    public function minute($value = null)
    {
        return func_num_args() === 0 || $value === null ? $this->part(4) : $this->setParts(array(4 => $value), false);
    }

    public function second($value = null)
    {
        return func_num_args() === 0 || $value === null ? $this->part(5) : $this->setParts(array(5 => $value), false);
    }

    public function millisecond($value = null)
    {
        return func_num_args() === 0 || $value === null ? $this->part(6) : $this->setParts(array(6 => $value), false);
    }

    /** Day of week, 0 = Sunday. Setting accepts a number or a weekday name. */
    public function day($value = null)
    {
        if (!$this->isValid()) {
            return func_num_args() && $value !== null ? $this : NAN;
        }
        $day = $this->part(7);
        if (func_num_args() === 0 || $value === null) {
            return $day;
        }
        if (is_string($value)) {
            // an unrecognised name parses to null, which moment then subtracts the day from (landing on Sunday)
            $value = is_numeric($value) ? (int) $value : $this->locale->weekdaysParse($value);
        }

        return $this->add(Calendar::toNumber($value) - $day, 'd');
    }

    /** Locale-aware day of week (0 = first day of the week). */
    public function weekday($value = null)
    {
        if (!$this->isValid()) {
            return func_num_args() && $value !== null ? $this : NAN;
        }
        $weekday = ($this->part(7) + 7 - $this->locale->firstDayOfWeek()) % 7;

        return func_num_args() === 0 || $value === null ? $weekday : $this->add(Calendar::toNumber($value) - $weekday, 'd');
    }

    /** ISO day of week, 1 = Monday .. 7 = Sunday. */
    public function isoWeekday($value = null)
    {
        if (!$this->isValid()) {
            return func_num_args() && $value !== null ? $this : NAN;
        }
        if (func_num_args() === 0 || $value === null) {
            return $this->part(7) ?: 7;
        }
        if (is_string($value)) {
            $value = $this->locale->weekdaysParse($value) % 7 ?: 7;
        }

        return $this->day($this->part(7) % 7 ? $value : Calendar::toNumber($value) - 7);
    }

    public function dayOfYear($value = null)
    {
        if (!$this->isValid()) {
            return func_num_args() && $value !== null ? $this : NAN;
        }
        $doy = $this->dayOfYearNumber();

        return func_num_args() === 0 || $value === null ? $doy : $this->add(Calendar::toNumber($value) - $doy, 'd');
    }

    /**
     * moment's day of year: the rounded number of 24h days between the start of this day and of this year,
     * so a day that isn't 24 hours long (a date line change) still counts as one.
     */
    protected function dayOfYearNumber()
    {
        $days = ($this->copy()->startOf('day')->ms - $this->copy()->startOf('year')->ms) / 864e5;

        return (int) floor($days + 0.5) + 1;
    }

    protected function weekInfo($dow, $doy)
    {
        $p = $this->parts();
        $dayOfYear = $this->dayOfYearNumber();

        return Calendar::weekOfYear($p[0], $dayOfYear, $dow, $doy);
    }

    public function week($value = null)
    {
        if (!$this->isValid()) {
            return func_num_args() && $value !== null ? $this : NAN;
        }
        $info = $this->weekInfo($this->locale->firstDayOfWeek(), $this->locale->firstDayOfYear());

        return func_num_args() === 0 || $value === null ? $info[0] : $this->add((Calendar::toNumber($value) - $info[0]) * 7, 'd');
    }

    public function isoWeek($value = null)
    {
        if (!$this->isValid()) {
            return func_num_args() && $value !== null ? $this : NAN;
        }
        $info = $this->weekInfo(1, 4);

        return func_num_args() === 0 || $value === null ? $info[0] : $this->add((Calendar::toNumber($value) - $info[0]) * 7, 'd');
    }

    public function weekYear($value = null)
    {
        if (!$this->isValid()) {
            return func_num_args() && $value !== null ? $this : NAN;
        }
        $dow = $this->locale->firstDayOfWeek();
        $doy = $this->locale->firstDayOfYear();

        return $this->weekYearHelper($value, $this->week(), $this->weekday() + $dow, $dow, $doy, func_num_args());
    }

    public function isoWeekYear($value = null)
    {
        if (!$this->isValid()) {
            return func_num_args() && $value !== null ? $this : NAN;
        }

        return $this->weekYearHelper($value, $this->isoWeek(), $this->isoWeekday(), 1, 4, func_num_args());
    }

    protected function weekYearHelper($input, $week, $weekday, $dow, $doy, $argc)
    {
        if ($argc === 0 || $input === null) {
            $info = $this->weekInfo($dow, $doy);

            return $info[1];
        }
        $target = Calendar::weeksInYear($input, $dow, $doy);
        if ($week > $target) {
            $week = $target;
        }
        $res = Calendar::dayOfYearFromWeeks($input, $week, $weekday, $dow, $doy);
        $days = Calendar::createUtcDays($res[0], 0, $res[1]);
        $c = Calendar::civilFromDays($days);

        return $this->setParts(array(0 => $c[0], 1 => $c[1], 2 => $c[2]));
    }

    public function quarter($value = null)
    {
        if (!$this->isValid()) {
            return func_num_args() && $value !== null ? $this : NAN;
        }
        if (func_num_args() === 0 || $value === null) {
            return (int) ceil(($this->part(1) + 1) / 3);
        }

        return $this->month((Calendar::toNumber($value) - 1) * 3 + ($this->part(1) % 3));
    }

    public function daysInMonth()
    {
        if (!$this->isValid()) {
            return NAN;
        }
        $p = $this->parts();

        return Calendar::daysInMonth($p[0], $p[1]);
    }

    public function isLeapYear()
    {
        return $this->isValid() && Calendar::isLeapYear($this->part(0));
    }

    public function weeksInYear()
    {
        if (!$this->isValid()) {
            return NAN;
        }

        return Calendar::weeksInYear($this->part(0), $this->locale->firstDayOfWeek(), $this->locale->firstDayOfYear());
    }

    public function isoWeeksInYear()
    {
        if (!$this->isValid()) {
            return NAN;
        }

        return Calendar::weeksInYear($this->part(0), 1, 4);
    }

    public function get($unit)
    {
        $unit = Units::normalize($unit);

        return $unit !== null && method_exists($this, $unit) ? $this->$unit() : $this;
    }

    /** set('month', 3) or set(array('year' => 2020, 'month' => 3)) */
    public function set($unit, $value = null)
    {
        if (is_array($unit)) {
            $units = Units::normalizeObject($unit);
            foreach (Units::prioritized($units) as $u) {
                $this->$u($units[$u]);
            }

            return $this;
        }
        $unit = Units::normalize($unit);
        if ($unit !== null && method_exists($this, $unit)) {
            return $this->$unit($value);
        }

        return $this;
    }

    // ------------------------------------------------------------------ add / subtract

    protected static function absRound($n)
    {
        return $n < 0 ? -1 * (int) round(-1 * $n) : (int) round($n);
    }

    /** add(1, 'month'), add(array('days' => 2, 'hours' => 3)) or add(Duration) */
    public function add($amount, $unit = null)
    {
        return $this->addSubtract($amount, $unit, 1);
    }

    public function subtract($amount, $unit = null)
    {
        return $this->addSubtract($amount, $unit, -1);
    }

    protected function addSubtract($amount, $unit, $direction)
    {
        if ($unit !== null && !is_array($unit) && Calendar::isNumeric($unit)) {
            $name = $direction > 0 ? 'add' : 'subtract';
            self::deprecate($name, 'moment().' . $name . '(period, number) is deprecated. Please use moment().' . $name
                . '(number, period). See http://momentjs.com/guides/#/warnings/add-inverted-param/ for more info.');
            $tmp = $amount;
            $amount = $unit;
            $unit = $tmp;
        }

        $duration = $amount instanceof Duration ? $amount : Duration::create($amount, $unit);
        $parts = $duration->parts();

        if (!$this->isValid()) {
            return $this;
        }

        $ms = $parts[0];
        $days = is_nan($parts[1]) ? 0 : self::absRound($parts[1]);
        $months = is_nan($parts[2]) ? 0 : self::absRound($parts[2]);
        $oldOffset = $this->offsetAt($this->ms);
        $this->quiet = true;

        if ($months) {
            $this->setMonthClamped($this->part(1) + $months * $direction);
        }
        if ($days) {
            $this->setParts(array(2 => $this->part(2) + $days * $direction));
        }
        if ($ms && !is_nan($ms)) {
            // Date.setTime() truncates a fractional millisecond value toward zero
            $this->ms = Calendar::trunc($this->ms + $ms * $direction);
        }
        $this->quiet = false;
        $this->announce((bool) ($days || $months), $oldOffset);

        return $this;
    }

    // ------------------------------------------------------------------ startOf / endOf

    public function startOf($units)
    {
        return $this->boundary($units, false);
    }

    public function endOf($units)
    {
        return $this->boundary($units, true);
    }

    protected function boundary($units, $end)
    {
        $units = Units::normalize($units);
        if ($units === null || $units === 'millisecond' || !$this->isValid()) {
            return $this;
        }

        $p = $this->parts();
        $wall = $this->wall();
        $e = $end ? 1 : 0; // end of X = start of the next X minus 1ms

        switch ($units) {
            case 'year': $time = Calendar::utcMs($p[0] + $e, 0, 1); break;
            case 'quarter': $time = Calendar::utcMs($p[0], $p[1] - ($p[1] % 3) + 3 * $e, 1); break;
            case 'month': $time = Calendar::utcMs($p[0], $p[1] + $e, 1); break;
            case 'week': $time = Calendar::utcMs($p[0], $p[1], $p[2] - $this->weekday() + 7 * $e); break;
            case 'isoWeek': $time = Calendar::utcMs($p[0], $p[1], $p[2] - ($this->isoWeekday() - 1) + 7 * $e); break;
            case 'day':
            case 'date': $time = Calendar::utcMs($p[0], $p[1], $p[2] + $e); break;
            case 'hour': $time = $wall - Calendar::mod($wall, 3600000) + 3600000 * $e; break;
            case 'minute': $time = $wall - Calendar::mod($wall, 60000) + 60000 * $e; break;
            case 'second': $time = $wall - Calendar::mod($wall, 1000) + 1000 * $e; break;
            default: return $this;
        }

        if (in_array($units, array('hour', 'minute', 'second'))) {
            // sub-day units truncate on the instant when the wall clock is ambiguous (DST overlap)
            $time = $end ? $time - 1 : $time;

            return $this->setWall($time);
        }

        // the end is the wall clock 1ms before the next start, resolved as a wall time (it matters when
        // that moment is ambiguous because the clocks went back)
        return $this->setWall($end ? $time - 1 : $time);
    }

    // ------------------------------------------------------------------ comparison

    protected static function coerce($input)
    {
        return $input instanceof self ? $input : self::create($input);
    }

    public function isAfter($input, $units = null)
    {
        $other = self::coerce($input);
        if (!($this->isValid() && $other->isValid())) {
            return false;
        }
        $units = Units::normalize($units) ?: 'millisecond';
        if ($units === 'millisecond') {
            return $this->ms > $other->ms;
        }

        return $other->ms < $this->copy()->startOf($units)->ms;
    }

    public function isBefore($input, $units = null)
    {
        $other = self::coerce($input);
        if (!($this->isValid() && $other->isValid())) {
            return false;
        }
        $units = Units::normalize($units) ?: 'millisecond';
        if ($units === 'millisecond') {
            return $this->ms < $other->ms;
        }

        return $this->copy()->endOf($units)->ms < $other->ms;
    }

    public function isSame($input, $units = null)
    {
        $other = self::coerce($input);
        if (!($this->isValid() && $other->isValid())) {
            return false;
        }
        $units = Units::normalize($units) ?: 'millisecond';
        if ($units === 'millisecond') {
            return $this->ms == $other->ms;
        }

        return $this->copy()->startOf($units)->ms <= $other->ms && $other->ms <= $this->copy()->endOf($units)->ms;
    }

    public function isSameOrAfter($input, $units = null)
    {
        return $this->isSame($input, $units) || $this->isAfter($input, $units);
    }

    public function isSameOrBefore($input, $units = null)
    {
        return $this->isSame($input, $units) || $this->isBefore($input, $units);
    }

    /** @param string $inclusivity '()' (default), '[]', '[)' or '(]' */
    public function isBetween($from, $to, $units = null, $inclusivity = '()')
    {
        $a = self::coerce($from);
        $b = self::coerce($to);
        if (!($this->isValid() && $a->isValid() && $b->isValid())) {
            return false;
        }
        $inclusivity = $inclusivity ?: '()';

        return ($inclusivity[0] === '(' ? $this->isAfter($a, $units) : !$this->isBefore($a, $units))
            && ($inclusivity[1] === ')' ? $this->isBefore($b, $units) : !$this->isAfter($b, $units));
    }

    public static function maxOf()
    {
        return self::pick('isAfter', func_get_args());
    }

    public static function minOf()
    {
        return self::pick('isBefore', func_get_args());
    }

    protected static function pick($fn, $moments)
    {
        if (count($moments) === 1 && is_array($moments[0])) {
            $moments = $moments[0];
        }
        if (!$moments) {
            return self::create();
        }
        $res = $moments[0];
        for ($i = 1; $i < count($moments); $i++) {
            if (!$moments[$i]->isValid() || $moments[$i]->$fn($res)) {
                $res = $moments[$i];
            }
        }

        return $res;
    }

    // ------------------------------------------------------------------ diff

    /**
     * @return int|float truncated toward zero unless $asFloat; NAN when either side is invalid
     */
    public function diff($input, $units = null, $asFloat = false)
    {
        if (!$this->isValid()) {
            return NAN;
        }
        $other = self::coerce($input);
        if (!$other->isValid()) {
            return NAN;
        }

        $that = $this->copy();
        if ($this->mode === self::MODE_LOCAL) {
            $that->touch(null, true); // moment: createLocal(input).local()
        } else {
            $that->touch(false);
        }
        $that->ms = $other->ms;
        $zoneDelta = ($that->offsetAt($that->ms) - $this->offsetAt($this->ms)) * 1000;
        $units = Units::normalize($units);

        switch ($units) {
            case 'year': $out = self::monthDiff($this, $that) / 12; break;
            case 'month': $out = self::monthDiff($this, $that); break;
            case 'quarter': $out = self::monthDiff($this, $that) / 3; break;
            case 'second': $out = ($this->ms - $that->ms) / 1e3; break;
            case 'minute': $out = ($this->ms - $that->ms) / 6e4; break;
            case 'hour': $out = ($this->ms - $that->ms) / 36e5; break;
            case 'day': $out = ($this->ms - $that->ms - $zoneDelta) / 864e5; break;
            case 'week': $out = ($this->ms - $that->ms - $zoneDelta) / 6048e5; break;
            default: $out = $this->ms - $that->ms;
        }

        if ($asFloat) {
            return $out;
        }

        return Calendar::trunc($out);
    }

    protected static function monthDiff(self $a, self $b)
    {
        if ($a->date() < $b->date()) {
            return -self::monthDiff($b, $a);
        }
        $whole = ($b->year() - $a->year()) * 12 + ($b->month() - $a->month());
        $anchor = $a->copy()->add($whole, 'months');

        if ($b->ms - $anchor->ms < 0) {
            $anchor2 = $a->copy()->add($whole - 1, 'months');
            $adjust = ($b->ms - $anchor->ms) / ($anchor->ms - $anchor2->ms);
        } else {
            $anchor2 = $a->copy()->add($whole + 1, 'months');
            $adjust = ($b->ms - $anchor->ms) / ($anchor2->ms - $anchor->ms);
        }

        $result = -($whole + $adjust);

        return $result ?: 0;
    }

    // ------------------------------------------------------------------ zones

    /**
     * Offset from UTC in minutes. utcOffset(480) / utcOffset('+08:00') / utcOffset(8) switch
     * to a fixed offset; $keepLocalTime keeps the wall clock instead of the instant.
     */
    public function utcOffset($input = null, $keepLocalTime = false, $keepMinutes = false)
    {
        if (!$this->isValid()) {
            return func_num_args() && $input !== null ? $this : NAN;
        }
        if (func_num_args() === 0 || $input === null) {
            return $this->offsetAt($this->ms) / 60;
        }

        if (is_string($input)) {
            $input = self::offsetFromString($input);
            if ($input === null) {
                return $this;
            }
        } elseif (abs($input) < 16 && !$keepMinutes) {
            $input = $input * 60;
        }

        $old = $this->offsetAt($this->ms);
        $wasLocal = $this->mode === self::MODE_LOCAL;
        $this->mode = self::MODE_OFFSET;
        $this->zone = null;
        $this->zoneName = null;
        $this->offsetSeconds = (int) round($input * 60);
        if ($keepLocalTime) {
            $this->ms += ($old - $this->offsetSeconds) * 1000;
            if ($wasLocal) {
                $this->touch(false);
            }
            if ($old !== $this->offsetSeconds) {
                $this->touch(true);
            }
        }

        return $this;
    }

    /** moment's offsetFromString(): minutes from the last offset in $string matching $matcher */
    public static function offsetFromString($string, $matcher = '/Z|[+-]\d\d(?::?\d\d)?/i')
    {
        if (!preg_match_all($matcher, (string) $string, $all) || !$all[0]) {
            return null;
        }
        $chunk = $all[0][count($all[0]) - 1];
        if (strtolower($chunk) === 'z') {
            return 0;
        }
        if (preg_match_all('/([\+\-]|\d\d)/', $chunk, $parts)) {
            $parts = $parts[0];
        } else {
            $parts = array('-', 0, 0);
        }
        $minutes = (int) $parts[1] * 60 + (isset($parts[2]) ? (int) $parts[2] : 0);

        return $minutes === 0 ? 0 : ($parts[0] === '+' ? $minutes : -$minutes);
    }

    public function toUtc($keepLocalTime = false)
    {
        return $this->utcOffset(0, $keepLocalTime);
    }

    public function local($keepLocalTime = false)
    {
        if ($this->mode === self::MODE_LOCAL || !$this->isValid()) {
            return $this;
        }
        $old = $this->offsetAt($this->ms);
        $this->mode = self::MODE_LOCAL;
        $this->zone = null;
        $this->zoneName = null;
        if ($keepLocalTime) {
            $this->ms += ($old - $this->offsetAt($this->ms)) * 1000;
        }

        return $this;
    }

    /** tz() returns the zone name or null; tz('Asia/Taipei') moves the moment into that zone. */
    public function getSetTz($name = null, $keepLocalTime = false)
    {
        if (func_num_args() === 0 || $name === null) {
            return $this->mode === self::MODE_ZONE ? $this->zoneName : null;
        }
        if (!$this->isValid()) {
            return $this;
        }

        $old = $this->offsetAt($this->ms);
        $zone = Tz::zone($name);
        if ($zone === null) {
            // moment-timezone logs the unknown name but drops the zone it had, keeping the current offset
            if ($this->mode === self::MODE_ZONE) {
                $this->mode = self::MODE_OFFSET;
                $this->offsetSeconds = $old;
                $this->zone = null;
                $this->zoneName = null;
            }

            return $this;
        }
        $wasLocal = $this->mode === self::MODE_LOCAL;
        $this->mode = self::MODE_ZONE;
        $this->zone = $zone;
        $this->zoneName = $zone->name;
        if ($keepLocalTime) {
            $this->ms = $this->resolve($this->ms + $old * 1000, $this->offsetAt($this->ms));
            $this->touch(true);
            if ($wasLocal) {
                $this->touch(false);
            }
            if ($this->offsetAt($this->ms) !== $old) {
                $this->touch(true);
            }
        } else {
            $this->touch(null);
        }

        return $this;
    }

    public function isUtc()
    {
        return $this->isValid() && $this->mode !== self::MODE_LOCAL && $this->offsetAt($this->ms) === 0;
    }

    public function isUtcOffset()
    {
        return $this->isValid() && $this->mode !== self::MODE_LOCAL;
    }

    public function isLocal()
    {
        return $this->isValid() && $this->mode === self::MODE_LOCAL;
    }

    public function zoneAbbr()
    {
        if ($this->mode === self::MODE_ZONE && $this->zone) {
            return $this->zone->abbr($this->ms === null ? NAN : $this->ms);
        }

        // moment: any moment in a fixed-offset or UTC mode says "UTC", whatever its offset
        return $this->mode === self::MODE_OFFSET ? 'UTC' : '';
    }

    public function zoneName()
    {
        if ($this->mode === self::MODE_ZONE) {
            return $this->zoneAbbr();
        }

        return $this->mode === self::MODE_OFFSET ? 'Coordinated Universal Time' : '';
    }

    // ------------------------------------------------------------------ relative time

    /** moment.duration(...) */
    public static function duration($input = null, $unit = null)
    {
        return Duration::create($input, $unit);
    }

    /** "2 hours ago" relative to $time (a moment input); $withoutSuffix drops "ago"/"in" */
    public function from($time, $withoutSuffix = false)
    {
        if ($this->isValid() && self::coerce($time)->isValid()) {
            return Duration::create(array('to' => $this, 'from' => $time))
                ->locale($this->getSetLocale())->humanize(!$withoutSuffix);
        }

        return $this->locale->invalidDate();
    }

    public function fromNow($withoutSuffix = false)
    {
        return $this->from(self::create(), $withoutSuffix);
    }

    public function to($time, $withoutSuffix = false)
    {
        if ($this->isValid() && self::coerce($time)->isValid()) {
            return Duration::create(array('from' => $this, 'to' => $time))
                ->locale($this->getSetLocale())->humanize(!$withoutSuffix);
        }

        return $this->locale->invalidDate();
    }

    public function toNow($withoutSuffix = false)
    {
        return $this->to(self::create(), $withoutSuffix);
    }

    /**
     * "Today at 2:30 PM" style output. $time defaults to now; $formats overrides the locale's
     * calendar formats by key (sameDay, nextDay, nextWeek, lastDay, lastWeek, sameElse) with a
     * format string or a callable receiving ($now, $moment) (JavaScript passes the moment as `this`).
     */
    public function calendar($time = null, $formats = null)
    {
        if (is_array($time) && $formats === null) {
            $formats = $time;
            $time = null;
        }
        $now = $time !== null ? self::coerce($time)->copy() : self::create();
        $sod = $this->copy()->setInstant($now->valueOf())->startOf('day');

        $key = (self::$calendarFormat !== null ? call_user_func(self::$calendarFormat, $this, $sod) : self::defaultCalendarFormat($this, $sod)) ?: 'sameElse';

        $output = null;
        if (is_array($formats) && isset($formats[$key])) {
            $output = is_callable($formats[$key]) && !is_string($formats[$key]) ? call_user_func($formats[$key], $now, $this) : $formats[$key];
        }

        return $this->format($output ?: $this->locale->calendar($key, $this, $now));
    }

    // ------------------------------------------------------------------ locale / output

    /** locale() returns the abbreviation; locale('zh-tw') switches this moment's locale. (Called as locale() / localeData() / utc() / tz() / unix(): see __call.) */
    public function getSetLocale($key = null)
    {
        if (func_num_args() === 0 || $key === null) {
            return $this->locale->abbr;
        }
        $locale = Registry::get($key);
        if ($locale) {
            $this->locale = $locale;
        }

        return $this;
    }

    /** @return Locale */
    public function getLocaleData()
    {
        return $this->locale;
    }

    public function format($format = null)
    {
        if (!$format) {
            $format = $this->isUtc() ? self::$defaultFormatUtc : self::$defaultFormat;
        }

        return Formatter::format($this, $format);
    }

    public function toISOString($keepOffset = false)
    {
        if (!$this->isValid()) {
            return null;
        }
        $utc = $keepOffset !== true;
        $m = $utc ? $this->copy()->toUtc() : $this;
        $year = $m->year();
        if ($year < 0 || $year > 9999) {
            return Formatter::format($m, $utc ? 'YYYYYY-MM-DD[T]HH:mm:ss.SSS[Z]' : 'YYYYYY-MM-DD[T]HH:mm:ss.SSSZ', false);
        }

        return Formatter::format($m, $utc ? 'YYYY-MM-DD[T]HH:mm:ss.SSS[Z]' : 'YYYY-MM-DD[T]HH:mm:ss.SSSZ', false);
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

    /** "Sat Jan 06 2024 12:00:00 GMT+0800" */
    public function toString()
    {
        return $this->copy()->getSetLocale('en')->format('ddd MMM DD YYYY HH:mm:ss [GMT]ZZ');
    }

    public function __toString()
    {
        return $this->toString();
    }

    // ------------------------------------------------------------------ how it was created

    /** moment's parsingFlags(): what the parser saw. */
    public function parsingFlags()
    {
        return $this->pf;
    }

    /** moment's invalidAt(): index of the part that overflowed (0 year .. 6 ms, 7 week, 8 weekday), -1 none, -2 not parsed. */
    public function invalidAt()
    {
        return $this->pf['overflow'];
    }

    /** moment's creationData(): array('input', 'format', 'locale' (a Locale), 'isUTC', 'strict'). */
    public function creationData()
    {
        return array('input' => $this->input, 'format' => $this->format, 'locale' => $this->locale,
            'isUTC' => $this->mode !== self::MODE_LOCAL, 'strict' => $this->strict);
    }

    /** Whether reading the input's date parts in this moment's zone moved them (DST gap). */
    public function isDSTShifted()
    {
        if ($this->parsedArray === null) {
            return false;
        }
        $a = $this->parsedArray;
        $other = $this->mode !== self::MODE_LOCAL ? self::createUtc($a) : self::create($a);
        $b = $other->toArray();

        $diffs = abs(count($a) - count($b));
        for ($i = 0, $n = min(count($a), count($b)); $i < $n; $i++) {
            if ((int) $a[$i] !== (int) $b[$i]) {
                $diffs++;
            }
        }

        return $this->isValid() && $diffs > 0;
    }

    /** Switch to the UTC offset the input string carried (or UTC when it had none), like moment's parseZone(). */
    public function applyParsedZone()
    {
        if ($this->tzm !== null) {
            $this->utcOffset($this->tzm, false, true);
        } elseif (is_string($this->input)) {
            $offset = self::offsetFromString($this->input, '/Z|[+-]\d\d:?\d\d/i');
            if ($offset !== null) {
                $this->utcOffset($offset);
            } else {
                $this->utcOffset(0, true);
            }
        }

        return $this;
    }

    /** touch(), plus the second call moment-timezone's updateOffset makes when a zone's offset changed under keepTime */
    protected function announce($keepTime, $oldOffset)
    {
        $this->touch($keepTime);
        if ($keepTime && $this->mode === self::MODE_ZONE && $this->isValid() && $this->offsetAt($this->ms) !== $oldOffset) {
            $this->touch(true);
        }
    }

    protected function touch($keepTime, $construct = false)
    {
        if (self::$updateOffset === null || $this->quiet) {
            return;
        }
        if (!$construct) {
            call_user_func(self::$updateOffset, $this, $keepTime);

            return;
        }
        // a new moment announces itself once; moments the hook itself creates stay quiet (moment's updateInProgress)
        if (!self::$updating) {
            self::$updating = true;
            try {
                call_user_func(self::$updateOffset, $this, null);
            } catch (\Exception $e) {
                self::$updating = false;
                throw $e;
            }
            self::$updating = false;
        }
    }

    /**
     * moment.fn.name = function () {...}: add a method to every moment. The callable gets the moment
     * first, then the call's arguments: Moment::macro('endOfNoon', function ($m) { return $m->hour(12)->endOf('hour'); }).
     */
    public static function macro($name, $callable)
    {
        self::$macros[$name] = $callable;
    }

    /** @internal moment-timezone's needsOffset(): the input was read as wall-clock parts, with no offset of its own */
    public function needsOffset()
    {
        return $this->parsedArray !== null && $this->tzm === null && $this->format !== 'X' && $this->format !== 'x';
    }

    // ------------------------------------------------------------------ deprecation

    /** Warn once per $name (E_USER_DEPRECATED) and tell moment.deprecationHandler every time. */
    public static function deprecate($name, $message)
    {
        if (self::$deprecationHandler !== null) {
            call_user_func(self::$deprecationHandler, $name, $message);
        }
        if (!self::$suppressDeprecationWarnings && !isset(self::$deprecated[$name])) {
            self::$deprecated[$name] = true;
            trigger_error('Deprecation warning: ' . $message, E_USER_DEPRECATED);
        }
    }

    // ------------------------------------------------------------------ more getters

    public function dates($value = null)
    {
        return call_user_func_array(array($this, 'date'), func_get_args());
    }

    public function days($value = null)
    {
        return call_user_func_array(array($this, 'day'), func_get_args());
    }

    public function hours($value = null)
    {
        return call_user_func_array(array($this, 'hour'), func_get_args());
    }

    public function minutes($value = null)
    {
        return call_user_func_array(array($this, 'minute'), func_get_args());
    }

    public function seconds($value = null)
    {
        return call_user_func_array(array($this, 'second'), func_get_args());
    }

    public function milliseconds($value = null)
    {
        return call_user_func_array(array($this, 'millisecond'), func_get_args());
    }

    public function years($value = null)
    {
        return call_user_func_array(array($this, 'year'), func_get_args());
    }

    public function weeks($value = null)
    {
        return call_user_func_array(array($this, 'week'), func_get_args());
    }

    public function isoWeeks($value = null)
    {
        return call_user_func_array(array($this, 'isoWeek'), func_get_args());
    }

    public function quarters($value = null)
    {
        return call_user_func_array(array($this, 'quarter'), func_get_args());
    }

    public function isoWeeksInISOWeekYear()
    {
        if (!$this->isValid()) {
            return NAN;
        }

        return Calendar::weeksInYear($this->isoWeekYear(), 1, 4);
    }

    public function weeksInWeekYear()
    {
        if (!$this->isValid()) {
            return NAN;
        }

        return Calendar::weeksInYear($this->weekYear(), $this->locale->firstDayOfWeek(), $this->locale->firstDayOfYear());
    }

    public function eraName()
    {
        return Formatter::era($this, 'name');
    }

    public function eraAbbr()
    {
        return Formatter::era($this, 'abbr');
    }

    public function eraNarrow()
    {
        return Formatter::era($this, 'narrow');
    }

    public function eraYear()
    {
        return Formatter::eraYear($this);
    }

    /** Whether the offset from $input (default UTC) is a whole number of hours. */
    public function hasAlignedHourOffset($input = null)
    {
        if (!$this->isValid()) {
            return false;
        }
        $other = $input ? self::create($input)->utcOffset() : 0;

        return fmod($this->utcOffset() - $other, 60) == 0;
    }

    /** Whether this moment is in daylight saving time (its offset exceeds January's or June's). */
    public function isDST()
    {
        if (!$this->isValid()) {
            return false;
        }

        return $this->utcOffset() > $this->copy()->month(0)->utcOffset()
            || $this->utcOffset() > $this->copy()->month(5)->utcOffset();
    }

    /** A string for debugging, like `moment("2024-01-31T12:00:00.000")`. */
    public function inspect()
    {
        if (!$this->isValid()) {
            return 'moment.invalid(/* ' . (is_float($this->input) ? Duration::jsNumber($this->input) : ($this->input === null ? 'null' : $this->input)) . ' */)';
        }
        $func = 'moment';
        $zone = '';
        if (!$this->isLocal()) {
            $func = $this->utcOffset() == 0 ? 'moment.utc' : 'moment.parseZone';
            $zone = 'Z';
        }
        $year = $this->year();

        return $this->format('[' . $func . '("]' . (0 <= $year && $year <= 9999 ? 'YYYY' : 'YYYYYY') . '-MM-DD[T]HH:mm:ss.SSS' . $zone . '[")]');
    }

    protected static function defaultCalendarFormat(self $m, self $now)
    {
        $diff = $m->diff($now, 'days', true);

        return $diff < -6 ? 'sameElse' : ($diff < -1 ? 'lastWeek' : ($diff < 0 ? 'lastDay'
            : ($diff < 1 ? 'sameDay' : ($diff < 2 ? 'nextDay' : ($diff < 7 ? 'nextWeek' : 'sameElse')))));
    }

    // ------------------------------------------------------------------ calls that moment spells the same for instances and statics

    /**
     * moment.js uses one name for a static function and an instance method in a few places, which
     * PHP can't: utc, unix, tz, locale, localeData, parseZone, lang, months, min, max (plus the
     * deprecated zone, and as()/clone() on Duration, which are reserved words in PHP 5). Both forms
     * are available here through __call / __callStatic.
     */
    public function __call($name, $args)
    {
        switch ($name) {
            case 'utc':
                return $this->toUtc(isset($args[0]) ? $args[0] : false);
            case 'unix':
                return $this->unixSeconds();
            case 'tz':
                return call_user_func_array(array($this, 'getSetTz'), $args);
            case 'locale':
                return call_user_func_array(array($this, 'getSetLocale'), $args);
            case 'localeData':
                return $this->getLocaleData();
            case 'months':
                return call_user_func_array(array($this, 'month'), $args);
            case 'clone':
                return $this->copy();
            case 'lang':
                self::deprecate('lang', 'moment().lang() is deprecated. Instead, use moment().localeData() to get the language configuration. Use moment().locale() to change locales.');

                return $args ? $this->getSetLocale($args[0]) : $this->getLocaleData();
            case 'zone':
                self::deprecate('zone', 'moment().zone is deprecated, use moment().utcOffset instead. http://momentjs.com/guides/#/warnings/zone/');
                if ($args && $args[0] !== null) {
                    $input = is_string($args[0]) ? $args[0] : -$args[0];

                    return $this->utcOffset($input, isset($args[1]) ? $args[1] : false);
                }

                return -$this->utcOffset();
            case 'min':
            case 'max':
                self::deprecate($name, 'moment().' . $name . ' is deprecated, use moment.' . ($name === 'min' ? 'max' : 'min') . ' instead. http://momentjs.com/guides/#/warnings/min-max/');
                $other = call_user_func_array(array(__CLASS__, 'create'), $args);
                if ($this->isValid() && $other->isValid()) {
                    return ($name === 'min' ? $other->ms < $this->ms : $other->ms > $this->ms) ? $this : $other;
                }

                return self::invalid();
            case 'parseZone':
                return $this->applyParsedZone();
        }

        if (isset(self::$macros[$name])) {
            return call_user_func_array(self::$macros[$name], array_merge(array($this), $args));
        }

        throw new \BadMethodCallException('Call to undefined method ' . __CLASS__ . '::' . $name . '()');
    }

    public static function __callStatic($name, $args)
    {
        switch ($name) {
            case 'utc':
                return call_user_func_array(array(__CLASS__, 'createUtc'), $args);
            case 'unix':
                return self::fromUnix($args[0]);
            case 'tz':
                return call_user_func_array(array(__CLASS__, 'createInZone'), $args);
            case 'min':
                return call_user_func_array(array(__CLASS__, 'minOf'), $args);
            case 'max':
                return call_user_func_array(array(__CLASS__, 'maxOf'), $args);
            case 'parseZone':
                return call_user_func_array(array(__CLASS__, 'create'), $args)->applyParsedZone();
            case 'locale':
                if (isset($args[1]) && $args[0]) {
                    return Registry::define($args[0], $args[1])->abbr;
                }

                return Registry::globalLocale(isset($args[0]) ? $args[0] : null);
            case 'lang':
                self::deprecate('lang', 'moment.lang is deprecated. Use moment.locale instead.');

                return call_user_func_array(array(__CLASS__, 'locale'), $args);
            case 'localeData':
                return Registry::get(isset($args[0]) ? $args[0] : null);
            case 'langData':
                self::deprecate('langData', 'moment.langData is deprecated. Use moment.localeData instead.');

                return Registry::get(isset($args[0]) ? $args[0] : null);
            case 'defineLocale':
                return call_user_func_array(array('Wilkques\Moment\Locale\Registry', 'define'), $args);
            case 'updateLocale':
                return call_user_func_array(array('Wilkques\Moment\Locale\Registry', 'update'), $args);
            case 'locales':
                return Registry::names();
            case 'months':
                return self::listMonths(isset($args[0]) ? $args[0] : null, isset($args[1]) ? $args[1] : null, 'months');
            case 'monthsShort':
                return self::listMonths(isset($args[0]) ? $args[0] : null, isset($args[1]) ? $args[1] : null, 'monthsShort');
            case 'weekdays':
            case 'weekdaysShort':
            case 'weekdaysMin':
                return self::listWeekdays(isset($args[0]) ? $args[0] : null, isset($args[1]) ? $args[1] : null, isset($args[2]) ? $args[2] : null, $name);
            case 'defaultZone':
                return Tz::$defaultZone;
            case 'normalizeUnits':
                return Units::normalize($args[0]);
            case 'isDate':
                return $args[0] instanceof DateTime;
            case 'isDuration':
                return Duration::isDuration($args[0]);
            case 'relativeTimeThreshold':
                return call_user_func_array(array('Wilkques\Moment\Duration', 'relativeTimeThreshold'), $args);
            case 'relativeTimeRounding':
                return call_user_func_array(array('Wilkques\Moment\Duration', 'relativeTimeRounding'), $args);
        }

        throw new \BadMethodCallException('Call to undefined method ' . __CLASS__ . '::' . $name . '()');
    }

    protected static function listOne($format, $index, $field, $unit)
    {
        $mom = self::createUtc();
        if (!is_float($index) || !is_nan($index)) {
            $mom->set($unit, $index);
        }

        return Registry::get()->$field($mom, $format);
    }

    protected static function listMonths($format, $index, $field)
    {
        if (is_numeric($format) && !is_string($format)) {
            $index = $format;
            $format = null;
        }
        $format = $format ?: '';
        if ($index !== null) {
            return self::listOne($format, $index, $field, 'month');
        }
        $out = array();
        for ($i = 0; $i < 12; $i++) {
            $out[$i] = self::listOne($format, $i, $field, 'month');
        }

        return $out;
    }

    protected static function listWeekdays($localeSorted, $format, $index, $field)
    {
        if (is_bool($localeSorted)) {
            if (is_numeric($format) && !is_string($format)) {
                $index = $format;
                $format = null;
            }
            $format = $format ?: '';
        } else {
            $format = $localeSorted;
            $index = $format;
            $localeSorted = false;
            if (is_numeric($format) && !is_string($format)) {
                $index = $format;
                $format = null;
            }
            $format = $format ?: '';
        }

        $locale = Registry::get();
        $shift = $localeSorted ? $locale->firstDayOfWeek() : 0;

        if ($index !== null) {
            // like moment, a non-numeric $index (e.g. a format string given alone) is NaN: no weekday is set
            return self::listOne($format, is_numeric($index) ? ($index + $shift) % 7 : NAN, $field, 'day');
        }
        $out = array();
        for ($i = 0; $i < 7; $i++) {
            $out[$i] = self::listOne($format, ($i + $shift) % 7, $field, 'day');
        }

        return $out;
    }
}
