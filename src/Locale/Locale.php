<?php

namespace Wilkques\Moment\Locale;

use Wilkques\Moment\Duration;
use Wilkques\Moment\Formatter;
use Wilkques\Moment\Moment;

/**
 * A moment.js locale object (moment's `Locale` prototype). The properties hold moment's
 * built-in English defaults ("baseConfig"); a locale class overrides them in its static
 * config() and overrides methods for the settings that are functions in the JS version.
 *
 * Regular expressions are full PCRE patterns including delimiters and flags, e.g. '~^janv~iu'.
 * Wherever moment passes a moment to a locale method, a Moment (or, as a shortcut, a 0-based
 * month / weekday index) is accepted here.
 */
class Locale
{
    /** moment's matchWord: a run of letters, used when a locale has no month/weekday regex of its own */
    const MATCH_WORD = "~[0-9]{0,256}['a-z\\x{00A0}-\\x{05FF}\\x{0700}-\\x{D7FF}\\x{F900}-\\x{FDCF}\\x{FDF0}-\\x{FF07}\\x{FF10}-\\x{FFEF}]{1,256}|[\\x{0600}-\\x{06FF}\\/]{1,256}(\\s*?[\\x{0600}-\\x{06FF}]{1,256}){1,2}~iu";

    const MONTHS_IN_FORMAT = '~D[oD]?(\[[^\[\]]*\]|\s)+MMMM?~u';

    public $abbr = 'en';
    /** @var string|null the name of the locale this one extends */
    public $parentLocale;

    protected $months = array('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');
    protected $monthsShort = array('Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec');
    protected $weekdays = array('Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday');
    protected $weekdaysShort = array('Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat');
    protected $weekdaysMin = array('Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa');

    protected $monthsParseExact = false;
    protected $weekdaysParseExact = false;

    protected $monthsRegex;
    protected $monthsShortRegex;
    protected $monthsStrictRegex;
    protected $monthsShortStrictRegex;
    protected $weekdaysRegex;
    protected $weekdaysShortRegex;
    protected $weekdaysMinRegex;
    protected $weekdaysStrictRegex;
    protected $weekdaysShortStrictRegex;
    protected $weekdaysMinStrictRegex;

    /** @var array|null per-month patterns (loose / long / short), filled lazily when a locale gives none */
    protected $monthsParse;
    protected $longMonthsParse;
    protected $shortMonthsParse;
    protected $weekdaysParse;
    protected $fullWeekdaysParse;
    protected $shortWeekdaysParse;
    protected $minWeekdaysParse;

    protected $longDateFormat = array(
        'LTS' => 'h:mm:ss A',
        'LT' => 'h:mm A',
        'L' => 'MM/DD/YYYY',
        'LL' => 'MMMM D, YYYY',
        'LLL' => 'MMMM D, YYYY h:mm A',
        'LLLL' => 'dddd, MMMM D, YYYY h:mm A',
    );

    protected $calendar = array(
        'sameDay' => '[Today at] LT',
        'nextDay' => '[Tomorrow at] LT',
        'nextWeek' => 'dddd [at] LT',
        'lastDay' => '[Yesterday at] LT',
        'lastWeek' => '[Last] dddd [at] LT',
        'sameElse' => 'L',
    );

    protected $relativeTime = array(
        'future' => 'in %s', 'past' => '%s ago',
        's' => 'a few seconds', 'ss' => '%d seconds',
        'm' => 'a minute', 'mm' => '%d minutes',
        'h' => 'an hour', 'hh' => '%d hours',
        'd' => 'a day', 'dd' => '%d days',
        'w' => 'a week', 'ww' => '%d weeks',
        'M' => 'a month', 'MM' => '%d months',
        'y' => 'a year', 'yy' => '%d years',
    );

    /** dow: first day of week (0 = Sunday); doy: the week containing Jan <doy> is week 1 */
    protected $week = array('dow' => 0, 'doy' => 6);

    protected $invalidDate = 'Invalid date';
    protected $ordinal = '%d';
    protected $dayOfMonthOrdinalParse = '~\d{1,2}~u';
    protected $meridiemParse = '~[ap]\.?m?\.?~iu';

    /** @var array|null list of array('since', 'until', 'offset', 'name', 'narrow', 'abbr'); null = English's */
    protected $eras;
    protected $eraYearOrdinalRegex;

    /** @var array function-valued settings given at runtime (see set()), keyed by setting name */
    protected $functions = array();

    /** @var array the settings last given to set() */
    protected $config = array();

    protected $dayOfMonthOrdinalParseLenient;
    protected $exact = array();
    protected $erasCache = array();

    /**
     * @param array $config settings merged over this class's config() chain (see mergeConfigs())
     */
    public function __construct(array $config = array())
    {
        $chain = array();
        for ($class = get_class($this); $class; $class = get_parent_class($class)) {
            array_unshift($chain, $class);
        }
        // like moment's defineLocale(): settings merge over the base config (the English defaults held in the properties)
        $merged = array('longDateFormat' => $this->longDateFormat, 'calendar' => $this->calendar,
            'relativeTime' => $this->relativeTime, 'week' => $this->week);
        foreach ($chain as $class) {
            $merged = self::mergeConfigs($merged, call_user_func(array($class, 'config')));
        }
        $this->set(self::mergeConfigs($merged, $config));
    }

