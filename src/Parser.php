<?php

namespace Wilkques\Moment;

use Wilkques\Moment\Locale\Locale;
use Wilkques\Moment\Parse\V8DateParser;

/**
 * String parsing, ported from moment's create/*: ISO 8601, RFC 2822, ASP.NET JSON dates
 * and custom format strings (strict or not). Unlike moment there is no fallback to the
 * host's Date parser: a string in none of these formats gives an invalid moment.
 */
class Parser
{
    const ISO_8601 = 'ISO_8601';
    const RFC_2822 = 'RFC_2822';

    const EXTENDED_ISO = '/^\s*((?:[+-]\d{6}|\d{4})-(?:\d\d-\d\d|W\d\d-\d|W\d\d|\d\d\d|\d\d))(?:(T| )(\d\d(?::\d\d(?::\d\d(?:[.,]\d+)?)?)?)([+-]\d\d(?::?\d\d)?|\s*Z)?)?$/';
    const BASIC_ISO = '/^\s*((?:[+-]\d{6}|\d{4})(?:\d\d\d\d|W\d\d\d|W\d\d|\d\d\d|\d\d|))(?:(T| )(\d\d(?:\d\d(?:\d\d(?:[.,]\d+)?)?)?)([+-]\d\d(?::?\d\d)?|\s*Z)?)?$/';
    const RFC2822 = '/^(?:(Mon|Tue|Wed|Thu|Fri|Sat|Sun),?\s)?(\d{1,2})\s(Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)\s(\d{2,4})\s(\d\d):(\d\d)(?::(\d\d))?\s(?:(UT|GMT|[ECMP][SD]T)|([Zz])|([+-]\d{4}))$/';


    protected static $isoDates = array(
        array('YYYYYY-MM-DD', '/[+-]\d{6}-\d\d-\d\d/', true),
        array('YYYY-MM-DD', '/\d{4}-\d\d-\d\d/', true),
        array('GGGG-[W]WW-E', '/\d{4}-W\d\d-\d/', true),
        array('GGGG-[W]WW', '/\d{4}-W\d\d/', false),
        array('YYYY-DDD', '/\d{4}-\d{3}/', true),
        array('YYYY-MM', '/\d{4}-\d\d/', false),
        array('YYYYYYMMDD', '/[+-]\d{10}/', true),
        array('YYYYMMDD', '/\d{8}/', true),
        array('GGGG[W]WWE', '/\d{4}W\d{3}/', true),
        array('GGGG[W]WW', '/\d{4}W\d{2}/', false),
        array('YYYYDDD', '/\d{7}/', true),
        array('YYYYMM', '/\d{6}/', false),
        array('YYYY', '/\d{4}/', false),
    );

    protected static $isoTimes = array(
        array('HH:mm:ss.SSSS', '/\d\d:\d\d:\d\d\.\d+/'),
        array('HH:mm:ss,SSSS', '/\d\d:\d\d:\d\d,\d+/'),
        array('HH:mm:ss', '/\d\d:\d\d:\d\d/'),
        array('HH:mm', '/\d\d:\d\d/'),
        array('HHmmss.SSSS', '/\d\d\d\d\d\d\.\d+/'),
        array('HHmmss,SSSS', '/\d\d\d\d\d\d,\d+/'),
        array('HHmmss', '/\d\d\d\d\d\d/'),
        array('HHmm', '/\d\d\d\d/'),
        array('HH', '/\d\d/'),
    );

    protected static $obsOffsets = array(
        'UT' => 0, 'GMT' => 0, 'EDT' => -240, 'EST' => -300, 'CDT' => -300,
        'CST' => -360, 'MDT' => -360, 'MST' => -420, 'PDT' => -420, 'PST' => -480,
    );

