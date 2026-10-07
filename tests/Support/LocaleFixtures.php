<?php

namespace Wilkques\Moment\Tests\Support;

use Wilkques\Moment\Calendar;
use Wilkques\Moment\Duration;
use Wilkques\Moment\Locale\Registry;
use Wilkques\Moment\Moment;

/**
 * Replays tests/fixtures/locales/<name>.json.gz (golden data from the real moment.js) against the
 * PHP locale class: formats, ordinals, name lists, relative time, calendar and parsing.
 */
class LocaleFixtures
{
    public static function names()
    {
        $names = array();
        foreach (glob(dirname(__DIR__) . '/fixtures/locales/*.json.gz') as $file) {
            $names[] = basename($file, '.json.gz');
        }

        return $names;
    }

    protected static function js($v)
    {
        return json_encode($v);
    }

    /**
     * @return array array('total' => int, 'failures' => array(category => array(count, examples)))
     */
    public static function check($name, $maxExamples = 3)
    {
        date_default_timezone_set('UTC');
        Moment::setNow(Calendar::utcMs(2024, 5, 15, 10, 0, 0)); // the fixtures were made with this "now"
        $file = dirname(__DIR__) . '/fixtures/locales/' . $name . '.json.gz';
        $fx = json_decode(file_get_contents('compress.zlib://' . $file), true);

        $locale = Registry::get($name);
        if (!$locale || $locale->abbr !== $name) {
            return array('total' => 1, 'failures' => array('missing' => array(1, array('no locale class for ' . $name))));
        }

        $fail = array();
        $total = 0;
        $rec = function ($cat, $ok, $info) use (&$fail, &$total, $maxExamples) {
            $total++;
            if (!$ok) {
                if (!isset($fail[$cat])) {
                    $fail[$cat] = array(0, array());
                }
                $fail[$cat][0]++;
                if (count($fail[$cat][1]) < $maxExamples) {
                    $fail[$cat][1][] = $info;
                }
            }
        };
        $mk = function ($ms) use ($name) {
            return Moment::createUtc($ms)->locale($name);
        };

        foreach ($fx['dates'] as $i => $ms) {
            foreach ($fx['tokens'] as $j => $tok) {
                try {
                    $got = $mk($ms)->format($tok);
                } catch (\Exception $e) {
                    $got = get_class($e) . ': ' . $e->getMessage();
                }
                $rec('format', $got === $fx['format'][$i][$j], "format('$tok') of " . gmdate('Y-m-d H:i:s', (int) floor($ms / 1000)) . ': got ' . self::js($got) . ' want ' . self::js($fx['format'][$i][$j]));
            }
        }
        foreach ($fx['ordDates'] as $i => $ms) {
            foreach ($fx['ordTokens'] as $j => $tok) {
                try {
                    $got = $mk($ms)->format($tok);
                } catch (\Exception $e) {
                    $got = get_class($e) . ': ' . $e->getMessage();
                }
                $rec('ordinals/names', $got === $fx['ordinals'][$i][$j], "format('$tok') of " . gmdate('Y-m-d', (int) floor($ms / 1000)) . ': got ' . self::js($got) . ' want ' . self::js($fx['ordinals'][$i][$j]));
            }
        }

        $lists = array('months' => $locale->months(), 'monthsShort' => $locale->monthsShort(), 'weekdays' => $locale->weekdays(),
            'weekdaysShort' => $locale->weekdaysShort(), 'weekdaysMin' => $locale->weekdaysMin(), 'weekdaysLocaleSorted' => $locale->weekdays(true));
        foreach ($lists as $k => $v) {
            $rec('lists', $v === $fx[$k], "$k: got " . self::js($v) . ' want ' . self::js($fx[$k]));
        }
        $rec('lists', Moment::invalid()->locale($name)->format() === $fx['invalid'], 'invalid date text');

        $lastW = 'x';
        $lastD = 'x';
        foreach ($fx['relative'] as $r) {
            if ($r['w'] !== $lastW || $r['d'] !== $lastD) {
                Duration::relativeTimeThreshold('w', $r['w']);
                Duration::relativeTimeThreshold('d', $r['d']);
                $lastW = $r['w'];
                $lastD = $r['d'];
            }
            try {
                $got = array(Duration::create($r['n'], $r['u'])->locale($name)->humanize(), Duration::create($r['n'], $r['u'])->locale($name)->humanize(true),
                    Duration::create(-$r['n'], $r['u'])->locale($name)->humanize(true));
            } catch (\Exception $e) {
                $got = array(get_class($e) . ': ' . $e->getMessage());
            }
            $want = array($r['bare'], $r['future'], $r['past']);
            $rec('relativeTime', $got === $want, "duration({$r['n']}, {$r['u']}) w={$r['w']} d={$r['d']}: got " . self::js($got) . ' want ' . self::js($want));
        }
        Duration::relativeTimeThreshold('w', null);
        Duration::relativeTimeThreshold('d', 26);

        foreach ($fx['calendar'] as $c) {
            try {
                $got = $mk($c['now'] + $c['off'] * 60000)->calendar($mk($c['now']));
            } catch (\Exception $e) {
                $got = get_class($e) . ': ' . $e->getMessage();
            }
            $rec('calendar', $got === $c['text'], "calendar now=" . gmdate('D H:i', (int) floor($c['now'] / 1000)) . " offset {$c['off']}min: got " . self::js($got) . ' want ' . self::js($c['text']));
        }

        $defaults = array('ss' => 44, 's' => 45, 'm' => 45, 'h' => 22, 'd' => 26, 'w' => null, 'M' => 11);
        $lastUnit = null;
        foreach ($fx['force'] as $r) {
            if ($r['u'] !== $lastUnit) {
                foreach ($defaults as $k => $v) {
                    Duration::relativeTimeThreshold($k, $v);
                }
                foreach ($r['th'] as $k => $v) {
                    Duration::relativeTimeThreshold($k, $v);
                }
                $lastUnit = $r['u'];
            }
            try {
                $got = array(Duration::create($r['n'], $r['u'])->locale($name)->humanize(), Duration::create($r['n'], $r['u'])->locale($name)->humanize(true),
                    Duration::create(-$r['n'], $r['u'])->locale($name)->humanize(true));
            } catch (\Exception $e) {
                $got = array(get_class($e) . ': ' . $e->getMessage());
            }
            $want = array($r['bare'], $r['future'], $r['past']);
            $rec('relativeTime-forced', $got === $want, "duration({$r['n']}, {$r['u']}): got " . self::js($got) . ' want ' . self::js($want));
        }
        foreach ($defaults as $k => $v) {
            Duration::relativeTimeThreshold($k, $v);
        }

        foreach (array('parse', 'names') as $suite) {
            foreach ($fx[$suite] as $p) {
                try {
                    $m = Moment::createUtc($p['text'], $p['fmt'], $name, $p['strict']);
                    $got = $m->isValid() ? $m->valueOf() : 'invalid';
                } catch (\Exception $e) {
                    $got = get_class($e) . ': ' . $e->getMessage();
                }
                $want = $p['valid'] ? $p['ms'] : 'invalid';
                $rec('parse-' . $suite, $got === $want, 'parse(' . self::js($p['text']) . ", '{$p['fmt']}', strict=" . ($p['strict'] ? 'true' : 'false') . '): got ' . self::js($got) . ' want ' . self::js($want));
            }
        }

        Moment::setNow(null);

        self::checkFunctions($name, $locale, $rec);

        return array('total' => $total, 'failures' => $fail);
    }