    /** @return array the settings this locale was built from (moment's _config) */
    public function getConfig()
    {
        return $this->config;
    }

    /** The settings that differ from moment's defaults. Locale classes override this. */
    public static function config()
    {
        return array();
    }

    /**
     * moment's mergeConfigs(): plain objects (associative arrays) merge key by key, lists and
     * scalars from the child replace the parent's, and a null in the child deletes the key.
     */
    public static function mergeConfigs(array $parent, array $child)
    {
        $res = $parent;
        foreach ($child as $prop => $value) {
            if (isset($parent[$prop]) && self::isObject($parent[$prop]) && self::isObject($value)) {
                $res[$prop] = array_merge($parent[$prop], $value);
            } elseif ($value !== null) {
                $res[$prop] = $value;
            } else {
                unset($res[$prop]);
            }
        }

        return $res;
    }

    protected static function isObject($value)
    {
        return is_array($value) && $value && array_keys($value) !== range(0, count($value) - 1);
    }

    /**
     * Apply settings. Callables are kept as function-valued settings (ordinal, meridiem, isPM,
     * meridiemHour, preparse, postformat, ...), and may also sit inside the calendar and
     * relativeTime arrays.
     */
    public function set(array $config)
    {
        $this->config = $config;
        foreach ($config as $key => $value) {
            if ($key === 'abbr') {
                $this->abbr = $value;
            } elseif ($key === 'parentLocale') {
                $this->parentLocale = $value;
            } elseif (is_object($value) && is_callable($value)) {
                $this->functions[$key] = $value;
            } elseif (property_exists($this, $key) && $key !== 'functions') {
                $this->$key = $value;
            }
        }
        $this->exact = array();
        $this->erasCache = array();
        preg_match('/^(.)(.*)\1([a-z]*)$/su', $this->dayOfMonthOrdinalParse, $m);
        $this->dayOfMonthOrdinalParseLenient = $m[1] . $m[2] . '|\d{1,2}' . $m[1] . $m[3];

        return $this;
    }

    // ------------------------------------------------------------------ helpers

    /** @internal tests: act as if ext-mbstring were missing */
    public static $withoutMbstring = false;

    /** @internal tests: act as if PHP's PCRE were the one bundled with PHP 5.3 */
    public static $oldPcre = false;

    /** toLocaleLowerCase(): mbstring when available, else a table covering Latin, Greek, Cyrillic, Armenian, Georgian */
    protected static function lower($s)
    {
        if (!self::$withoutMbstring && function_exists('mb_strtolower')) {
            return mb_strtolower($s, 'UTF-8');
        }

        return preg_replace_callback('/[^\x00-\x7F]/u', array(__CLASS__, 'lowerChar'), strtolower($s));
    }

