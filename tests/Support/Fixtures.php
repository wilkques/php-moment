<?php

namespace Wilkques\Moment\Tests\Support;

use Wilkques\Moment\Calendar;
use Wilkques\Moment\Duration;
use Wilkques\Moment\InvalidDate;
use Wilkques\Moment\Locale\Registry;
use Wilkques\Moment\Moment;
use Wilkques\Moment\Timezone\Tz;

/**
 * Replays tests/fixtures/*.json (generated from the real moment.js by tools/generate-fixtures.js)
 * against this library and returns a list of mismatches (empty when everything agrees).
 */
class Fixtures
{
    /** Kept for callers; zones now come from the bundled moment-timezone data, so every PHP version supports them. */
    public static function zoneSupported($zone)
    {
        return true;
    }

    public static function load($name)
    {
        $name = $name === 'guess-table' ? 'guess-icu' : $name; // the same cases, answered from the bundled table
        $file = dirname(__DIR__) . '/fixtures/' . $name . '.json';
        if (!is_file($file)) {
            $file = 'compress.zlib://' . $file . '.gz'; // the bigger ones are gzipped
        }

        return json_decode(file_get_contents($file), true);
    }

    public static function isoMs($iso)
    {
        preg_match('/^(-?\d+)-(\d\d)-(\d\d)T(\d\d):(\d\d):(\d\d)\.(\d{3})Z$/', $iso, $m);

        return Calendar::utcMs((int) $m[1], $m[2] - 1, (int) $m[3], (int) $m[4], (int) $m[5], (int) $m[6], (int) $m[7]);
    }

    protected static function mk($iso, $zone, $locale = 'en')
    {
        return Moment::createInZone(self::isoMs($iso), $zone)->locale($locale);
    }

    public static function run($suite)
    {
        date_default_timezone_set('UTC');
        Moment::setNow(Calendar::utcMs(2024, 5, 15, 10, 0, 0));
        $failures = array();

        foreach (self::load($suite) as $c) {
            $zone = isset($c['zone']) ? $c['zone'] : 'UTC';
            if (!self::zoneSupported($zone)) {
                continue;
            }
            date_default_timezone_set('UTC');
            $method = str_replace(' ', '', ucwords(str_replace('-', ' ', preg_replace('/^fuzz-big\d+$/', 'fuzz', $suite))));
            $method = lcfirst($method);
            list($got, $want) = self::$method($c);
            if (!self::same($got, $want)) {
                $failures[] = json_encode($c) . "\n  got:  " . json_encode($got) . "\n  want: " . json_encode($want);
            }
        }
        Moment::setNow(null);

        return $failures;
    }

    protected static function format($c)
    {
        return array(self::mk($c['iso'], $c['zone'], $c['locale'])->format($c['format']), $c['expected']);
    }

    protected static function add($c)
    {
        $m = self::mk($c['iso'], $c['zone'])->add($c['amount'], $c['unit']);

        return array($m->toISOString() . ' | ' . $m->format(), $c['expected'] . ' | ' . $c['local']);
    }

    protected static function startEnd($c)
    {
        $s = self::mk($c['iso'], $c['zone'])->startOf($c['unit']);
        $e = self::mk($c['iso'], $c['zone'])->endOf($c['unit']);

        return array($s->format() . ' ' . $e->format() . ' ' . $s->valueOf() . ' ' . $e->valueOf(),
            $c['start'] . ' ' . $c['end'] . ' ' . $c['startMs'] . ' ' . $c['endMs']);
    }

    protected static function diff($c)
    {
        $a = self::mk($c['a'], $c['zone']);
        $b = self::mk($c['b'], $c['zone']);

        return array(
            array($a->diff($b, $c['unit']), self::round($a->diff($b, $c['unit'], true))),
            array($c['expected'], self::round($c['expectedFloat'])),
        );
    }

    protected static function round($float)
    {
        return (float) sprintf('%.8e', $float);
    }

    protected static function parse($c)
    {
        $m = $c['zone'] === 'UTC'
            ? Moment::createUtc($c['input'], $c['format'], $c['locale'], $c['strict'])
            : Moment::createInZone($c['input'], $c['zone'], $c['format'], $c['locale'], $c['strict']);

        return array(
            $m->isValid() ? $m->valueOf() . ' ' . $m->format() : 'invalid',
            $c['valid'] ? $c['ms'] . ' ' . $c['formatted'] : 'invalid',
        );
    }

    /**
     * tz.guess() by offset scoring for a host in each zone, on a host whose ICU names carry no ASCII abbreviation
     * (Node found none, so only offsets decide). Five legacy zones still differ because there Node did find one.
     */
    protected static function guess($c)
    {
        static $legacy = array('CET', 'EET', 'Eire', 'MET', 'WET');
        if (in_array($c['tz'], $legacy)) {
            return array(1, 1);
        }

        return self::guessHost($c, false);
    }

    /** the same with English ICU names: the abbreviation counts, from ext-intl when present, else the bundled table (a few zones differ between ICU releases) */
    protected static function guessIcu($c)
    {
        if ($c['tz'] === 'Eire') {
            return array(1, 1);
        }

        return self::guessHost($c, null);
    }

    /** the same cases again with ext-intl hidden, so the bundled table answers */
    protected static function guessTable($c)
    {
        Tz::$withoutIntl = true;
        try {
            return self::guessIcu($c);
        } catch (\Exception $e) {
            Tz::$withoutIntl = false;
            throw $e;
        }
    }

    protected static function guessHost($c, $useAbbr)
    {
        // guess() probes the HOST's zone data (PHP's own tzdata); an old PHP's copy can disagree with
        // moment-timezone's for zones whose rules changed lately, which would make the answer legitimately differ
        $zone = Tz::zone($c['tz']);
        try {
            $host = new \DateTimeZone($c['tz']);
        } catch (\Exception $e) {
            return array(1, 1); // this PHP's tzdata predates the zone
        }
        for ($t = Calendar::utcMs(2022, 0, 1); $t <= Calendar::utcMs(2028, 0, 1); $t += 15 * 86400000) {
            if (-$zone->utcOffset($t) * 60 !== $host->getOffset(new \DateTime('@' . (int) floor($t / 1000)))) {
                return array(1, 1);
            }
        }
        Tz::$guessByName = false;
        Tz::$guessUseAbbr = $useAbbr;
        date_default_timezone_set($c['tz']);
        $got = Tz::guess(true);
        Tz::$guessByName = true;
        Tz::$guessUseAbbr = null;
        Tz::$withoutIntl = false;
        date_default_timezone_set('UTC');

        return array($got, $c['guess']);
    }


    // ------------------------------------------------------------------ cross-product suite

    protected static function day($m)
    {
        return $m->isValid() ? $m->format('YYYY-MM-DD') : 'invalid';
    }

    /** locale x zone x DST edge: formats, relative time, calendar, arithmetic and parse round trips */
    protected static function crossLocaleZone($c)
    {
        $loc = $c['locale'];
        $got = array();
        foreach ($c['zones'] as $zone) {
            $row = array();
            foreach ($c['instants'] as $ms) {
                $m = Moment::createInZone($ms, $zone)->locale($loc);
                $rt1 = Moment::createInZone($m->format('LLLL'), $zone, 'LLLL', $loc);
                $rt2 = Moment::createInZone($m->format('L LTS Z'), $zone, 'L LTS Z', $loc);
                $row[] = array($m->format('LLLL'), $m->format('llll'), $m->format('dddd Do MMMM YYYY HH:mm:ss.SSS Z z'), $m->format('LTS'),
                    $m->format('Wo gggg Qo'), $m->format('x X'), $m->fromNow(), $m->calendar(), $m->copy()->startOf('day')->format('LLL'),
                    $m->copy()->endOf('week')->format('L LT'), $m->copy()->add(1, 'day')->format('llll'), $m->copy()->add(1, 'month')->format('llll Z'),
                    $m->copy()->subtract(1, 'hour')->format('LTS Z'), $rt1->isValid() ? $rt1->valueOf() : null, $rt2->isValid() ? $rt2->valueOf() : null);
            }
            $got[] = $row;
        }

        return array($got, $c['expected']);
    }