    /** tokens that have a format function in moment (so they count as "unused" when missing) */
    protected static $formatTokens = array(
        'M', 'MM', 'Mo', 'MMM', 'MMMM', 'D', 'DD', 'Do', 'DDD', 'DDDD', 'DDDo', 'd', 'do', 'dd', 'ddd', 'dddd',
        'e', 'E', 'w', 'ww', 'wo', 'W', 'WW', 'Wo', 'Y', 'YY', 'YYYY', 'YYYYY', 'YYYYYY', 'gg', 'gggg', 'ggggg',
        'GG', 'GGGG', 'GGGGG', 'Q', 'Qo', 'H', 'HH', 'h', 'hh', 'k', 'kk', 'hmm', 'hmmss', 'Hmm', 'Hmmss', 'a', 'A',
        'm', 'mm', 's', 'ss', 'S', 'SS', 'SSS', 'SSSS', 'SSSSS', 'SSSSSS', 'SSSSSSS', 'SSSSSSSS', 'SSSSSSSSS',
        'X', 'x', 'Z', 'ZZ', 'z', 'zz', 'N', 'NN', 'NNN', 'NNNN', 'NNNNN', 'y', 'yy', 'yyy', 'yyyy', 'yo',
    );

    /** @var array|null moment's parsing flags of the last parse() call (see Moment::parsingFlags()) */
    public static $flags;

    /** @var array|null moment's config._a of the last parse() call: the parsed date parts */
    public static $parsedArray;

    /** @var string|null the format string actually used (moment's config._f after ISO / multi-format parsing) */
    public static $format;

    /**
     * @param Moment $m used for the locale, strictness, current time and zone
     * @return array|null array('ms' => int) for an absolute instant, array('wall' => int, 'tzm' => int|null)
     *                    for wall-clock ms (UTC-style) plus an optional parsed offset in minutes; null if invalid
     */
    public static function parse(Moment $m, $string, $format)
    {
        $locale = $m->getLocaleData();
        $strict = $m->isStrict();
        self::$flags = null;
        self::$parsedArray = null;
        self::$format = null;

        if ($string === '' && $format === null) {
            return null;
        }
        $string = $locale->preparse($string);

        if ($format === null) {
            return self::fromString($string, $locale, $strict, $m->currentWall(), $m->isUseUtc());
        }
        if (is_array($format)) {
            return self::fromStringAndArray($string, $format, $locale, $strict, $m->currentWall());
        }

        return self::fromStringAndFormat($string, $format, $locale, $strict, $m->currentWall());
    }

    // ------------------------------------------------------------------ string without format

    /**
     * Like moment: ASP.NET JSON date, ISO 8601, RFC 2822, then (unless strict) whatever
     * JavaScript's `new Date(string)` would make of it. A string that looks like ISO 8601 or
     * RFC 2822 but is invalid is final: it does not fall through to the next parser.
     */
    protected static function fromString($string, Locale $locale, $strict, $nowWall, $useUtc)
    {
        if (preg_match('/^\/?Date\((-?\d+)/i', $string, $match)) {
            return array('ms' => Calendar::int(round((float) $match[1])));
        }
        $result = self::fromIso($string, $locale, $strict, $nowWall);
        if ($result === false) {
            return null;
        }
        if ($result !== null) {
            return $result;
        }
        $result = self::fromRfc2822($string);
        if ($result === false) {
            return null;
        }
        if ($result !== null) {
            return $result;
        }
        if ($strict) {
            return null;
        }

        if (Moment::$createFromInputFallback !== null) {
            return call_user_func(Moment::$createFromInputFallback, $string, $useUtc);
        }

        // moment appends " UTC" for utc()/tz() inputs so the host parser reads them as UTC
        return V8DateParser::parse($string . ($useUtc ? ' UTC' : ''));
    }

    /** @return array|null|false the parse, null if the text isn't ISO 8601, false if it is but invalid */
    protected static function fromIso($string, Locale $locale, $strict, $nowWall)
    {
        if (!preg_match(self::EXTENDED_ISO, $string, $match) && !preg_match(self::BASIC_ISO, $string, $match)) {
            return null;
        }
        $dateFormat = null;
        $allowTime = true;
        foreach (self::$isoDates as $row) {
            if (preg_match($row[1], $match[1])) {
                $dateFormat = $row[0];
                $allowTime = $row[2];
                break;
            }
        }
        if ($dateFormat === null) {
            return null;
        }
        $timeFormat = null;
        if (isset($match[3]) && $match[3] !== '') {
            foreach (self::$isoTimes as $row) {
                if (preg_match($row[1], $match[3])) {
                    $timeFormat = (isset($match[2]) && $match[2] !== '' ? $match[2] : ' ') . $row[0];
                    break;
                }
            }
            if ($timeFormat === null || !$allowTime) {
                return null;
            }
        }
        $tzFormat = '';
        if (isset($match[4]) && $match[4] !== '') {
            if (!preg_match('/Z|[+-]\d\d(?::?\d\d)?/', $match[4])) {
                return null;
            }
            $tzFormat = 'Z';
        }

        $r = self::run($string, $dateFormat . $timeFormat . $tzFormat, $locale, $strict, $nowWall);
        self::publish($r);
        self::$flags['iso'] = true;

        return $r['valid'] ? $r['result'] : false;
    }