    protected static function lowerChar($match)
    {
        $c = $match[0];
        $o = ord($c[0]);
        $cp = $o < 0xE0 ? (($o & 0x1F) << 6) | (ord($c[1]) & 0x3F)
            : ($o < 0xF0 ? (($o & 0x0F) << 12) | ((ord($c[1]) & 0x3F) << 6) | (ord($c[2]) & 0x3F)
            : ((($o & 0x07) << 18) | ((ord($c[1]) & 0x3F) << 12) | ((ord($c[2]) & 0x3F) << 6) | (ord($c[3]) & 0x3F)));

        static $special = array(0x178 => 0xFF, 0x18F => 0x259, 0x186 => 0x254, 0x190 => 0x25B, 0x18E => 0x1DD, 0x1B7 => 0x292,
            0x386 => 0x3AC, 0x38C => 0x3CC, 0x4C0 => 0x4CF,
            // Latin Extended-B, Greek and Latin Extended Additional exceptions
            0x181 => 0x253, 0x182 => 0x183, 0x184 => 0x185, 0x187 => 0x188, 0x189 => 0x256, 0x18A => 0x257, 0x18B => 0x18C, 0x191 => 0x192,
            0x193 => 0x260, 0x194 => 0x263, 0x196 => 0x269, 0x197 => 0x268, 0x198 => 0x199, 0x19C => 0x26F, 0x19D => 0x272, 0x19F => 0x275,
            0x1A6 => 0x280, 0x1A7 => 0x1A8, 0x1A9 => 0x283, 0x1AC => 0x1AD, 0x1AE => 0x288, 0x1B1 => 0x28A, 0x1B2 => 0x28B, 0x1B3 => 0x1B4,
            0x1B5 => 0x1B6, 0x1B8 => 0x1B9, 0x1BC => 0x1BD, 0x1C4 => 0x1C6, 0x1C5 => 0x1C6, 0x1C7 => 0x1C9, 0x1C8 => 0x1C9, 0x1CA => 0x1CC,
            0x1CB => 0x1CC, 0x1F1 => 0x1F3, 0x1F2 => 0x1F3, 0x1F4 => 0x1F5, 0x1F6 => 0x195, 0x1F7 => 0x1BF, 0x220 => 0x19E, 0x23A => 0x2C65,
            0x23B => 0x23C, 0x23D => 0x19A, 0x23E => 0x2C66, 0x241 => 0x242, 0x243 => 0x180, 0x244 => 0x289, 0x245 => 0x28C,
            0x370 => 0x371, 0x372 => 0x373, 0x376 => 0x377, 0x37F => 0x3F3, 0x3CF => 0x3D7, 0x3F4 => 0x3B8, 0x3F7 => 0x3F8, 0x3F9 => 0x3F2,
            0x3FA => 0x3FB, 0x3FD => 0x37B, 0x3FE => 0x37C, 0x3FF => 0x37D, 0x1E9E => 0xDF, 0x10C7 => 0x2D27, 0x10CD => 0x2D2D,
            0x1FBA => 0x1F70, 0x1FBB => 0x1F71, 0x1FC8 => 0x1F72, 0x1FC9 => 0x1F73, 0x1FCA => 0x1F74, 0x1FCB => 0x1F75, 0x1FDA => 0x1F76,
            0x1FDB => 0x1F77, 0x1FF8 => 0x1F78, 0x1FF9 => 0x1F79, 0x1FEA => 0x1F7A, 0x1FEB => 0x1F7B, 0x1FFA => 0x1F7C, 0x1FFB => 0x1F7D,
            0x1FBC => 0x1FB3, 0x1FCC => 0x1FC3, 0x1FFC => 0x1FF3, 0x1FEC => 0x1FE5, 0x1FB8 => 0x1FB0, 0x1FB9 => 0x1FB1, 0x1FD8 => 0x1FD0,
            0x1FD9 => 0x1FD1, 0x1FE8 => 0x1FE0, 0x1FE9 => 0x1FE1, 0x1F59 => 0x1F51, 0x1F5B => 0x1F53, 0x1F5D => 0x1F55, 0x1F5F => 0x1F57);
        if ($cp === 0x130) {
            return "i\xCC\x87";
        }
        if (isset($special[$cp])) {
            $low = $special[$cp];
        } elseif (($cp >= 0xC0 && $cp <= 0xDE && $cp !== 0xD7) || ($cp >= 0x391 && $cp <= 0x3AB && $cp !== 0x3A2) || ($cp >= 0x410 && $cp <= 0x42F)) {
            $low = $cp + 32;
        } elseif (($cp >= 0x100 && $cp <= 0x137) || ($cp >= 0x14A && $cp <= 0x177) || ($cp >= 0x460 && $cp <= 0x481)
            || ($cp >= 0x48A && $cp <= 0x4BF) || ($cp >= 0x4D0 && $cp <= 0x52F) || ($cp >= 0x1E00 && $cp <= 0x1E95) || ($cp >= 0x1EA0 && $cp <= 0x1EFF)
            || ($cp >= 0x1A0 && $cp <= 0x1A5) || ($cp >= 0x1DE && $cp <= 0x1EF) || ($cp >= 0x1F8 && $cp <= 0x21F) || ($cp >= 0x222 && $cp <= 0x233) || ($cp >= 0x246 && $cp <= 0x24F)) {
            $low = $cp % 2 === 0 ? $cp + 1 : $cp;
        } elseif ($cp === 0x1AF) {
            $low = 0x1B0; // Ư (Vietnamese)
        } elseif (($cp >= 0x139 && $cp <= 0x148) || ($cp >= 0x179 && $cp <= 0x17E) || ($cp >= 0x4C1 && $cp <= 0x4CE) || ($cp >= 0x1CD && $cp <= 0x1DC)) {
            $low = $cp % 2 === 1 ? $cp + 1 : $cp;
        } elseif (($cp >= 0x3D8 && $cp <= 0x3EF)) {
            $low = $cp % 2 === 0 ? $cp + 1 : $cp;
        } elseif ($cp >= 0x10A0 && $cp <= 0x10C5) {
            $low = $cp + 0x1C60;
        } elseif (($cp >= 0x1F08 && $cp <= 0x1F0F) || ($cp >= 0x1F18 && $cp <= 0x1F1D) || ($cp >= 0x1F28 && $cp <= 0x1F2F) || ($cp >= 0x1F38 && $cp <= 0x1F3F)
            || ($cp >= 0x1F48 && $cp <= 0x1F4D) || ($cp >= 0x1F68 && $cp <= 0x1F6F) || ($cp >= 0x1F88 && $cp <= 0x1F8F) || ($cp >= 0x1F98 && $cp <= 0x1F9F)
            || ($cp >= 0x1FA8 && $cp <= 0x1FAF)) {
            $low = $cp - 8;
        } elseif ($cp >= 0x388 && $cp <= 0x38A) {
            $low = $cp + 37;
        } elseif ($cp === 0x38E || $cp === 0x38F) {
            $low = $cp + 63;
        } elseif ($cp >= 0x400 && $cp <= 0x40F) {
            $low = $cp + 80;
        } elseif ($cp >= 0x531 && $cp <= 0x556) {
            $low = $cp + 48;
        } elseif ($cp >= 0x1C90 && $cp <= 0x1CBF && $cp !== 0x1CBB && $cp !== 0x1CBC) {
            $low = $cp - 0xBC0;
        } else {
            return $c;
        }

        return $low < 0x800 ? chr(0xC0 | ($low >> 6)) . chr(0x80 | ($low & 0x3F))
            : chr(0xE0 | ($low >> 12)) . chr(0x80 | (($low >> 6) & 0x3F)) . chr(0x80 | ($low & 0x3F));
    }