    protected static function crossWeeks($c)
    {
        $got = array();
        foreach ($c['dates'] as $d) {
            $b = Moment::createUtc($d)->locale($c['locale']);
            $got[] = array($b->week(), $b->weekYear(), $b->weekday(), $b->isoWeek(), $b->isoWeekYear(), $b->weeksInYear(), $b->weeksInWeekYear(),
                $b->isoWeeksInYear(), $b->isoWeeksInISOWeekYear(), $b->dayOfYear(), $b->quarter(),
                self::day($b->copy()->startOf('week')), self::day($b->copy()->endOf('week')), self::day($b->copy()->startOf('isoWeek')),
                self::day($b->copy()->week(1)), self::day($b->copy()->week(2)), self::day($b->copy()->week(52)), self::day($b->copy()->week(53)),
                self::day($b->copy()->weekYear(2020)), self::day($b->copy()->weekYear(2021)), self::day($b->copy()->weekday(0)), self::day($b->copy()->weekday(6)),
                self::day($b->copy()->isoWeekday(1)), self::day($b->copy()->isoWeekday(7)), self::day($b->copy()->isoWeek(1)), self::day($b->copy()->isoWeek(53)),
                self::day($b->copy()->isoWeekYear(2026)), self::day($b->copy()->dayOfYear(1)), self::day($b->copy()->dayOfYear(366)),
                self::day($b->copy()->day(0)), self::day($b->copy()->day('Monday')));
        }

        return array($got, $c['expected']);
    }

    protected static function crossSetget($c)
    {
        $f = 'YYYY-MM-DDTHH:mm:ss.SSSZ';
        $got = array();
        foreach ($c['units'] as $u) {
            $set = array();
            foreach ($c['values'] as $v) {
                $m = Moment::createInZone($c['base'], $c['zone']);
                $m->set($u, $v);
                $set[] = $m->format($f);
            }
            $got[$u] = array('get' => Moment::createInZone($c['base'], $c['zone'])->get($u), 'set' => $set);
        }

        return array(self::normalize($got), self::normalize($c['expected']));
    }

    protected static function crossSetObject($c)
    {
        $m = Moment::createInZone($c['base'], $c['zone']);
        $m->set($c['obj']);

        return array($m->format('YYYY-MM-DDTHH:mm:ss.SSSZ'), $c['expected']);
    }

    protected static function crossAddDiff($c)
    {
        $f = 'YYYY-MM-DDTHH:mm:ss.SSSZ';
        $zone = $c['zone'];
        $got = array();
        foreach ($c['bases'] as $ms) {
            $base = Moment::createInZone($ms, $zone);
            $add = array();
            $sub = array();
            foreach ($c['addUnits'] as $u) {
                $a = array();
                $b = array();
                foreach ($c['amounts'] as $amount) {
                    $a[] = $base->copy()->add($amount, $u)->format($f);
                    $b[] = $base->copy()->subtract($amount, $u)->format($f);
                }
                $add[] = $a;
                $sub[] = $b;
            }
            $start = array();
            foreach (array('year', 'quarter', 'month', 'week', 'isoWeek', 'day', 'date', 'hour', 'minute', 'second', 'millisecond', 'y', 'w', 'd') as $u) {
                $start[] = array($base->copy()->startOf($u)->format($f), $base->copy()->endOf($u)->format($f));
            }
            $diff = array();
            foreach ($c['bases'] as $other) {
                $row = array();
                foreach (array('year', 'quarter', 'month', 'week', 'day', 'hour', 'minute', 'second', 'millisecond', 'y', 'M', 'w', 'd', 'h') as $u) {
                    $o = Moment::createInZone($other, $zone);
                    $row[] = array($base->diff($o, $u), $base->diff($o, $u, true));
                }
                $diff[] = $row;
            }
            $got[] = array('add' => $add, 'sub' => $sub, 'start' => $start, 'diff' => $diff);
        }

        return array(self::normalize($got), self::normalize($c['expected']));
    }

    protected static function crossOffsets($c)
    {
        $ms = Moment::createUtc($c['iso'])->valueOf();
        $got = array();
        foreach ($c['ops'] as $op) {
            if ($c['mode'][0] === 'utc') {
                $m = Moment::createUtc($ms);
            } elseif ($c['mode'][0] === 'tz') {
                $m = Moment::createInZone($ms, $c['mode'][1]);
            } else {
                $m = Moment::createUtc($ms)->utcOffset($c['mode'][1]);
            }
            $name = array_shift($op);
            if ($name === 'utcOffset') {
                $m->utcOffset($op[0], isset($op[1]) ? $op[1] : false, isset($op[2]) ? $op[2] : false);
            } else {
                call_user_func_array(array($m, $name), $op);
            }
            $got[] = array($m->format('YYYY-MM-DD HH:mm:ss Z z'), $m->valueOf(), $m->utcOffset(), $m->isUtc(), $m->isUtcOffset(), $m->isLocal(), $m->tz());
        }

        return array(self::normalize($got), self::normalize($c['expected']));
    }

    protected static function crossHumanize($c)
    {
        $got = array();
        foreach ($c['durations'] as $d) {
            $du = Duration::create($d[0], $d[1])->locale($c['locale']);
            $row = array($du->humanize(), $du->humanize(true), $du->humanize(true, array('ss' => 5, 's' => 30, 'm' => 30, 'h' => 12, 'd' => 15, 'w' => 3, 'M' => 6)),
                $du->humanize(array('s' => 50)), $du->humanize(false, array('w' => 4)));
            Duration::relativeTimeRounding('floor');
            $row[] = $du->humanize(true);
            Duration::relativeTimeRounding(array('Wilkques\\Moment\\Calendar', 'round'));
            Duration::relativeTimeThreshold('w', 4);
            $row[] = $du->humanize();
            Duration::relativeTimeThreshold('w', null);
            Duration::relativeTimeThreshold('d', 15);
            Duration::relativeTimeThreshold('M', 6);
            $row[] = $du->humanize(true);
            Duration::relativeTimeThreshold('d', 26);
            Duration::relativeTimeThreshold('M', 11);
            $got[] = $row;
        }

        return array($got, $c['expected']);
    }

    protected static function crossFormats($c)
    {
        $got = array();
        foreach ($c['zones'] as $zone) {
            $z = array();
            foreach ($c['instants'] as $ms) {
                $m = Moment::createInZone($ms, $zone)->locale($c['locale']);
                $row = array();
                foreach ($c['formats'] as $f) {
                    $row[] = $m->format($f);
                }
                $z[] = $row;
            }
            $got[] = $z;
        }

        return array($got, $c['expected']);
    }