    /** @return array|null|false the parse, null if the text isn't RFC 2822, false if it is but invalid */
    protected static function fromRfc2822($string)
    {
        $s = preg_replace('/\([^()]*\)|[\n\t]/', ' ', $string);
        $s = trim(preg_replace('/(\s\s+)/', ' ', $s));
        if (!preg_match(self::RFC2822, $s, $m)) {
            return null;
        }
        $months = array('Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec');
        $year = (int) $m[4];
        if ($year <= 49) {
            $year += 2000;
        } elseif ($year <= 999) {
            $year += 1900;
        }
        $month = array_search($m[3], $months);
        $day = (int) $m[2];

        if ($m[1] !== '') {
            $days = array('Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat');
            $fields = Calendar::fields(Calendar::utcMs($year, $month, $day));
            if (array_search($m[1], $days) !== $fields[7]) {
                self::$flags = Moment::defaultFlags();
                self::$flags['weekdayMismatch'] = true;

                return false; // moment flags a weekday mismatch, which makes the date invalid for good
            }
        }

        if (isset($m[8]) && $m[8] !== '') {
            $tzm = self::$obsOffsets[$m[8]];
        } elseif (isset($m[9]) && $m[9] !== '') {
            $tzm = 0;
        } else {
            $hm = (int) $m[10];
            $mm = $hm % 100;
            $tzm = (($hm - $mm) / 100) * 60 + $mm;
        }
        $sec = isset($m[7]) && $m[7] !== '' ? (int) $m[7] : 0;
        $wall = Calendar::utcMs($year, $month, $day, (int) $m[5], (int) $m[6], $sec);

        self::$parsedArray = array($year, $month, $day, (int) $m[5], (int) $m[6]);
        if (isset($m[7]) && $m[7] !== '') {
            self::$parsedArray[] = $sec;
        }
        self::$flags = Moment::defaultFlags();
        self::$flags['rfc2822'] = true;
        self::$flags['overflow'] = self::overflow(array('a' => array_pad(self::$parsedArray, 7, 0), 'overflowDoy' => false, 'overflowWeeks' => false, 'overflowWeekday' => false));

        return array('ms' => $wall - $tzm * 60000);
    }

    // ------------------------------------------------------------------ format strings

    protected static function fromStringAndArray($string, array $formats, Locale $locale, $strict, $nowWall)
    {
        if (!$formats) {
            return null;
        }
        $best = null;
        $bestScore = null;
        $bestValid = false;
        foreach ($formats as $format) {
            $r = self::run($string, $format, $locale, $strict, $nowWall);
            $score = $r['flags']['charsLeftOver'] + count($r['flags']['unusedTokens']) * 10;
            $valid = $r['valid'];

            if (!$bestValid) {
                if ($bestScore === null || $score < $bestScore || $valid) {
                    $bestScore = $score;
                    $best = $r;
                    $bestValid = $valid;
                }
            } elseif ($score < $bestScore) {
                $bestScore = $score;
                $best = $r;
            }
        }

        if (empty($best['jsflags'])) {
            $best['jsflags'] = Moment::defaultFlags();
        }
        self::publish($best);

        return $best['valid'] ? $best['result'] : null;
    }

    /** remember the flags and parsed parts of a run() for Moment::parsingFlags() & co */
    protected static function publish(array $run)
    {
        self::$flags = isset($run['jsflags']) ? $run['jsflags'] : null;
        self::$parsedArray = isset($run['a']) ? $run['a'] : null;
        self::$format = isset($run['f']) ? $run['f'] : null;
    }

    protected static function fromStringAndFormat($string, $format, Locale $locale, $strict, $nowWall)
    {
        $r = self::run($string, $format, $locale, $strict, $nowWall);
        self::publish($r);

        return $r['valid'] ? $r['result'] : null;
    }