    protected static function len($s)
    {
        return !self::$withoutMbstring && function_exists('mb_strlen') ? mb_strlen($s, 'UTF-8') : strlen($s);
    }

    protected static function regexEscape($s)
    {
        return preg_quote($s, '~');
    }

    /** moment's sort(cmpLenRev): longest first, stable */
    protected static function sortByLengthDesc(array $pieces)
    {
        $keyed = array();
        foreach (array_values($pieces) as $i => $p) {
            $keyed[] = array(self::len($p), $i, $p);
        }
        usort($keyed, function ($a, $b) {
            return $a[0] === $b[0] ? $a[1] - $b[1] : $b[0] - $a[0];
        });
        $out = array();
        foreach ($keyed as $k) {
            $out[] = $k[2];
        }

        return $out;
    }

    protected function call($key, array $args, $default = null)
    {
        return isset($this->functions[$key]) ? call_user_func_array($this->functions[$key], $args) : $default;
    }

    protected static function utcMonth($i)
    {
        return Moment::createUtc(array(2000, $i));
    }

    protected static function utcWeekday($i)
    {
        $m = Moment::createUtc(array(2000, 1));

        return $m->day($i);
    }

    protected static function monthIndex($m)
    {
        return $m instanceof Moment ? $m->month() : (int) $m;
    }

    protected static function dayIndex($m)
    {
        return $m instanceof Moment ? $m->day() : (int) $m;
    }

    // ------------------------------------------------------------------ months

    /**
     * months(): the list; months($moment, $format): one name. With separate "format" and
     * "standalone" lists the format string decides which one applies.
     */
    public function months($m = null, $format = '')
    {
        $custom = $this->call('months', array($m, $format));
        if ($custom !== null) {
            return $custom;
        }

        return $this->pick($this->months, $m, $format, true);
    }

    public function monthsShort($m = null, $format = '')
    {
        $custom = $this->call('monthsShort', array($m, $format));
        if ($custom !== null) {
            return $custom;
        }

        return $this->pick($this->monthsShort, $m, $format, true);
    }

    protected function pick($list, $m, $format, $isMonth)
    {
        if (!self::isObject($list)) {
            return $m === null ? $list : $list[self::monthIndex($m)];
        }
        if ($m === null) {
            return $list['standalone'];
        }
        $pattern = isset($list['isFormat']) ? $list['isFormat'] : self::MONTHS_IN_FORMAT;

        return $list[preg_match($pattern, (string) $format) ? 'format' : 'standalone'][self::monthIndex($m)];
    }

    protected function monthsExact($name, $format, $strict)
    {
        if (!isset($this->exact['months'])) {
            $short = array();
            $long = array();
            for ($i = 0; $i < 12; ++$i) {
                $mom = self::utcMonth($i);
                $short[$i] = self::lower($this->monthsShort($mom, ''));
                $long[$i] = self::lower($this->months($mom, ''));
            }
            $this->exact['months'] = array($short, $long);
        }
        list($short, $long) = $this->exact['months'];
        $llc = self::lower($name);

        $order = $format === 'MMM' ? array($short, $long) : array($long, $short);
        if ($strict) {
            $order = array($order[0]);
        }
        foreach ($order as $list) {
            $ii = array_search($llc, $list, true);
            if ($ii !== false) {
                return $ii;
            }
        }

        return null;
    }

    /** Index (0-11) of a month name, or null. */
    public function monthsParse($monthName, $format = null, $strict = false)
    {
        if ($this->monthsParseExact) {
            return $this->monthsExact($monthName, $format, $strict);
        }

        if (!$this->monthsParse) {
            $this->monthsParse = array();
            $this->longMonthsParse = array();
            $this->shortMonthsParse = array();
        }

        for ($i = 0; $i < 12; $i++) {
            $mom = self::utcMonth($i);
            if ($strict && !isset($this->longMonthsParse[$i])) {
                $this->longMonthsParse[$i] = '~^' . self::stripDot($this->months($mom, '')) . '$~iu';
                $this->shortMonthsParse[$i] = '~^' . self::stripDot($this->monthsShort($mom, '')) . '$~iu';
            }
            if (!$strict && !isset($this->monthsParse[$i])) {
                $this->monthsParse[$i] = '~' . self::stripDot('^' . $this->months($mom, '') . '|^' . $this->monthsShort($mom, '')) . '~iu';
            }
            if ($strict && $format === 'MMMM' && self::jsMatch($this->longMonthsParse[$i], $monthName)) {
                return $i;
            } elseif ($strict && $format === 'MMM' && self::jsMatch($this->shortMonthsParse[$i], $monthName)) {
                return $i;
            } elseif (!$strict && self::jsMatch($this->monthsParse[$i], $monthName)) {
                return $i;
            }
        }

        return null;
    }