    protected static function crossCompare($c)
    {
        $zone = $c['zone'];
        $rows = array();
        foreach ($c['instants'] as $a) {
            foreach ($c['instants'] as $b) {
                $ma = Moment::createInZone($a, $zone);
                $row = array();
                foreach ($c['units'] as $u) {
                    $row[] = array($ma->isSame($b, $u), $ma->isBefore($b, $u), $ma->isAfter($b, $u), $ma->isSameOrBefore($b, $u), $ma->isSameOrAfter($b, $u));
                }
                $rows[] = $row;
            }
        }
        $between = array();
        $i = $c['instants'];
        foreach (array(array(0, 1, 2), array(1, 0, 2), array(0, 3, 4), array(5, 6, 6), array(7, 8, 3)) as $t) {
            foreach (array('()', '[]', '[)', '(]', null) as $inc) {
                foreach ($c['units'] as $u) {
                    $between[] = Moment::createInZone($i[$t[0]], $zone)->isBetween($i[$t[1]], $i[$t[2]], $u, $inc);
                }
            }
        }
        $all = array();
        foreach ($i as $x) {
            $all[] = Moment::createInZone($x, $zone);
        }
        $got = array('rows' => $rows, 'between' => $between, 'minmax' => array(Moment::min($all)->valueOf(), Moment::max($all)->valueOf()));

        return array(self::normalize($got), self::normalize($c['expected']));
    }


    /** seeded random chains of operations and parse round trips (tests/fixtures/fuzz.json.gz) */
    protected static function fuzz($c)
    {
        if ($c['kind'] === 'parse') {
            if ($c['mode'] === 'utc') {
                $m = Moment::createUtc($c['text'], $c['pattern'], $c['locale'], $c['strict']);
            } elseif ($c['mode'] === 'tz') {
                $m = Moment::createInZone($c['text'], $c['zone'], $c['pattern'], $c['locale'], $c['strict']);
            } else {
                $m = Moment::create($c['text'], $c['pattern'], $c['locale'], $c['strict']);
            }
            $f = 'YYYY-MM-DDTHH:mm:ss.SSSZ';

            return array(
                $m->isValid() ? array(true, $m->valueOf(), $m->format($f)) : array(false),
                $c['valid'] ? array(true, $c['ms'], $c['fmt']) : array(false));
        }

        $f = 'YYYY-MM-DDTHH:mm:ss.SSSZ';
        $m = Moment::createInZone($c['ms'], $c['zone'])->locale($c['locale']);
        try {
            foreach ($c['ops'] as $op) {
                $name = array_shift($op);
                call_user_func_array(array($m, $name), $op);
            }
            $o = Moment::createInZone($c['ms'], $c['zone']);
            $other = $m->isValid() ? array($m->diff($o, $c['dunit']), $m->diff($o, $c['dunit'], true), $m->fromNow(), $m->calendar(), $m->toISOString(),
                $m->isBefore($o), $m->isSame($o, $c['dunit'])) : null;
            $got = array($m->format($f), $m->valueOf(), $m->format('LLLL'), $m->format($c['fmt']), $m->isValid(), $m->utcOffset(), $m->format('z'), $other);
        } catch (\Exception $e) {
            $got = array(get_class($e) . ': ' . $e->getMessage());
        }

        return array(self::normalize($got), self::normalize($c['expected']));
    }


    // ------------------------------------------------------------------ Duration / calendar() / runtime locales

    protected static function durationFrom($sp)
    {
        switch ($sp[0]) {
            case 'num':
                return $sp[2] === null ? Duration::create($sp[1]) : Duration::create($sp[1], $sp[2]);
            case 'range':
                return Duration::create(array('from' => Moment::createUtc($sp[1]), 'to' => Moment::createUtc($sp[2])));
        }

        return Duration::create($sp[1]);
    }

    protected static function durationApply($d, $sp, $subtract)
    {
        $f = $subtract ? 'subtract' : 'add';
        switch ($sp[0]) {
            case 'num':
                if ($sp[2] === null) {
                    $d->$f($sp[1]);
                } else {
                    $d->$f($sp[1], $sp[2]);
                }
                break;
            case 'range':
                $d->$f(self::durationFrom($sp));
                break;
            default:
                $d->$f($sp[1]);
        }
    }

    protected static function extraDuration($c)
    {
        $d = self::durationFrom($c['spec'])->locale($c['locale']);
        foreach ($c['ops'] as $op) {
            if ($op[0] === 'abs') {
                $d->abs();
            } elseif ($op[0] === 'clone') {
                $d = $d->clone();
            } else {
                self::durationApply($d, $op[1], $op[0] === 'subtract');
            }
        }
        $got = array('valid' => $d->isValid(), 'humanize' => $d->humanize(), 'humanizeSuffix' => $d->humanize(true),
            'humanizeTh' => $d->humanize(true, array('ss' => 5, 's' => 30, 'm' => 30, 'h' => 12, 'd' => 15, 'w' => 3, 'M' => 6)),
            'iso' => $d->toISOString(), 'json' => $d->toJSON(), 'str' => (string) $d);
        foreach (array('years', 'months', 'days', 'hours', 'minutes', 'seconds', 'milliseconds', 'weeks') as $u) {
            $got[$u] = $d->$u();
            $got['get_' . $u] = $d->get($u);
        }
        foreach (array('ms', 's', 'm', 'h', 'd', 'w', 'M', 'Q', 'y') as $u) {
            $got['as_' . $u] = $d->as($u);
        }
        $got['valueOf'] = $d->valueOf();

        return array(self::normalize($got), self::normalize($c['expected']));
    }

    protected static function extraCalendar($c)
    {
        $now = Calendar::utcMs(2024, 5, 15, 10, 0, 0);
        $m = Moment::createInZone($now + $c['offset'], $c['zone'])->locale($c['locale']);
        $ref = $c['refOff'] === null ? null : Moment::createInZone($now + $c['refOff'], $c['zone']);
        $formats = $c['formats'];
        if ($formats) {
            foreach ($formats as $k => $v) {
                if ($v === 'FN_WEEK') {
                    $formats[$k] = function ($n, $mom) {
                        return $mom->isSame($n, 'week') ? '[same wk] ddd' : '[other wk] ddd';
                    };
                } elseif ($v === 'FN_EMPTY') {
                    $formats[$k] = function ($n, $mom) {
                        return '';
                    };
                }
            }
        }
        if ($c['hook']) {
            Moment::$calendarFormat = function ($mm, $n) {
                $days = $mm->diff($n, 'days');

                return $days > 1 ? 'nextWeek' : ($days < -1 ? 'lastDay' : 'sameDay');
            };
        }
        if ($c['form'] === 'none') {
            $got = $m->calendar();
        } elseif ($c['form'] === 'time') {
            $got = $m->calendar($ref);
        } elseif ($c['form'] === 'formats') {
            $got = $m->calendar($formats);
        } else {
            $got = $m->calendar($ref, $formats);
        }
        Moment::$calendarFormat = null;

        return array($got, $c['expected']);
    }