    protected static function newFlags()
    {
        return array('empty' => false, 'unusedTokens' => array(), 'unusedInput' => array(), 'charsLeftOver' => 0,
            'bigHour' => null, 'invalidMonth' => false, 'invalidWeekday' => false, 'weekdayMismatch' => false,
            'meridiem' => null, 'parsedDateParts' => array(), 'era' => null, 'invalidEra' => false);
    }

    /** @return array(valid, result, flags) */
    protected static function run($string, $format, Locale $locale, $strict, $nowWall)
    {
        if ($format === self::ISO_8601) {
            $result = self::fromIso($string, $locale, $strict, $nowWall);

            return array('valid' => is_array($result), 'result' => $result, 'flags' => self::newFlags(), 'jsflags' => self::$flags, 'a' => self::$parsedArray);
        }
        if ($format === self::RFC_2822) {
            $result = self::fromRfc2822($string);

            return array('valid' => is_array($result), 'result' => $result, 'flags' => self::newFlags(), 'jsflags' => self::$flags, 'a' => self::$parsedArray);
        }

        $st = array('a' => array(null, null, null, null, null, null, null), 'w' => array(), 'doy' => null,
            'tzm' => null, 'meridiem' => null, 'isPm' => null, 'ms' => null, 'flags' => self::newFlags(),
            'overflowDoy' => false, 'overflowWeeks' => false, 'overflowWeekday' => false, 'locale' => $locale, 'strict' => $strict);
        $flags = &$st['flags'];
        $flags['empty'] = true;

        $input = (string) $string;
        $length = strlen($input);
        $total = 0;

        preg_match_all(Formatter::TOKENS, Formatter::expand($format, $locale), $tm);
        foreach ($tm[0] as $token) {
            $regex = self::tokenRegex($token, $strict, $locale);
            $parsed = null;
            if (preg_match($regex, $input, $mm)) {
                $parsed = $mm[0];
            }
            if ($parsed !== null && $parsed !== '') {
                $pos = strpos($input, $parsed);
                $skipped = substr($input, 0, $pos);
                if ($skipped !== '') {
                    $flags['unusedInput'][] = $skipped;
                }
                $input = substr($input, $pos + strlen($parsed));
                $total += strlen($parsed);
            }
            if (in_array($token, self::$formatTokens, true)) {
                if ($parsed !== null && $parsed !== '') {
                    $flags['empty'] = false;
                } else {
                    $flags['unusedTokens'][] = $token;
                }
                if ($parsed !== null && $parsed !== '') {
                    self::applyToken($token, $parsed, $st);
                }
            } elseif ($strict && ($parsed === null || $parsed === '')) {
                $flags['unusedTokens'][] = $token;
            }
        }

        $flags['charsLeftOver'] = $length - $total;
        if (strlen($input) > 0) {
            $flags['unusedInput'][] = $input;
        }

        if ($st['a'][3] !== null && $st['a'][3] <= 12 && $flags['bigHour'] === true && $st['a'][3] > 0) {
            $flags['bigHour'] = null;
        }
        $flags['parsedDateParts'] = $st['a'];
        $flags['meridiem'] = $st['meridiem'];
        $st['a'][3] = self::meridiemFix($locale, $st['a'][3], $st['meridiem']);

        if ($flags['era'] !== null) {
            $st['a'][0] = $locale->erasConvertYear($flags['era'], $st['a'][0]);
        }

        if ($st['ms'] !== null) {
            $result = array('ms' => $st['ms']);
            $overflow = -1;
        } else {
            $result = self::fromArray($st, $nowWall);
            $overflow = self::overflow($st);
        }

        $parsedParts = false;
        foreach ($flags['parsedDateParts'] as $p) {
            if ($p !== null) {
                $parsedParts = true;
            }
        }
        if ($st['a'][3] !== null && is_float($st['a'][3]) && is_nan($st['a'][3])) {
            $overflow = 3; // NaN hour: JS builds an invalid Date
        }
        $valid = $overflow < 0 && !$flags['empty'] && !$flags['invalidEra'] && !$flags['invalidMonth'] && !$flags['invalidWeekday']
            && !$flags['weekdayMismatch'] && (!$flags['meridiem'] || $parsedParts);
        if ($strict) {
            $valid = $valid && $flags['charsLeftOver'] === 0 && !$flags['unusedTokens'] && $flags['bigHour'] === null;
        }

        $js = Moment::defaultFlags();
        foreach (array('empty', 'unusedTokens', 'unusedInput', 'charsLeftOver', 'parsedDateParts', 'era',
            'meridiem', 'weekdayMismatch') as $k) {
            $js[$k] = $flags[$k];
        }
        $js['invalidMonth'] = $flags['invalidMonth'] ?: null;
        $js['invalidEra'] = $flags['invalidEra'] ?: null;
        $js['overflow'] = $overflow;
        $js['invalidWeekday'] = $flags['invalidWeekday'];
        $js['bigHour'] = $flags['bigHour'];

        return array('valid' => $valid, 'result' => $result, 'flags' => $flags, 'jsflags' => $js, 'f' => $format, 'a' => $st['ms'] !== null ? null : $st['a']);
    }