    /**
     * preg_match() with JavaScript's /i semantics for three letters: a case-insensitive JS regex never
     * lets a non-ASCII letter match an ASCII one, but the PCRE bundled with PHP 5.3 does for dotless i
     * (U+0131 ~ I), long s (U+017F ~ S) and the Kelvin sign (U+212A ~ K). There, a match that only worked
     * through such a fold is rejected; newer PCRE doesn't need this.
     */
    protected static function jsMatch($pattern, $subject)
    {
        if (!preg_match($pattern, $subject)) {
            return false;
        }
        if (PHP_VERSION_ID >= 50400 && !self::$oldPcre) {
            return true;
        }
        foreach (array("\xC4\xB1", "\xC5\xBF", "\xE2\x84\xAA") as $char) {
            if (strpos($pattern, $char) !== false && strpos($subject, $char) === false
                && !preg_match(str_replace($char, '(?!)', $pattern), $subject)) {
                return false;
            }
        }

        return true;
    }

    /** JS String.replace('.', '') with a string pattern: the first dot only */
    protected static function stripDot($s)
    {
        return preg_replace('~\.~', '', $s, 1);
    }

    protected function computeMonthsParse()
    {
        $short = array();
        $long = array();
        $mixed = array();
        for ($i = 0; $i < 12; $i++) {
            $mom = self::utcMonth($i);
            $s = self::regexEscape($this->monthsShort($mom, ''));
            $l = self::regexEscape($this->months($mom, ''));
            $short[] = $s;
            $long[] = $l;
            $mixed[] = $l;
            $mixed[] = $s;
        }
        $short = self::sortByLengthDesc($short);
        $long = self::sortByLengthDesc($long);
        $mixed = self::sortByLengthDesc($mixed);

        $this->monthsRegex = '~^(' . implode('|', $mixed) . ')~iu';
        $this->monthsShortRegex = $this->monthsRegex;
        $this->monthsStrictRegex = '~^(' . implode('|', $long) . ')~iu';
        $this->monthsShortStrictRegex = '~^(' . implode('|', $short) . ')~iu';
    }

    public function monthsRegex($isStrict = false)
    {
        if ($this->monthsParseExact) {
            if (!isset($this->exact['monthsRegex'])) {
                $this->computeMonthsParse();
                $this->exact['monthsRegex'] = true;
            }

            return $isStrict ? $this->monthsStrictRegex : $this->monthsRegex;
        }
        if ($this->monthsRegex === null) {
            $this->monthsRegex = self::MATCH_WORD;
        }

        return $this->monthsStrictRegex && $isStrict ? $this->monthsStrictRegex : $this->monthsRegex;
    }

    public function monthsShortRegex($isStrict = false)
    {
        if ($this->monthsParseExact) {
            if (!isset($this->exact['monthsRegex'])) {
                $this->computeMonthsParse();
                $this->exact['monthsRegex'] = true;
            }

            return $isStrict ? $this->monthsShortStrictRegex : $this->monthsShortRegex;
        }
        if ($this->monthsShortRegex === null) {
            $this->monthsShortRegex = self::MATCH_WORD;
        }

        return $this->monthsShortStrictRegex && $isStrict ? $this->monthsShortStrictRegex : $this->monthsShortRegex;
    }

    // ------------------------------------------------------------------ weekdays

    protected function shiftWeekdays(array $ws, $n)
    {
        return array_merge(array_slice($ws, $n, 7 - $n), array_slice($ws, 0, $n));
    }

    /**
     * weekdays(): the list; weekdays(true): the list starting at the locale's first day of the
     * week; weekdays($moment, $format): one name.
     */
    public function weekdays($m = null, $format = '')
    {
        $custom = $this->call('weekdays', array($m, $format));
        if ($custom !== null) {
            return $custom;
        }

        $list = $this->weekdays;
        if (self::isObject($list)) {
            $useFormat = $m !== null && $m !== true && isset($list['isFormat']) && preg_match($list['isFormat'], (string) $format);
            $list = $list[$useFormat ? 'format' : 'standalone'];
        }
        if ($m === true) {
            return $this->shiftWeekdays($list, $this->week['dow']);
        }

        return $m !== null ? $list[self::dayIndex($m)] : $list;
    }

    public function weekdaysShort($m = null)
    {
        $custom = $this->call('weekdaysShort', array($m));
        if ($custom !== null) {
            return $custom;
        }
        if ($m === true) {
            return $this->shiftWeekdays($this->weekdaysShort, $this->week['dow']);
        }

        return $m !== null ? $this->weekdaysShort[self::dayIndex($m)] : $this->weekdaysShort;
    }