    protected static function runtimeConfig($c)
    {
        $o = array();
        foreach (array('months', 'monthsShort', 'weekdays', 'weekdaysShort', 'weekdaysMin', 'longDateFormat', 'calendar', 'relativeTime', 'week', 'invalidDate') as $k) {
            if (!isset($c[$k])) {
                continue;
            }
            $v = $c[$k];
            if (is_array($v) && isset($v['isFormat'])) {
                $v['isFormat'] = '~' . $v['isFormat'] . '~u';
            }
            $o[$k] = $v;
        }
        if (isset($c['ordinal'])) {
            if (is_string($c['ordinal'])) {
                $o['ordinal'] = $c['ordinal'];
            } elseif ($c['ordinal']['fn'] === 'suffix') {
                $o['ordinal'] = function ($n, $t) {
                    return $n . '°';
                };
            } else {
                $o['ordinal'] = function ($n, $t) {
                    return $n . ($t === 'M' ? 'ᵐ' : 'ᵈ');
                };
            }
        }
        if (!empty($c['meridiem'])) {
            $o['meridiem'] = function ($h, $mi, $isLower) {
                return $h < 12 ? ($isLower ? 'vm' : 'VM') : ($isLower ? 'nm' : 'NM');
            };
            $o['isPM'] = function ($input) {
                return strtolower(substr($input . '', 0, 1)) === 'n';
            };
            $o['meridiemParse'] = '~[vn]m~iu';
        }
        if (!empty($c['parent'])) {
            $o['parentLocale'] = $c['parent'];
        }

        return $o;
    }

    protected static function runtimeOutputs($name)
    {
        $tokens = array('MMMM', 'MMM', 'dddd', 'ddd', 'dd', 'Do', 'Mo', 'DDDo', 'wo', 'a', 'A', 'LT', 'LTS', 'L', 'LL', 'LLL', 'LLLL', 'D MMMM', 'MMMM D',
            '[T] dddd', 'dddd [T]', 'h:mm A', 'w gggg');
        $instants = array(Calendar::utcMs(2024, 0, 1, 0, 0), Calendar::utcMs(2024, 1, 29, 13, 5), Calendar::utcMs(2024, 5, 15, 10, 0), Calendar::utcMs(2024, 8, 3, 23, 59),
            Calendar::utcMs(2024, 11, 31, 11, 30), Calendar::utcMs(2021, 0, 3, 5, 6));
        $durations = array(array(1, 's'), array(30, 's'), array(45, 's'), array(2, 'm'), array(50, 'm'), array(1, 'h'), array(5, 'h'), array(1, 'd'), array(10, 'd'),
            array(3, 'w'), array(1, 'M'), array(8, 'M'), array(1, 'y'), array(20, 'y'));
        $out = array('fmt' => array(), 'rel' => array(), 'cal' => array(), 'parse' => array());
        foreach ($instants as $ms) {
            $m = Moment::createUtc($ms)->locale($name);
            $row = array();
            foreach ($tokens as $t) {
                $row[] = $m->format($t);
            }
            $out['fmt'][] = $row;
        }
        foreach ($durations as $d) {
            $du = Duration::create($d[0], $d[1])->locale($name);
            $out['rel'][] = array($du->humanize(), $du->humanize(true), Duration::create(-$d[0], $d[1])->locale($name)->humanize(true));
        }
        $now = Calendar::utcMs(2024, 5, 15, 10, 0, 0);
        foreach (array(-8, -3, -1, 0, 1, 2, 5, 9) as $days) {
            $out['cal'][] = Moment::createUtc($now + $days * 86400000 + 3600000)->locale($name)->calendar(Moment::createUtc($now));
        }
        foreach (array('LLL', 'LL', 'L', 'D MMMM YYYY', 'MMM D YYYY h:mm A', 'dddd') as $f) {
            foreach (array_slice($instants, 0, 3) as $ms) {
                $text = Moment::createUtc($ms)->locale($name)->format($f);
                $p = Moment::createUtc($text, $f, $name);
                $ps = Moment::createUtc($text, $f, $name, true);
                $out['parse'][] = array($p->isValid() ? $p->valueOf() : null, $ps->isValid() ? $ps->valueOf() : null);
            }
        }
        $ld = Registry::get($name);
        $out['lists'] = array($ld->months(), $ld->monthsShort(), $ld->weekdays(), $ld->weekdaysShort(), $ld->weekdaysMin(), $ld->firstDayOfWeek(),
            $ld->firstDayOfYear(), Moment::invalid()->locale($name)->format());

        return $out;
    }

    protected static function extraDefineLocale($c)
    {
        $cfg = $c['config'];
        Moment::defineLocale($cfg['name'], self::runtimeConfig($cfg));
        $got = self::runtimeOutputs($cfg['name']);
        Moment::locale('en');

        return array(self::normalize($got), self::normalize($c['expected']));
    }

    protected static function extraUpdateLocale($c)
    {
        $base = $c['base'];
        $before = self::runtimeOutputs($base);
        Moment::updateLocale($base, self::runtimeConfig($c['config']));
        $during = self::runtimeOutputs($base);
        Moment::updateLocale($base, null);
        $after = self::runtimeOutputs($base);
        Moment::locale('en');

        return array(self::normalize(array('before' => $before, 'during' => $during, 'after' => $after)), self::normalize($c['expected']));
    }

    /** random Duration chains, calendar() with custom formats and runtime locales (tests/fixtures/extra.json.gz) */
    protected static function extra($c)
    {
        $method = 'extra' . ucfirst($c['kind']);

        return self::$method($c);
    }


    // ------------------------------------------------------------------ gap-driven suite (tests/fixtures/gaps.json.gz)

    /** the same JSON-able view of a result that generate-gaps-fixtures.js builds */
    protected static function gapsConv($r)
    {
        if ($r instanceof Moment) {
            return $r->isValid() ? 'M:' . $r->format('YYYY-MM-DDTHH:mm:ss.SSSZ') : 'M:invalid';
        }
        if ($r instanceof InvalidDate) {
            return 'D:invalid';
        }
        if ($r instanceof \DateTime) {
            return 'D:' . Calendar::str(Calendar::int((float) $r->format('U') * 1000 + (int) ($r->format('u') / 1000))); // format('U') keeps 64 bits on a 32-bit PHP
        }
        if (is_float($r) || is_int($r)) {
            if (is_float($r) && is_nan($r)) {
                return 'NaN';
            }
            if (is_float($r) && is_infinite($r)) {
                return $r > 0 ? 'Infinity' : '-Infinity';
            }

            return $r;
        }
        if (is_array($r)) {
            foreach ($r as $k => $v) {
                $r[$k] = self::gapsConv($v);
            }
        }

        return $r;
    }

    protected static function gapsInvalid($c)
    {
        $make = function ($which) {
            switch ($which) {
                case 'moment.invalid()': return Moment::invalid();
                case 'bad string': return Moment::createUtc('nope', 'YYYY');
                case 'bad tz': return Moment::createInZone('nope', 'Asia/Taipei', 'YYYY');
            }

            return Moment::createUtc(NAN);
        };
        $got = array();
        foreach ($c['noarg'] as $n) {
            $m = $make($c['which']);
            $r = call_user_func(array($m, $n));
            $got[$n] = self::gapsConv($r);
        }
        foreach ($c['witharg'] as $a) {
            $m = $make($c['which']);
            $name = array_shift($a);
            $key = $name . ($a ? '|' . implode('|', array_map(function ($x) {
                return is_bool($x) ? ($x ? 'true' : 'false') : $x;
            }, $a)) : '');
            $got[$key] = self::gapsConv(call_user_func_array(array($m, $name), $a));
        }

        return array(self::normalize($got), self::normalize($c['expected']));
    }

    protected static function gapsConvert($c)
    {
        $m = Moment::createInZone($c['ms'], $c['zone']);

        return array(self::normalize(array('obj' => $m->toObject(), 'arr' => $m->toArray(), 'date' => self::gapsConv($m->toDate()), 'json' => $m->toJSON(),
            'str' => $m->toString(), 'insp' => $m->inspect(), 'unix' => $m->unix(), 'isDate' => Moment::isDate($m->toDate()), 'isDate2' => Moment::isDate($m))),
            self::normalize(array_merge($c['expected'], array('date' => 'D:' . $c['expected']['date']))));
    }