    protected static function meridiemFix(Locale $locale, $hour, $meridiem)
    {
        if ($meridiem === null) {
            return $hour;
        }
        if ($locale->hasMeridiemHour()) {
            if ($hour === null) {
                // An hour that was never read is `undefined` in JS: a locale that hands it back leaves it unread
                // (a valid date at 00:00), one that does arithmetic on it gets NaN (an invalid date). PHP's null
                // would turn into 0 in arithmetic, so probe with null and NaN to tell the two apart.
                $unread = $locale->meridiemHour(null, $meridiem);
                if ($unread === null) {
                    return null;
                }
                $probe = $locale->meridiemHour(NAN, $meridiem);

                return is_float($probe) && is_nan($probe) ? NAN : $unread;
            }

            return $locale->meridiemHour($hour, $meridiem);
        }
        $isPm = $locale->isPM($meridiem);
        // comparisons with an unread hour are false in JS, so it stays unread
        if ($isPm && $hour !== null && $hour < 12) {
            $hour += 12;
        }
        if (!$isPm && $hour === 12) {
            $hour = 0;
        }

        return $hour;
    }

    protected static function twoDigitYear($input)
    {
        if (Moment::$parseTwoDigitYear !== null) {
            return (int) call_user_func(Moment::$parseTwoDigitYear, $input);
        }

        return (int) $input + ((int) $input > 68 ? 1900 : 2000);
    }