    public function weekdaysMin($m = null)
    {
        $custom = $this->call('weekdaysMin', array($m));
        if ($custom !== null) {
            return $custom;
        }
        if ($m === true) {
            return $this->shiftWeekdays($this->weekdaysMin, $this->week['dow']);
        }

        return $m !== null ? $this->weekdaysMin[self::dayIndex($m)] : $this->weekdaysMin;
    }

    protected function weekdaysExact($name, $format, $strict)
    {
        if (!isset($this->exact['weekdays'])) {
            $min = array();
            $short = array();
            $full = array();
            for ($i = 0; $i < 7; ++$i) {
                $mom = self::utcWeekday($i);
                $min[$i] = self::lower($this->weekdaysMin($mom, ''));
                $short[$i] = self::lower($this->weekdaysShort($mom, ''));
                $full[$i] = self::lower($this->weekdays($mom, ''));
            }
            $this->exact['weekdays'] = array($full, $short, $min);
        }
        list($full, $short, $min) = $this->exact['weekdays'];
        $llc = self::lower($name);

        if ($format === 'dddd') {
            $order = array($full, $short, $min);
        } elseif ($format === 'ddd') {
            $order = array($short, $full, $min);
        } else {
            $order = array($min, $full, $short);
        }
        if ($strict) {
            $order = array($order[0]);
        }
        foreach ($order as $list) {
            $ii = array_search($llc, $list, true);
            if ($ii !== false) {
                return $ii;
            }
        }

        return null;
    }

    /** Index (0-6, 0 = Sunday) of a weekday name, or null. */
    public function weekdaysParse($weekdayName, $format = null, $strict = false)
    {
        if ($this->weekdaysParseExact) {
            return $this->weekdaysExact($weekdayName, $format, $strict);
        }

        if (!$this->weekdaysParse) {
            $this->weekdaysParse = array();
            $this->minWeekdaysParse = array();
            $this->shortWeekdaysParse = array();
            $this->fullWeekdaysParse = array();
        }

        for ($i = 0; $i < 7; $i++) {
            $mom = self::utcWeekday($i);
            if ($strict && !isset($this->fullWeekdaysParse[$i])) {
                $this->fullWeekdaysParse[$i] = '~^' . self::dotOptional($this->weekdays($mom, '')) . '$~iu';
                $this->shortWeekdaysParse[$i] = '~^' . self::dotOptional($this->weekdaysShort($mom, '')) . '$~iu';
                $this->minWeekdaysParse[$i] = '~^' . self::dotOptional($this->weekdaysMin($mom, '')) . '$~iu';
            }
            if (!isset($this->weekdaysParse[$i])) {
                $regex = '^' . $this->weekdays($mom, '') . '|^' . $this->weekdaysShort($mom, '') . '|^' . $this->weekdaysMin($mom, '');
                $this->weekdaysParse[$i] = '~' . self::stripDot($regex) . '~iu';
            }
            if ($strict && $format === 'dddd' && self::jsMatch($this->fullWeekdaysParse[$i], $weekdayName)) {
                return $i;
            } elseif ($strict && $format === 'ddd' && self::jsMatch($this->shortWeekdaysParse[$i], $weekdayName)) {
                return $i;
            } elseif ($strict && $format === 'dd' && self::jsMatch($this->minWeekdaysParse[$i], $weekdayName)) {
                return $i;
            } elseif (!$strict && self::jsMatch($this->weekdaysParse[$i], $weekdayName)) {
                return $i;
            }
        }

        return null;
    }

    /** JS String.replace('.', '\\.?') with a string pattern: the first dot only */
    protected static function dotOptional($s)
    {
        return preg_replace('~\.~', '\\.?', $s, 1);
    }

    protected function computeWeekdaysParse()
    {
        $min = array();
        $short = array();
        $long = array();
        $mixed = array();
        for ($i = 0; $i < 7; $i++) {
            $mom = self::utcWeekday($i);
            $minp = self::regexEscape($this->weekdaysMin($mom, ''));
            $shortp = self::regexEscape($this->weekdaysShort($mom, ''));
            $longp = self::regexEscape($this->weekdays($mom, ''));
            $min[] = $minp;
            $short[] = $shortp;
            $long[] = $longp;
            $mixed[] = $minp;
            $mixed[] = $shortp;
            $mixed[] = $longp;
        }
        $min = self::sortByLengthDesc($min);
        $short = self::sortByLengthDesc($short);
        $long = self::sortByLengthDesc($long);
        $mixed = self::sortByLengthDesc($mixed);

        $this->weekdaysRegex = '~^(' . implode('|', $mixed) . ')~iu';
        $this->weekdaysShortRegex = $this->weekdaysRegex;
        $this->weekdaysMinRegex = $this->weekdaysRegex;
        $this->weekdaysStrictRegex = '~^(' . implode('|', $long) . ')~iu';
        $this->weekdaysShortStrictRegex = '~^(' . implode('|', $short) . ')~iu';
        $this->weekdaysMinStrictRegex = '~^(' . implode('|', $min) . ')~iu';
    }