    protected static function gapsParseTokens($c)
    {
        $got = array();
        foreach ($c['list'] as $r) {
            $m = Moment::createUtc($r['text'], $r['fmt'], $c['locale'], $r['strict']);
            $row = array('valid' => $m->isValid());
            if ($m->isValid()) {
                $row['ms'] = $m->valueOf();
            }
            $got[] = $row;
        }
        $want = array();
        foreach ($c['list'] as $r) {
            $row = array('valid' => $r['valid']);
            if ($r['valid']) {
                $row['ms'] = $r['ms'];
            }
            $want[] = $row;
        }

        return array(self::normalize($got), self::normalize($want));
    }

    protected static function gapsSetStrings($c)
    {
        $got = array();
        foreach ($c['bases'] as $b) {
            $row = array();
            foreach ($c['sets'] as $s) {
                $m = Moment::createUtc($b)->locale($c['locale']);
                $name = array_shift($s);
                try {
                    call_user_func_array(array($m, $name), $s);
                    $row[] = self::gapsConv($m);
                } catch (\Exception $e) {
                    $row[] = 'threw';
                }
            }
            $got[] = $row;
        }

        return array($got, $c['expected']);
    }

    protected static function gapsMinmax($c)
    {
        $base = Moment::createUtc($c['base']);
        $got = array();
        $quiet = Moment::$suppressDeprecationWarnings;
        Moment::$suppressDeprecationWarnings = true; // the instance min()/max() are deprecated: that's what's being replayed
        foreach ($c['cases'] as $x) {
            if (isset($x['arg'])) {
                $arg = $x['arg'] === 'nope' ? Moment::invalid() : $x['arg'];
                $got[] = self::gapsConv(call_user_func(array($base->copy(), $x['fn']), $arg));
            } else {
                $fn = substr($x['fn'], 0, 3);
                $got[] = call_user_func(array('Wilkques\\Moment\\Moment', $fn))->valueOf() === Calendar::utcMs(2024, 5, 15, 10, 0, 0) ? 'now' : 'other';
            }
        }
        Moment::$suppressDeprecationWarnings = $quiet;
        $want = array();
        foreach ($c['cases'] as $x) {
            $want[] = $x['expected'];
        }

        return array($got, $want);
    }

    protected static function gapsStatics($c)
    {
        $M = 'Wilkques\\Moment\\Moment';
        $got = array('isDate' => array(Moment::isDate(new \DateTime()), Moment::isDate(1), Moment::isDate(null)),
            'isDuration' => array(Moment::isDuration(Moment::duration(1)), Moment::isDuration(1)),
            'thr' => array(Moment::relativeTimeThreshold('s'), Moment::relativeTimeThreshold('ss'), Moment::relativeTimeThreshold('w'), Moment::relativeTimeThreshold('M'),
                Moment::relativeTimeThreshold('x'), Moment::relativeTimeThreshold('s', 50), Moment::relativeTimeThreshold('s'), Moment::relativeTimeThreshold('ss'),
                Moment::relativeTimeThreshold('s', 45)));

        return array(self::normalize($got), self::normalize($c['expected']));
    }

    protected static function gapsSpecial($c)
    {
        $map = function ($f) {
            return $f; // 'ISO_8601' / 'RFC_2822' are the same strings as Moment::ISO_8601 / RFC_2822
        };
        $got = array();
        foreach ($c['strings'] as $s) {
            foreach ($c['formats'] as $f) {
                foreach (array(false, true) as $strict) {
                    $fmt = $map($f);
                    if ($c['mode'] === 'utc') {
                        $m = Moment::createUtc($s, $fmt, null, $strict);
                    } elseif ($c['mode'] === 'tz') {
                        $m = Moment::createInZone($s, 'America/New_York', $fmt, null, $strict);
                    } else {
                        $m = Moment::create($s, $fmt, null, $strict);
                    }
                    $pf = $m->parsingFlags();
                    $got[] = array($m->isValid(), $m->isValid() ? $m->valueOf() : null, $pf['iso'], $pf['rfc2822'], $pf['weekdayMismatch']);
                }
            }
        }

        return array(self::normalize($got), self::normalize($c['expected']));
    }

    protected static function gapsNumberStrings($c)
    {
        $got = array();
        $want = array();
        foreach ($c['expected'] as $r) {
            $d = Duration::create($r['in']);
            $got[] = array('valid' => $d->isValid(), 'ms' => self::gapsConv($d->asMilliseconds()), 'iso' => $d->toISOString(), 'humanize' => $d->humanize());
            $want[] = array('valid' => $r['valid'], 'ms' => $r['ms'], 'iso' => $r['iso'], 'humanize' => $r['humanize']);
        }

        return array(self::normalize($got), self::normalize($want));
    }

    protected static function gapsExtremeDurations($c)
    {
        $got = array();
        $want = array();
        foreach ($c['expected'] as $r) {
            $n = $r['in'][0];
            $n = $n === 'Infinity' ? INF : ($n === '-Infinity' ? -INF : ($n === 'NaN' ? NAN : $n));
            $d = Duration::create($n, $r['in'][1]);
            try {
                $d->as('bogus');
                $threw = false;
            } catch (\Exception $e) {
                $threw = true;
            }
            $got[] = array('valid' => $d->isValid(), 'iso' => $d->toISOString(), 'years' => self::gapsConv($d->years()), 'days' => self::gapsConv($d->days()),
                'hours' => self::gapsConv($d->hours()), 'ms' => self::gapsConv($d->asMilliseconds()), 'asY' => self::gapsConv($d->asYears()),
                'humanize' => $d->humanize(), 'threw' => $threw);
            $want[] = array('valid' => $r['valid'], 'iso' => $r['iso'], 'years' => $r['years'], 'days' => $r['days'], 'hours' => $r['hours'], 'ms' => $r['ms'],
                'asY' => $r['asY'], 'humanize' => $r['humanize'], 'threw' => $r['threw'] !== null);
        }

        return array(self::normalize($got), self::normalize($want));
    }

    protected static function gapsLocaleLookup($c)
    {
        $localeData = array();
        $instance = array();
        $global = array();
        foreach ($c['keys'] as $k) {
            $l = $k === null ? Registry::get() : Registry::get($k);
            $localeData[] = $l ? $l->abbr : null;

            $m = Moment::createUtc(Calendar::utcMs(2024, 5, 15, 10, 0, 0))->locale('ru');
            if ($k === null) {
                $m->locale();
            } else {
                $m->locale($k);
            }
            $instance[] = $m->locale();

            Moment::locale('en');
            $global[] = $k === null ? Moment::locale() : Moment::locale($k);
            Moment::locale('en');
        }

        return array(array('localeData' => $localeData, 'instance' => $instance, 'global' => $global), $c['expected']);
    }