    protected static function applyToken($token, $in, array &$st)
    {
        $a = &$st['a'];
        $f = &$st['flags'];
        $locale = $st['locale'];

        switch ($token) {
            case 'M':
            case 'MM':
                $a[1] = (int) $in - 1;
                break;
            case 'MMM':
            case 'MMMM':
                $month = $locale->monthsParse($in, $token, $st['strict']);
                if ($month !== null) {
                    $a[1] = $month;
                } else {
                    $f['invalidMonth'] = $in;
                }
                break;
            case 'D':
            case 'DD':
                $a[2] = (int) $in;
                break;
            case 'Do':
                preg_match('/\d\d?/', $in, $d);
                $a[2] = (int) $d[0];
                break;
            case 'DDD':
            case 'DDDD':
                $st['doy'] = (int) $in;
                break;
            case 'YYYYY':
            case 'YYYYYY':
            case 'y':
            case 'yy':
            case 'yyy':
            case 'yyyy':
                $a[0] = (int) $in;
                break;
            case 'yo':
                $regex = $locale->eraYearOrdinalRegex();
                $match = $regex ? (preg_match($regex, $in, $mm) ? $mm : null) : null;
                $a[0] = $locale->eraYearOrdinalParse($in, $match);
                break;
            case 'N':
            case 'NN':
            case 'NNN':
            case 'NNNN':
            case 'NNNNN':
                $era = $locale->erasParse($in, $token, $st['strict']);
                if ($era) {
                    $f['era'] = $era;
                } else {
                    $f['invalidEra'] = $in;
                }
                break;
            case 'YYYY':
                $a[0] = strlen($in) === 2 ? self::twoDigitYear($in) : (int) $in;
                break;
            case 'YY':
                $a[0] = self::twoDigitYear($in);
                break;
            case 'Y':
                $a[0] = (int) $in;
                break;
            case 'gggg':
            case 'ggggg':
            case 'GGGG':
            case 'GGGGG':
                $st['w'][substr($token, 0, 2)] = (int) $in;
                break;
            case 'gg':
            case 'GG':
                $st['w'][$token] = self::twoDigitYear($in);
                break;
            case 'w':
            case 'ww':
            case 'W':
            case 'WW':
                $st['w'][$token[0]] = (int) $in;
                break;
            case 'd':
            case 'e':
            case 'E':
                $st['w'][$token] = (int) $in;
                break;
            case 'dd':
            case 'ddd':
            case 'dddd':
                $weekday = $locale->weekdaysParse($in, $token, $st['strict']);
                if ($weekday !== null) {
                    $st['w']['d'] = $weekday;
                } else {
                    $f['invalidWeekday'] = $in;
                }
                break;
            case 'Q':
                $a[1] = ((int) $in - 1) * 3;
                break;
            case 'H':
            case 'HH':
                $a[3] = (int) $in;
                break;
            case 'k':
            case 'kk':
                $a[3] = (int) $in === 24 ? 0 : (int) $in;
                break;
            case 'h':
            case 'hh':
                $a[3] = (int) $in;
                $f['bigHour'] = true;
                break;
            case 'hmm':
                $pos = strlen($in) - 2;
                $a[3] = (int) substr($in, 0, $pos);
                $a[4] = (int) substr($in, $pos);
                $f['bigHour'] = true;
                break;
            case 'hmmss':
                $a[3] = (int) substr($in, 0, strlen($in) - 4);
                $a[4] = (int) substr($in, strlen($in) - 4, 2);
                $a[5] = (int) substr($in, strlen($in) - 2);
                $f['bigHour'] = true;
                break;
            case 'Hmm':
                $pos = strlen($in) - 2;
                $a[3] = (int) substr($in, 0, $pos);
                $a[4] = (int) substr($in, $pos);
                break;
            case 'Hmmss':
                $a[3] = (int) substr($in, 0, strlen($in) - 4);
                $a[4] = (int) substr($in, strlen($in) - 4, 2);
                $a[5] = (int) substr($in, strlen($in) - 2);
                break;
            case 'a':
            case 'A':
                $st['isPm'] = $locale->isPM($in);
                $st['meridiem'] = $in;
                break;
            case 'm':
            case 'mm':
                $a[4] = (int) $in;
                break;
            case 's':
            case 'ss':
                $a[5] = (int) $in;
                break;
            case 'X':
                $st['ms'] = Calendar::trunc(floatval($in) * 1000);
                break;
            case 'x':
                $st['ms'] = Calendar::int(round(floatval($in)));
                break;
            case 'Z':
            case 'ZZ':
                $st['tzm'] = Moment::offsetFromString($in);
                break;
            default:
                if ($token[0] === 'S') {
                    $a[6] = (int) (floatval('0.' . $in) * 1000);
                }
        }
    }

    /** @return array('wall' => ms, 'tzm' => int|null) */
    protected static function fromArray(array &$st, $nowWall)
    {
        $a = &$st['a'];
        $now = Calendar::fields($nowWall);

        if ($st['w'] && $a[2] === null && $a[1] === null) {
            self::dayOfYearFromWeekInfo($st, $nowWall);
        }

        if ($st['doy'] !== null) {
            $year = $a[0] !== null ? $a[0] : $now[0];
            if ($st['doy'] > Calendar::daysInYear($year) || $st['doy'] === 0) {
                $st['overflowDoy'] = true;
            }
            $c = Calendar::civilFromDays(Calendar::daysFromCivil($year, 0, $st['doy']));
            $a[1] = $c[1];
            $a[2] = $c[2];
        }

        for ($i = 0; $i < 3 && $a[$i] === null; ++$i) {
            $a[$i] = $now[$i];
        }
        for (; $i < 7; $i++) {
            $a[$i] = $a[$i] === null ? ($i === 2 ? 1 : 0) : $a[$i];
        }

        $nextDay = false;
        if ($a[3] === 24 && $a[4] === 0 && $a[5] === 0 && $a[6] === 0) {
            $nextDay = true;
            $a[3] = 0;
        }
        $wall = Calendar::utcMs($a[0], $a[1], $a[2], $a[3], $a[4], $a[5], $a[6]);
        if (is_nan($wall)) {
            return array('wall' => $wall, 'tzm' => $st['tzm']);
        }
        if ($nextDay) {
            $a[3] = 24;
            $wall += 86400000;
        }

        $fields = Calendar::fields($wall);
        if (isset($st['w']['d']) && $st['w']['d'] !== $fields[7]) {
            $st['flags']['weekdayMismatch'] = true;
        }

        return array('wall' => $wall, 'tzm' => $st['tzm']);
    }