    /** tests/fixtures/localefn/<name>.json.gz: the locale's functions called directly, with inputs humanize() never reaches */
    protected static function checkFunctions($name, $locale, $rec)
    {
        $file = dirname(__DIR__) . '/fixtures/localefn/' . $name . '.json.gz';
        if (!is_file($file)) {
            return;
        }
        $fx = json_decode(file_get_contents('compress.zlib://' . $file), true);
        $call = function ($f) {
            try {
                $r = $f();

                return is_float($r) && (is_nan($r) || is_infinite($r)) ? (string) $r : $r;
            } catch (\Exception $e) {
                return 'THROWS';
            }
        };

        $i = 0;
        foreach ($fx['keys'] as $k) {
            foreach ($fx['numbers'] as $n) {
                foreach (array(true, false) as $wo) {
                    foreach (array(true, false) as $fut) {
                        $got = $call(function () use ($locale, $n, $wo, $k, $fut) {
                            return $locale->relativeTime($n, $wo, $k, $fut);
                        });
                        $want = $fx['relativeTime'][$i++];
                        $rec('fn-relativeTime', $got === $want, "relativeTime($n, " . json_encode($wo) . ", '$k', " . json_encode($fut) . '): got ' . self::js($got) . ' want ' . self::js($want));
                    }
                }
            }
        }
        foreach (array(-1000, -1, 0, 1, 1000) as $i => $diff) {
            $got = $call(function () use ($locale, $diff) {
                return $locale->pastFuture($diff, 'X');
            });
            $rec('fn-pastFuture', $got === $fx['pastFuture'][$i], "pastFuture($diff): got " . self::js($got) . ' want ' . self::js($fx['pastFuture'][$i]));
        }

        $i = 0;
        $now = Moment::createUtc(Calendar::utcMs(2024, 5, 15, 10, 0, 0))->locale($name);
        for ($wd = 0; $wd < 7; $wd++) {
            $mom = Moment::createUtc(Calendar::utcMs(2024, 5, 9 + $wd, 12, 0, 0))->locale($name);
            foreach ($fx['calKeys'] as $k) {
                $got = $call(function () use ($locale, $k, $mom, $now) {
                    return $locale->calendar($k, $mom, $now);
                });
                $rec('fn-calendar', $got === $fx['calendar'][$i], "calendar('$k') on weekday " . $mom->day() . ': got ' . self::js($got) . ' want ' . self::js($fx['calendar'][$i]));
                $i++;
            }
        }

        $i = 0;
        foreach ($fx['periods'] as $p) {
            foreach ($fx['ordNumbers'] as $n) {
                $got = $call(function () use ($locale, $n, $p) {
                    return $locale->ordinal($n, $p);
                });
                $rec('fn-ordinal', $got === $fx['ordinal'][$i], "ordinal($n, '$p'): got " . self::js($got) . ' want ' . self::js($fx['ordinal'][$i]));
                $i++;
            }
        }

        $i = 0;
        for ($h = 0; $h < 24; $h++) {
            foreach (array(0, 30, 59) as $mi) {
                foreach (array(true, false) as $lower) {
                    $got = $call(function () use ($locale, $h, $mi, $lower) {
                        return $locale->meridiem($h, $mi, $lower);
                    });
                    $rec('fn-meridiem', $got === $fx['meridiem'][$i], "meridiem($h, $mi, " . json_encode($lower) . '): got ' . self::js($got) . ' want ' . self::js($fx['meridiem'][$i]));
                    $i++;
                }
            }
        }
        foreach ($fx['isPMInputs'] as $i => $text) {
            $got = $call(function () use ($locale, $text) {
                return $locale->isPM($text);
            });
            $rec('fn-isPM', $got === $fx['isPM'][$i], 'isPM(' . self::js($text) . '): got ' . self::js($got) . ' want ' . self::js($fx['isPM'][$i]));
        }
        $rec('fn-hasMeridiemHour', $locale->hasMeridiemHour() === $fx['hasMeridiemHour'], 'hasMeridiemHour: got ' . self::js($locale->hasMeridiemHour()) . ' want ' . self::js($fx['hasMeridiemHour']));
        if ($fx['hasMeridiemHour']) {
            $i = 0;
            foreach ($fx['meridiemWords'] as $w) {
                foreach (array(0, 1, 2, 5, 6, 11, 12, 13) as $hh) {
                    $got = $call(function () use ($locale, $hh, $w) {
                        return $locale->meridiemHour($hh, $w);
                    });
                    $want = $fx['meridiemHour'][$i++];
                    $rec('fn-meridiemHour', $got === $want, "meridiemHour($hh, " . self::js($w) . '): got ' . self::js($got) . ' want ' . self::js($want));
                }
            }
        }

        $i = 0;
        for ($mo = 0; $mo < 12; $mo++) {
            $mm = Moment::createUtc(Calendar::utcMs(2024, $mo, 10, 0, 0, 0))->locale($name);
            foreach ($fx['fmts'] as $fmt) {
                $got = $call(function () use ($locale, $mm, $fmt) {
                    return $locale->months($mm, $fmt);
                });
                $rec('fn-names', $got === $fx['names'][$i], "months(#$mo, '$fmt'): got " . self::js($got) . ' want ' . self::js($fx['names'][$i]));
                $i++;
                $got = $call(function () use ($locale, $mm, $fmt) {
                    return $locale->monthsShort($mm, $fmt);
                });
                $rec('fn-names', $got === $fx['names'][$i], "monthsShort(#$mo, '$fmt'): got " . self::js($got) . ' want ' . self::js($fx['names'][$i]));
                $i++;
            }
        }
        for ($wd = 0; $wd < 7; $wd++) {
            $wm = Moment::createUtc(Calendar::utcMs(2024, 5, 9 + $wd, 0, 0, 0))->locale($name);
            foreach ($fx['fmts'] as $fmt) {
                foreach (array('weekdays', 'weekdaysShort', 'weekdaysMin') as $fnName) {
                    $got = $call(function () use ($locale, $wm, $fmt, $fnName) {
                        return $fnName === 'weekdays' ? $locale->weekdays($wm, $fmt) : $locale->$fnName($wm);
                    });
                    $rec('fn-names', $got === $fx['names'][$i], "$fnName(weekday $wd, '$fmt'): got " . self::js($got) . ' want ' . self::js($fx['names'][$i]));
                    $i++;
                }
            }
        }
        $lists = array($locale->months(), $locale->monthsShort(), $locale->weekdays(), $locale->weekdaysShort(), $locale->weekdaysMin(), $locale->weekdays(true), $locale->weekdaysShort(true), $locale->weekdaysMin(true));
        foreach ($lists as $i => $list) {
            $rec('fn-lists', $list === $fx['lists'][$i], "list #$i: got " . self::js($list) . ' want ' . self::js($fx['lists'][$i]));
        }

        foreach ($fx['texts'] as $i => $text) {
            $got = $call(function () use ($locale, $text) {
                return $locale->preparse($text);
            });
            $rec('fn-preparse', $got === $fx['preparse'][$i], 'preparse(' . self::js($text) . '): got ' . self::js($got) . ' want ' . self::js($fx['preparse'][$i]));
            $got = $call(function () use ($locale, $text) {
                return $locale->postformat($text);
            });
            $rec('fn-postformat', $got === $fx['postformat'][$i], 'postformat(' . self::js($text) . '): got ' . self::js($got) . ' want ' . self::js($fx['postformat'][$i]));
        }
    }
}