    /** which strings reach Moment::$createFromInputFallback, and in which modes */
    protected static function gapsFallbackHook($c)
    {
        $fixed = $c['fixed'];
        $calls = array();
        Moment::$createFromInputFallback = function ($string, $useUtc) use (&$calls, $fixed) {
            $calls[] = array($string, (bool) $useUtc);

            return array('ms' => $fixed);
        };
        $modes = array(
            'create' => function ($x) { return Moment::create($x); },
            'utc' => function ($x) { return Moment::utc($x); },
            'tz' => function ($x) { return Moment::tz($x, 'Asia/Taipei'); },
            'parseZone' => function ($x) { return Moment::parseZone($x); },
            'strict-format' => function ($x) { return Moment::create($x, 'YYYY-MM-DD', true); },
            'format' => function ($x) { return Moment::create($x, 'YYYY-MM-DD'); },
            'array' => function ($x) { return Moment::create(array($x)); },
        );
        $got = array();
        try {
            foreach ($c['inputs'] as $str) {
                foreach ($modes as $name => $fn) {
                    $calls = array();
                    $m = $fn($str);
                    $valid = $m->isValid();
                    $got[] = array('in' => $str, 'mode' => $name, 'calls' => $calls, 'valid' => $valid, 'ms' => $valid ? $m->valueOf() : null);
                }
            }
        } catch (\Exception $e) {
            Moment::$createFromInputFallback = null;
            throw $e;
        }
        Moment::$createFromInputFallback = null;

        return array(self::normalize($got), self::normalize($c['expected']));
    }

    /** when Moment::$updateOffset is called, and with which keepTime */
    protected static function gapsUpdateOffsetHook($c)
    {
        $now = Calendar::utcMs(2024, 5, 15, 10, 0, 0);
        $log = '';
        $starts = array(
            'utc' => function () use ($now) { return Moment::utc($now); },
            'local' => function () use ($now) { return Moment::create($now); },
            'ny' => function () use ($now) { return Moment::tz($now, 'America/New_York'); },
            'offset' => function () use ($now) { return Moment::utc($now)->utcOffset(-300); },
        );
        $ops = array(
            'create' => function () use ($now) { return Moment::create($now); },
            'createUtc' => function () use ($now) { return Moment::utc($now); },
            'createTz' => function () use ($now) { return Moment::tz($now, 'America/New_York'); },
            'createMoment' => function () use ($now) { return Moment::create(Moment::utc($now)); },
            'createNull' => function () { return Moment::create(null, null); },
            'createDate' => function () use ($now) { return Moment::create(new \DateTime('@' . ($now / 1000))); },
            'createArray' => function () { return Moment::create(array(2024, 0, 1)); },
            'createOffset' => function () use ($now) { return Moment::utc($now)->utcOffset(120); },
            'parseZone' => function () { return Moment::parseZone('2024-01-01T00:00:00+05:30'); },
            'utc' => function ($m) { return $m->utc(); },
            'local' => function ($m) { return $m->local(); },
            'utcOffset' => function ($m) { return $m->utcOffset(60); },
            'utcOffsetKeep' => function ($m) { return $m->utcOffset(60, true); },
            'utcOffsetStr' => function ($m) { return $m->utcOffset('+08:00'); },
            'add d' => function ($m) { return $m->add(1, 'd'); },
            'add h' => function ($m) { return $m->add(1, 'h'); },
            'add M' => function ($m) { return $m->add(1, 'M'); },
            'add 0' => function ($m) { return $m->add(0, 'd'); },
            'subtract' => function ($m) { return $m->subtract(1, 'd'); },
            'add dur' => function ($m) { return $m->add(Moment::duration(1, 'day')); },
            'month' => function ($m) { return $m->month(1); },
            'date' => function ($m) { return $m->date(5); },
            'hour' => function ($m) { return $m->hour(3); },
            'set' => function ($m) { return $m->set('year', 2000); },
            'startOf' => function ($m) { return $m->startOf('day'); },
            'endOf' => function ($m) { return $m->endOf('month'); },
            'tz' => function ($m) { return $m->tz('Asia/Taipei'); },
            'tzKeep' => function ($m) { return $m->tz('Asia/Taipei', true); },
            'tzUtc' => function ($m) { return $m->tz('UTC'); },
            'tzBad' => function ($m) { return $m->tz('Nope/Zone'); },
            'dayOfYear' => function ($m) { return $m->dayOfYear(10); },
            'week' => function ($m) { return $m->week(3); },
            'weekday' => function ($m) { return $m->weekday(2); },
            'clone' => function ($m) { return $m->copy(); },
            'diff' => function ($m) { $m->diff(Moment::utc($m->valueOf() + 1000000000)); return $m; },
            'format' => function ($m) { $m->format(); return $m; },
            'invalid' => function () { return Moment::invalid(); },
            'unix' => function () { return Moment::unix(1700000000); },
        );
        Moment::$updateOffset = function ($m, $keep) use (&$log) {
            $log .= $keep === null ? 'u' : ($keep ? 't' : 'f');
        };
        $got = array();
        try {
            foreach ($c['ops'] as $op) {
                foreach ($c['starts'] as $st) {
                    $m = $starts[$st]();
                    $log = '';
                    $r = $ops[$op]($m);
                    $valid = $r->isValid();
                    $got[] = array('op' => $op, 'start' => $st, 'calls' => $log, 'ms' => $valid ? $r->valueOf() : null, 'off' => $valid ? $r->utcOffset() : null);
                }
            }
        } catch (\Exception $e) {
            Moment::$updateOffset = null;
            throw $e;
        }
        Moment::$updateOffset = null;

        return array(self::normalize($got), self::normalize($c['expected']));
    }

    /** the updateOffset calls moment.tz(...) makes for each kind of input (moment-timezone builds some of them twice over) */
    protected static function gapsTzCreateHook($c)
    {
        Moment::setNow(Calendar::utcMs(2024, 5, 15, 10, 0, 0));
        $log = '';
        Moment::$updateOffset = function ($m, $keep) use (&$log) {
            $log .= $keep === null ? 'u' : ($keep ? 't' : 'f');
        };
        $got = array();
        try {
            foreach ($c['specs'] as $sp) {
                $log = '';
                $inp = isset($sp['i']) ? $sp['i'] : null;
                if (is_array($inp) && isset($inp['moment'])) {
                    $inp = Moment::utc($inp['moment']);
                } elseif (is_array($inp) && isset($inp['date'])) {
                    $inp = new \DateTime('@' . ($inp['date'] / 1000));
                }
                $log = '';
                $m = Moment::tz($inp, $sp['z'], isset($sp['f']) ? $sp['f'] : null, isset($sp['l']) ? $sp['l'] : null, !empty($sp['strict']));
                $valid = $m->isValid();
                $got[] = array('n' => $sp['n'], 'calls' => $log, 'valid' => $valid, 'text' => $valid ? $m->format() : null);
            }
        } catch (\Exception $e) {
            Moment::$updateOffset = null;
            Moment::setNow(null);
            throw $e;
        }
        Moment::$updateOffset = null;
        Moment::setNow(null);

        return array(self::normalize($got), self::normalize($c['expected']));
    }

    /** V8's date parser on its own: new Date(string).getTime() for strings including ones moment would parse itself */
    protected static function gapsV8Raw($c)
    {
        $got = array();
        foreach ($c['inputs'] as $str) {
            $r = \Wilkques\Moment\Parse\V8DateParser::parse($str);
            if ($r === null) {
                $got[] = null;
            } elseif (!empty($r['host']) || $r['tzm'] === null) {
                $got[] = $r['wall']; // the host zone is UTC here
            } else {
                $got[] = $r['wall'] - $r['tzm'] * 60000;
            }
        }

        return array(self::normalize($got), self::normalize($c['expected']));
    }