    protected static function dayOfYearFromWeekInfo(array &$st, $nowWall)
    {
        $w = $st['w'];
        $a = &$st['a'];
        $now = Calendar::fields($nowWall);
        $nowDoy = Calendar::daysFromCivil($now[0], $now[1], $now[2]) - Calendar::daysFromCivil($now[0], 0, 1) + 1;
        $overflowWeekday = false;

        if (isset($w['GG']) || isset($w['W']) || isset($w['E'])) {
            $dow = 1;
            $doy = 4;
            $cur = Calendar::weekOfYear($now[0], $nowDoy, 1, 4);
            $weekYear = isset($w['GG']) ? $w['GG'] : ($a[0] !== null ? $a[0] : $cur[1]);
            $week = isset($w['W']) ? $w['W'] : 1;
            $weekday = isset($w['E']) ? $w['E'] : 1;
            if ($weekday < 1 || $weekday > 7) {
                $overflowWeekday = true;
            }
        } else {
            $dow = $st['locale']->firstDayOfWeek();
            $doy = $st['locale']->firstDayOfYear();
            $cur = Calendar::weekOfYear($now[0], $nowDoy, $dow, $doy);
            $weekYear = isset($w['gg']) ? $w['gg'] : ($a[0] !== null ? $a[0] : $cur[1]);
            $week = isset($w['w']) ? $w['w'] : $cur[0];
            if (isset($w['d'])) {
                $weekday = $w['d'];
                if ($weekday < 0 || $weekday > 6) {
                    $overflowWeekday = true;
                }
            } elseif (isset($w['e'])) {
                $weekday = $w['e'] + $dow;
                if ($w['e'] < 0 || $w['e'] > 6) {
                    $overflowWeekday = true;
                }
            } else {
                $weekday = $dow;
            }
        }

        if ($week < 1 || $week > Calendar::weeksInYear($weekYear, $dow, $doy)) {
            $st['overflowWeeks'] = true;
        } elseif ($overflowWeekday) {
            $st['overflowWeekday'] = true;
        } else {
            $t = Calendar::dayOfYearFromWeeks($weekYear, $week, $weekday, $dow, $doy);
            $a[0] = $t[0];
            $st['doy'] = $t[1];
        }
    }

    /** @return int the overflow index for a list of 7 date parts (Moment::invalidAt()) */
    public static function overflowOf(array $a)
    {
        return self::overflow(array('a' => $a, 'overflowDoy' => false, 'overflowWeeks' => false, 'overflowWeekday' => false));
    }

    /** @return int index of the overflowing part (0..6), 7/8 for week/weekday, -1 when fine */
    protected static function overflow(array $st)
    {
        $a = $st['a'];
        $o = -1;
        if ($a[1] < 0 || $a[1] > 11) {
            $o = 1;
        } elseif ($a[2] < 1 || $a[2] > Calendar::daysInMonth($a[0], $a[1])) {
            $o = 2;
        } elseif ($a[3] < 0 || $a[3] > 24 || ($a[3] === 24 && ($a[4] !== 0 || $a[5] !== 0 || $a[6] !== 0))) {
            $o = 3;
        } elseif ($a[4] < 0 || $a[4] > 59) {
            $o = 4;
        } elseif ($a[5] < 0 || $a[5] > 59) {
            $o = 5;
        } elseif ($a[6] < 0 || $a[6] > 999) {
            $o = 6;
        }
        if ($st['overflowDoy'] && ($o < 0 || $o > 2)) {
            $o = 2;
        }
        if ($st['overflowWeeks'] && $o === -1) {
            $o = 7;
        }
        if ($st['overflowWeekday'] && $o === -1) {
            $o = 8;
        }

        return $o;
    }

    // ------------------------------------------------------------------ token regexes