    protected function weekdaysRegexFor($loose, $strictName, $isStrict)
    {
        if ($this->weekdaysParseExact) {
            if (!isset($this->exact['weekdaysRegex'])) {
                $this->computeWeekdaysParse();
                $this->exact['weekdaysRegex'] = true;
            }

            return $isStrict ? $this->$strictName : $this->$loose;
        }
        if ($this->$loose === null) {
            $this->$loose = self::MATCH_WORD;
        }

        return $this->$strictName && $isStrict ? $this->$strictName : $this->$loose;
    }

    public function weekdaysRegex($isStrict = false)
    {
        return $this->weekdaysRegexFor('weekdaysRegex', 'weekdaysStrictRegex', $isStrict);
    }

    public function weekdaysShortRegex($isStrict = false)
    {
        return $this->weekdaysRegexFor('weekdaysShortRegex', 'weekdaysShortStrictRegex', $isStrict);
    }

    public function weekdaysMinRegex($isStrict = false)
    {
        return $this->weekdaysRegexFor('weekdaysMinRegex', 'weekdaysMinStrictRegex', $isStrict);
    }

    // ------------------------------------------------------------------ formats, calendar, relative time

    public function longDateFormat($key)
    {
        if (isset($this->longDateFormat[$key])) {
            return $this->longDateFormat[$key];
        }
        $upper = strtoupper($key);
        if (!isset($this->longDateFormat[$upper])) {
            return null;
        }

        preg_match_all(Formatter::TOKENS, $this->longDateFormat[$upper], $matches);
        $out = '';
        foreach ($matches[0] as $token) {
            $out .= in_array($token, array('MMMM', 'MM', 'DD', 'dddd')) ? substr($token, 1) : $token;
        }

        // moment caches the derived format in the settings object itself, so a later updateLocale()/defineLocale()
        // that starts from this locale's settings sees it (and keeps the old derivation if "L"/"LL" changes)
        if (isset($this->config['longDateFormat']) && is_array($this->config['longDateFormat'])) {
            $this->config['longDateFormat'][$key] = $out;
        }

        return $this->longDateFormat[$key] = $out;
    }

    /**
     * The calendar format for $key (sameDay|nextDay|nextWeek|lastDay|lastWeek|sameElse).
     * A calendar entry may be a callable taking ($now, $mom), as moment's functions do.
     */
    public function calendar($key, $mom = null, $now = null)
    {
        $output = isset($this->calendar[$key]) ? $this->calendar[$key] : $this->calendar['sameElse'];

        return is_object($output) && is_callable($output) ? call_user_func($output, $now, $mom) : $output;
    }

    /**
     * Text for a relative-time unit ($key: s ss m mm h hh d dd w ww M MM y yy). Entries may be
     * callables taking ($number, $withoutSuffix, $key, $isFuture).
     */
    public function relativeTime($number, $withoutSuffix, $key, $isFuture)
    {
        $output = $this->relativeTime[$key];
        if (is_object($output) && is_callable($output)) {
            return call_user_func($output, $number, $withoutSuffix, $key, $isFuture);
        }

        return preg_replace('/%d/i', is_float($number) ? Duration::jsNumber($number) : $number, $output, 1);
    }

    public function pastFuture($diff, $output)
    {
        $format = $this->relativeTime[$diff > 0 ? 'future' : 'past'];
        if (is_object($format) && is_callable($format)) {
            return call_user_func($format, $output);
        }

        return preg_replace('/%s/i', $output, $format, 1);
    }

    // ------------------------------------------------------------------ ordinals, meridiem, misc

    /** The ordinal of $number for the format token $token ('D', 'M', 'w', ...). */
    public function ordinal($number, $token = '')
    {
        $custom = $this->call('ordinal', array($number, $token));
        if ($custom !== null) {
            return $custom;
        }

        return str_replace('%d', $number, $this->ordinal);
    }

    public function meridiem($hours, $minutes, $isLower)
    {
        $custom = $this->call('meridiem', array($hours, $minutes, $isLower));
        if ($custom !== null) {
            return $custom;
        }
        if ($hours > 11) {
            return $isLower ? 'pm' : 'PM';
        }

        return $isLower ? 'am' : 'AM';
    }

    public function isPM($input)
    {
        $custom = $this->call('isPM', array($input));
        if ($custom !== null) {
            return $custom;
        }

        return strtolower(substr($input . '', 0, 1)) === 'p';
    }

    /** Locales with word-like meridiems return the 24h hour here; null means "use isPM()". */
    public function meridiemHour($hour, $meridiem)
    {
        return $this->call('meridiemHour', array($hour, $meridiem));
    }

    /** Whether this locale defines meridiemHour() (so the parser uses it instead of isPM()). */
    public function hasMeridiemHour()
    {
        if (isset($this->functions['meridiemHour'])) {
            return true;
        }
        $method = new \ReflectionMethod($this, 'meridiemHour');

        return $method->getDeclaringClass()->getName() !== __CLASS__;
    }