    protected static function gapsZonesForCountry($c)
    {
        Moment::setNow(Calendar::utcMs(2024, 5, 15, 10, 0, 0));
        $got = array();
        foreach ($c['expected'] as $row) {
            $got[] = array('c' => $row['c'], 'plain' => Tz::zonesForCountry($row['c']), 'off' => Tz::zonesForCountry($row['c'], true));
        }
        Moment::setNow(null);

        return array(self::normalize($got), self::normalize($c['expected']));
    }

    protected static function gaps($c)
    {
        $method = 'gaps' . ucfirst($c['kind']);

        return self::$method($c);
    }

    /** the cross-product cases (tests/fixtures/cross.json.gz), by kind */
    protected static function cross($c)
    {
        $method = 'cross' . ucfirst($c['kind']);

        return self::$method($c);
    }

    /** the bundled zone data against moment-timezone: offset, abbreviation and parse() at random instants */
    protected static function tzcheck($c)
    {
        $z = Tz::zone($c['zone']);

        return array(
            $z ? array($z->utcOffset($c['at']), $z->abbr($c['at']), $z->parse($c['at'])) : null,
            array($c['offset'], $c['abbr'], $c['parse']));
    }

    /** moment's fallback to `new Date(string)`; local time is the fixture's TZ */
    protected static function fallback($c)
    {
        date_default_timezone_set($c['tz']);
        $m = $c['utc'] ? Moment::createUtc($c['input']) : Moment::create($c['input']);
        date_default_timezone_set('UTC');

        return array($m->isValid() ? $m->valueOf() : 'invalid', $c['valid'] ? $c['ms'] : 'invalid');
    }

    /** deep comparison where floats only need to agree to ~1e-9 */
    protected static function same($a, $b)
    {
        if (is_array($a) && is_array($b)) {
            if (array_keys($a) !== array_keys($b)) {
                return false;
            }
            foreach ($a as $k => $v) {
                if (!self::same($v, $b[$k])) {
                    return false;
                }
            }

            return true;
        }
        if ((is_int($a) || is_float($a)) && (is_int($b) || is_float($b))) {
            return abs($a - $b) <= 1e-9 * max(1, abs($b));
        }

        return $a === $b;
    }

    /** the less common API: aliases, flags, creationData, parseZone, statics, moment-timezone data API */
    protected static function api($c)
    {
        \Wilkques\Moment\Moment::$suppressDeprecationWarnings = true;
        $call = function ($class, $fn, $args) {
            return call_user_func_array(array($class, $fn), $args);
        };
        $M = 'Wilkques\\Moment\\Moment';

        switch ($c['kind']) {
            case 'method':
                $m = self::mk($c['iso'], $c['zone'], $c['locale']);
                if ($c['method'] === 'invalidAt') {
                    // invalidAt() reports what parsing saw, so build it from the string like the fixture did
                    $m = Moment::createInZone($c['iso'], $c['zone'])->locale($c['locale']);
                }
                $args = $c['args'];
                if ($c['method'] === 'hasAlignedHourOffset' && $args && $args[0] === null) {
                    $args = array();
                }
                $got = $call($m, $c['method'], $args);
                $want = $c['expected'];
                if (is_float($got) && is_nan($got)) {
                    $got = null;
                }

                return array(self::normalize($got), self::normalize($want));

            case 'create':
                $input = $c['isNaN'] ? NAN : $c['input'];
                $fmt = $c['format'];
                if ($c['mode'] === 'utc') {
                    $m = $input === null ? Moment::createUtc(null, $fmt, null, $c['strict']) : Moment::createUtc($input, $fmt, null, $c['strict']);
                } elseif ($c['mode'] === 'tz') {
                    $m = Moment::createInZone($input, 'America/New_York', $fmt, null, $c['strict']);
                } else {
                    $m = Moment::create($input, $fmt, null, $c['strict']);
                }
                $pf = $m->parsingFlags();
                $keys = array('iso', 'rfc2822', 'empty', 'charsLeftOver', 'unusedTokens', 'overflow', 'nullInput', 'userInvalidated', 'weekdayMismatch', 'invalidMonth', 'invalidFormat');
                $flags = array();
                foreach ($keys as $k) {
                    $flags[$k] = $pf[$k];
                }
                $cd = $m->creationData();
                $got = array('valid' => $m->isValid(), 'invalidAt' => $m->invalidAt(), 'flags' => $flags,
                    'cd' => array('format' => $cd['format'], 'strict' => (bool) $cd['strict'], 'isUTC' => $cd['isUTC']), 'dst' => $m->isDSTShifted());
                $want = array('valid' => $c['valid'], 'invalidAt' => $c['invalidAt'], 'flags' => $c['flags'],
                    'cd' => $c['cd'], 'dst' => $c['dstShifted']);
                if ($c['valid']) {
                    $got['ms'] = $m->valueOf();
                    $got['fmt'] = $m->format();
                    $want['ms'] = $c['ms'];
                    $want['fmt'] = $c['fmt'];
                }

                return array(self::normalize($got), self::normalize($want));

            case 'parseZone':
                $m = $call($M, 'parseZone', array($c['input']));

                return array(
                    array($m->isValid(), $m->isValid() ? $m->utcOffset() : null, $m->isValid() ? $m->format('YYYY-MM-DD HH:mm Z') : null),
                    array($c['valid'], $c['offset'], $c['fmt']));

            case 'static':
                if ($c['fn'] === 'localeAbbr') {
                    Moment::locale($c['locale']);
                    $got = Moment::locale();
                } else {
                    Moment::locale($c['locale']);
                    $got = $call($M, $c['fn'], $c['args']);
                }
                Moment::locale('en');

                return array(self::normalize($got), self::normalize($c['expected']));

            case 'add':
                $m = Moment::createUtc('2024-01-31T12:00:00Z');
                call_user_func_array(array($m, 'add'), $c['args']);

                return array($m->toISOString(), $c['expected']);

            case 'duration':
                $d = call_user_func_array(array($M, 'duration'), $c['args']);
                $got = array($d->as('hours'), $d->as('M'), $d->toIsoString(), $d->clone()->add(1, 'h')->toISOString(), $d->lang('fr')->humanize());
                $want = array($c['as'], $c['asM'], $c['iso'], $c['cloneIso'], $c['lang']);

                return array(self::normalize($got), self::normalize($want));

            case 'invalid':
                $m = $c['flags'] === null ? Moment::invalid() : Moment::invalid($c['flags']);
                $pf = $m->parsingFlags();
                if ($c['flags'] === null) {
                    $got = array('valid' => $m->isValid(), 'userInvalidated' => $pf['userInvalidated'], 'invalidAt' => $m->invalidAt(), 'fmt' => $m->format());
                } else {
                    $got = array('valid' => $m->isValid(), 'invalidMonth' => $pf['invalidMonth']);
                }

                return array($got, $c['expected']);

            case 'html5':
                return array(Moment::$HTML5_FMT, $c['expected']);

            case 'version':
                return array(Moment::VERSION, $c['expected']);

            case 'tz':
                return self::tzCase($c);

            case 'tzsettings':
                return self::tzSettings($c);
        }

        return array('unknown kind ' . $c['kind'], null);
    }

    protected static function normalize($v)
    {
        if (is_float($v)) {
            return is_nan($v) ? null : (float) sprintf('%.9e', $v);
        }
        if (is_array($v)) {
            foreach ($v as $k => $x) {
                $v[$k] = self::normalize($x);
            }
        }

        return $v;
    }