    protected static function tokenRegex($token, $strict, Locale $locale)
    {
        $re = null;
        switch ($token) {
            case 'M': $re = $strict ? '^[1-9]\d?' : '\d\d?'; break;
            case 'MM': $re = $strict ? '\d\d' : '\d\d?'; break;
            case 'MMM': return $locale->monthsShortRegex($strict);
            case 'MMMM': return $locale->monthsRegex($strict);
            case 'dd': return $locale->weekdaysMinRegex($strict);
            case 'ddd': return $locale->weekdaysShortRegex($strict);
            case 'dddd': return $locale->weekdaysRegex($strict);
            case 'N':
            case 'NN':
            case 'NNN': return $locale->erasAbbrRegex($strict);
            case 'NNNN': return $locale->erasNameRegex($strict);
            case 'NNNNN': return $locale->erasNarrowRegex($strict);
            case 'yo':
                $yo = $locale->eraYearOrdinalRegex();

                return $yo ?: '~\d+~u';
            case 'D': $re = $strict ? '^[1-9]\d?' : '\d\d?'; break;
            case 'DD': $re = $strict ? '\d\d' : '\d\d?'; break;
            case 'Do': return $locale->dayOfMonthOrdinalParse($strict);
            case 'DDD': $re = '\d{1,3}'; break;
            case 'DDDD': $re = '\d{3}'; break;
            case 'd':
            case 'e':
            case 'E': $re = '\d\d?'; break;
            case 'w':
            case 'W': $re = $strict ? '^[1-9]\d?' : '\d\d?'; break;
            case 'ww':
            case 'WW': $re = $strict ? '\d\d' : '\d\d?'; break;
            case 'Y': $re = '[+-]?\d+'; break;
            case 'YY': $re = $strict ? '\d\d' : '\d\d?'; break;
            case 'YYYY': $re = $strict ? '\d{4}' : '\d{1,4}'; break;
            case 'YYYYY':
            case 'YYYYYY': $re = $strict ? '[+-]?\d{6}' : '[+-]?\d{1,6}'; break;
            case 'G':
            case 'g': $re = '[+-]?\d+'; break;
            case 'gg':
            case 'GG': $re = $strict ? '\d\d' : '\d\d?'; break;
            case 'gggg':
            case 'GGGG': $re = $strict ? '\d{4}' : '\d{1,4}'; break;
            case 'ggggg':
            case 'GGGGG': $re = $strict ? '[+-]?\d{6}' : '[+-]?\d{1,6}'; break;
            case 'Q': $re = '\d'; break;
            case 'H': $re = $strict ? '^([1-9]\d|\d)' : '\d\d?'; break;
            case 'h':
            case 'k': $re = $strict ? '^[1-9]\d?' : '\d\d?'; break;
            case 'HH':
            case 'hh':
            case 'kk': $re = $strict ? '\d\d' : '\d\d?'; break;
            case 'hmm':
            case 'Hmm': $re = '\d\d\d\d?'; break;
            case 'hmmss':
            case 'Hmmss': $re = '\d\d\d\d\d\d?'; break;
            case 'a':
            case 'A': return $locale->meridiemParse();
            case 'm':
            case 's': $re = $strict ? '^([1-9]\d|\d)' : '\d\d?'; break;
            case 'mm':
            case 'ss': $re = $strict ? '\d\d' : '\d\d?'; break;
            case 'S': $re = $strict ? '\d' : '\d{1,3}'; break;
            case 'SS': $re = $strict ? '\d\d' : '\d{1,3}'; break;
            case 'SSS': $re = $strict ? '\d{3}' : '\d{1,3}'; break;
            case 'X': $re = '[+-]?\d+(\.\d{1,3})?'; break;
            case 'x': $re = '[+-]?\d+'; break;
            case 'Z':
            case 'ZZ': $re = 'Z|[+-]\d\d(?::?\d\d)?'; break;
            case 'y':
            case 'yy':
            case 'yyy':
            case 'yyyy': $re = '\d+'; break;
        }
        if ($re === null && preg_match('/^S{4,9}$/', $token)) {
            $re = '\d+';
        }
        if ($re !== null) {
            return '~' . $re . '~iu';
        }

        return '~' . preg_quote(self::unescape($token), '~') . '~u';
    }

    protected static function unescape($token)
    {
        $s = preg_replace('/\\\\/', '', $token, 1);

        return preg_replace_callback('/\\\\(\[)|\\\\(\])|\[([^\]\[]*)\]|\\\\(.)/', function ($m) {
            for ($i = 1; $i <= 4; $i++) {
                if (isset($m[$i]) && $m[$i] !== '') {
                    return $m[$i];
                }
            }

            return '';
        }, $s);
    }
}