    public function meridiemParse()
    {
        return $this->meridiemParse;
    }

    public function dayOfMonthOrdinalParse($isStrict = true)
    {
        return $isStrict ? $this->dayOfMonthOrdinalParse : $this->dayOfMonthOrdinalParseLenient;
    }

    /** moment's locale.week(mom): the locale week number of a moment */
    public function week($mom = null)
    {
        if ($mom === null) {
            return $this->week;
        }

        return $mom->week();
    }

    public function firstDayOfWeek()
    {
        return $this->week['dow'];
    }

    public function firstDayOfYear()
    {
        return $this->week['doy'];
    }

    public function invalidDate()
    {
        return $this->invalidDate;
    }

    public function preparse($string)
    {
        $custom = $this->call('preparse', array($string));

        return $custom !== null ? $custom : $string;
    }

    public function postformat($string)
    {
        $custom = $this->call('postformat', array($string));

        return $custom !== null ? $custom : $string;
    }

    // ------------------------------------------------------------------ eras

    /** The locale's eras (English's when it has none), with since/until as ms timestamps. */
    public function eras($m = null, $format = null)
    {
        if ($this->eras === null) {
            return Registry::get('en')->eras();
        }
        if (isset($this->erasCache['eras'])) {
            return $this->erasCache['eras'];
        }
        $eras = $this->eras;
        foreach ($eras as $i => $era) {
            $eras[$i]['since'] = self::eraTime($era['since']);
            $eras[$i]['until'] = array_key_exists('until', $era) ? self::eraTime($era['until']) : INF;
        }

        return $this->erasCache['eras'] = $eras;
    }

    /** since/until: a 'YYYY-MM-DD' string (start of that day, UTC), or INF / -INF / null (= INF) */
    protected static function eraTime($value)
    {
        if (is_string($value)) {
            return Moment::createUtc($value)->startOf('day')->valueOf();
        }

        return $value === null ? INF : $value;
    }

    public function erasParse($eraName, $format, $strict)
    {
        $eraName = strtoupper($eraName);
        foreach ($this->eras() as $era) {
            $name = strtoupper($era['name']);
            $abbr = strtoupper($era['abbr']);
            $narrow = strtoupper($era['narrow']);

            if ($strict) {
                switch ($format) {
                    case 'N':
                    case 'NN':
                    case 'NNN':
                        if ($abbr === $eraName) {
                            return $era;
                        }
                        break;
                    case 'NNNN':
                        if ($name === $eraName) {
                            return $era;
                        }
                        break;
                    case 'NNNNN':
                        if ($narrow === $eraName) {
                            return $era;
                        }
                        break;
                }
            } elseif (in_array($eraName, array($name, $abbr, $narrow), true)) {
                return $era;
            }
        }

        return null;
    }

    public function erasConvertYear($era, $year = null)
    {
        $dir = $era['since'] <= $era['until'] ? 1 : -1;
        $sinceYear = Moment::createUtc($era['since'])->year();
        if ($year === null) {
            return $sinceYear;
        }

        return $sinceYear + ($year - $era['offset']) * $dir;
    }

    protected function computeErasParse()
    {
        $abbr = array();
        $name = array();
        $narrow = array();
        $mixed = array();
        foreach ($this->eras() as $era) {
            $n = self::regexEscape($era['name']);
            $a = self::regexEscape($era['abbr']);
            $r = self::regexEscape($era['narrow']);
            $name[] = $n;
            $abbr[] = $a;
            $narrow[] = $r;
            array_push($mixed, $n, $a, $r);
        }
        $this->erasCache['regex'] = '~^(' . implode('|', $mixed) . ')~iu';
        $this->erasCache['name'] = '~^(' . implode('|', $name) . ')~iu';
        $this->erasCache['abbr'] = '~^(' . implode('|', $abbr) . ')~iu';
        $this->erasCache['narrow'] = '~^(' . implode('|', $narrow) . ')~iu';
    }

    protected function erasRegexFor($kind, $isStrict)
    {
        if (!isset($this->erasCache['regex'])) {
            $this->computeErasParse();
        }

        return $isStrict ? $this->erasCache[$kind] : $this->erasCache['regex'];
    }

    public function erasNameRegex($isStrict = false)
    {
        return $this->erasRegexFor('name', $isStrict);
    }

    public function erasAbbrRegex($isStrict = false)
    {
        return $this->erasRegexFor('abbr', $isStrict);
    }

    public function erasNarrowRegex($isStrict = false)
    {
        return $this->erasRegexFor('narrow', $isStrict);
    }

    /** Regex matching an era year ordinal ("yo"), or null for plain digits. */
    public function eraYearOrdinalRegex()
    {
        return $this->eraYearOrdinalRegex;
    }

    /** Year from an era-year ordinal match; locales with their own ordinals override this. */
    public function eraYearOrdinalParse($input, $match)
    {
        $custom = $this->call('eraYearOrdinalParse', array($input, $match));

        return $custom !== null ? $custom : (int) $input;
    }
}