    protected static function zoneArray($z)
    {
        if (!$z) {
            return null;
        }
        $untils = array();
        foreach ($z->untils as $u) {
            $untils[] = is_infinite($u) ? 'Infinity' : $u;
        }

        return array('name' => $z->name, 'abbrs' => $z->abbrs, 'offsets' => $z->offsets, 'untils' => $untils, 'population' => $z->population);
    }

    protected static function probe($m)
    {
        return $m->isValid() ? array($m->valueOf(), $m->format('YYYY-MM-DD HH:mm Z z')) : null;
    }

    /** moment.tz.setDefault() and the move*Forward settings, replayed step by step */
    protected static function tzSettings($c)
    {
        $T = 'Wilkques\\Moment\\Timezone\\Tz';
        $got = array();
        $inputs = $c['inputs'];
        foreach ($c['steps'] as $st) {
            switch ($st['op']) {
                case 'setDefault':
                    call_user_func(array($T, 'setDefault'), $st['zone']);
                    break;
                case 'flags':
                    Tz::$moveInvalidForward = $st['moveInvalidForward'];
                    Tz::$moveAmbiguousForward = $st['moveAmbiguousForward'];
                    break;
                case 'probe':
                    $rows = array();
                    foreach ($inputs as $i) {
                        $rows[] = array(self::probe(Moment::create($i)), self::probe(Moment::createUtc($i)), self::probe(Moment::createInZone($i, 'Asia/Tokyo')));
                    }
                    $rows[] = array(self::probe(Moment::create()), self::probe(Moment::createUtc()), null);
                    $got[] = $rows;
                    break;
                case 'probeTz':
                    $rows = array();
                    foreach (array_merge(array_slice($inputs, 0, 3), array('2024-03-31 01:30', '2024-10-27 01:30')) as $i) {
                        $rows[] = self::probe(Moment::createInZone($i, $st['zone']));
                    }
                    $got[] = $rows;
                    break;
                case 'ops':
                    $m = Moment::create('2024-03-09 12:00');
                    $got[] = array(self::probe($m->copy()->add(1, 'day')), self::probe($m->copy()->startOf('day')),
                        self::probe($m->copy()->add(1, 'day')->startOf('day')), self::probe(Moment::create(1710054000000)->startOf('hour')),
                        self::probe($m->copy()->endOf('month')));
                    break;
                default:
                    $got[] = null;
            }
        }
        call_user_func(array($T, 'setDefault'), null);
        Tz::$moveInvalidForward = true;
        Tz::$moveAmbiguousForward = false;

        return array(self::normalize($got), self::normalize($c['expected']));
    }

    protected static function tzCase($c)
    {
        $T = 'Wilkques\\Moment\\Timezone\\Tz';
        switch ($c['fn']) {
            case 'dataVersion':
                return array(call_user_func(array($T, 'dataVersion')), $c['expected']);
            case 'names':
            case 'countries':
                return array(call_user_func(array($T, $c['fn'])), $c['expected']);
            case 'zonesForCountry':
                return array(call_user_func(array($T, 'zonesForCountry'), $c['args'][0]), $c['expected']);
            case 'zone':
                return array(self::normalize(self::zoneArray(call_user_func(array($T, 'zone'), $c['args'][0]))), self::normalize($c['expected']));
            case 'zoneExists':
                return array(call_user_func(array($T, 'zoneExists'), $c['args'][0]), $c['expected']);
            case 'unpackBase60':
                return array(self::normalize(call_user_func(array($T, 'unpackBase60'), $c['args'][0])), self::normalize($c['expected']));
            case 'zoneCountries':
                $z = call_user_func(array($T, 'zone'), $c['args'][0]);
                $got = $z->countries();
                sort($got);

                return array($got, $c['expected']);
            case 'load':
                call_user_func(array($T, 'load'), $c['args'][0]);
                $z = call_user_func(array($T, 'zone'), 'Test/Load');
                $tw = call_user_func(array($T, 'zone'), 'Asia/Taipei');
                $load = $z->countries();
                sort($load);
                $taipei = $tw->countries();
                sort($taipei);
                $countries = call_user_func(array($T, 'countries'));
                $got = array('version' => call_user_func(array($T, 'dataVersion')),
                    'countries' => in_array('XX', $countries) && in_array('YY', $countries),
                    'xx' => call_user_func(array($T, 'zonesForCountry'), 'xx'), 'yy' => call_user_func(array($T, 'zonesForCountry'), 'YY'),
                    'loadZone' => call_user_func(array($T, 'zone'), 'Test/LoadAlias')->name, 'loadCountries' => $load, 'taipei' => $taipei,
                    'at' => Moment::createInZone(Calendar::utcMs(2021, 6, 1), 'Test/LoadAlias')->format('Z z'),
                    'names' => in_array('Test/LoadAlias', call_user_func(array($T, 'names'))));
                call_user_func(array($T, 'reset'));

                return array(self::normalize($got), self::normalize($c['expected']));
            case 'custom':
                call_user_func(array($T, 'add'), $c['args'][0]);
                call_user_func(array($T, 'link'), $c['args'][1]);
                $z = call_user_func(array($T, 'zone'), 'Test/Alias');
                $arr = self::zoneArray($z);
                $got = array('name' => $arr['name'], 'offsets' => $arr['offsets'], 'abbrs' => $arr['abbrs'], 'untils' => $arr['untils'],
                    'at' => Moment::createInZone(Calendar::utcMs(2020, 5, 1), 'Test/Alias')->format('Z z'),
                    'names' => in_array('Test/Alias', call_user_func(array($T, 'names'))));

                call_user_func(array($T, 'reset'));

                return array(self::normalize($got), self::normalize($c['expected']));
        }

        return array('unknown tz fn', null);
    }

    protected static function duration($c)
    {
        $in = $c['input'];
        if (isset($in[0]) && is_array($in[0]) && isset($in[0]['from'])) {
            $in = array(array('from' => Moment::createUtc(self::isoMs($in[0]['from'])), 'to' => Moment::createUtc(self::isoMs($in[0]['to']))));
        }
        $d = Moment::duration($in[0], isset($in[1]) ? $in[1] : null)->locale($c['locale']);

        if (!$c['valid']) {
            return array($d->isValid() ? 'valid' : $d->humanize(), $c['humanize']);
        }

        $units = array('ms', 's', 'm', 'h', 'd', 'w', 'M', 'Q', 'y');
        $gets = array('milliseconds', 'seconds', 'minutes', 'hours', 'days', 'weeks', 'months', 'years');
        $got = array();
        $want = array();
        foreach ($units as $u) {
            $got[] = self::round($d->toUnit($u));
            $want[] = self::round($c['as_' . $u]);
        }
        foreach ($gets as $g) {
            $got[] = self::round($d->$g());
            $want[] = self::round($c['get_' . $g]);
        }
        $got = array_merge($got, array($d->humanize(), $d->humanize(true), $d->toISOString()));
        $want = array_merge($want, array($c['humanize'], $c['humanizeSuffix'], $c['iso']));

        return array($got, $want);
    }

    protected static function relative($c)
    {
        $m = Moment::createInZone(Moment::now() + $c['offset'], $c['zone'])->locale($c['locale']);
        $base = Moment::createInZone(Moment::now(), $c['zone'])->locale($c['locale']);

        return array(
            array($m->fromNow(), $m->fromNow(true), $m->toNow(), $m->from($base), $m->calendar(), $m->calendar(null, array('sameElse' => 'YYYY|MMM'))),
            array($c['fromNow'], $c['fromNowBare'], $c['toNow'], $c['from'], $c['calendar'], $c['calendarFmt']),
        );
    }
}
