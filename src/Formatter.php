<?php

namespace Wilkques\Moment;

use Wilkques\Moment\Locale\Locale;

class Formatter
{
    const TOKENS = '/(\[[^\[]*\])|(\\\\)?([Hh]mm(ss)?|Mo|MM?M?M?|Do|DDDo|DD?D?D?|ddd?d?|do?|w[o|w]?|W[o|W]?|Qo?|N{1,5}|YYYYYY|YYYYY|YYYY|YY|y{2,4}|yo?|gg(ggg?)?|GG(GGG?)?|e|E|a|A|hh?|HH?|kk?|mm?|ss?|S{1,9}|x|X|zz?|ZZ?|.)/u';

    const LOCAL_TOKENS = '/(\[[^\[]*\])|(\\\\)?(LTS|LT|LL?L?L?|l{1,4})/u';

    public static function zeroFill($number, $length, $forceSign = false)
    {
        $abs = Calendar::str(abs($number));
        $sign = $number >= 0;

        return ($sign ? ($forceSign ? '+' : '') : '-') . str_pad($abs, $length, '0', STR_PAD_LEFT);
    }

    public static function expand($format, Locale $locale)
    {
        for ($i = 5; $i >= 0; $i--) {
            $replaced = preg_replace_callback(self::LOCAL_TOKENS, function ($m) use ($locale) {
                if ($m[1] !== '') {
                    return $m[0];
                }
                $long = $locale->longDateFormat($m[0]);

                return $long === null || $long === '' ? $m[0] : $long;
            }, $format);
            if ($replaced === $format) {
                break;
            }
            $format = $replaced;
        }

        return $format;
    }

    /** @param bool $postformat false for toISOString(), which JS builds without the locale's digit mapping */
    public static function format(Moment $m, $format, $postformat = true)
    {
        $locale = $m->getLocaleData();
        if (!$m->isValid()) {
            return $locale->invalidDate();
        }

        $format = self::expand($format, $locale);
        preg_match_all(self::TOKENS, $format, $matches);

        $out = '';
        foreach ($matches[0] as $token) {
            $piece = self::token($m, $token, $locale, $format);
            if ($piece === null) {
                $piece = preg_match('/\[[\s\S]/', $token)
                    ? preg_replace('/^\[|\]$/', '', $token)
                    : str_replace('\\', '', $token);
            }
            $out .= $piece;
        }

        return $postformat ? $locale->postformat($out) : $out;
    }

    /**
     * @param string $format the whole (expanded) format string: locales choose between the "format" and
     *                       "standalone" forms of months and weekdays by looking at it
     * @return string|null null when the token is not a format token (so it is literal text)
     */
    protected static function token(Moment $m, $token, Locale $locale, $format)
    {
        switch ($token) {
            case 'M': return (string) ($m->month() + 1);
            case 'MM': return self::zeroFill($m->month() + 1, 2);
            case 'Mo': return $locale->ordinal($m->month() + 1, 'M');
            case 'MMM': return $locale->monthsShort($m, $format);
            case 'MMMM': return $locale->months($m, $format);

            case 'D': return (string) $m->date();
            case 'DD': return self::zeroFill($m->date(), 2);
            case 'Do': return $locale->ordinal($m->date(), 'D');
            case 'DDD': return (string) $m->dayOfYear();
            case 'DDDD': return self::zeroFill($m->dayOfYear(), 3);
            case 'DDDo': return $locale->ordinal($m->dayOfYear(), 'DDD');

            case 'd': return (string) $m->day();
            case 'do': return $locale->ordinal($m->day(), 'd');
            case 'dd': return $locale->weekdaysMin($m);
            case 'ddd': return $locale->weekdaysShort($m);
            case 'dddd': return $locale->weekdays($m, $format);
            case 'e': return (string) $m->weekday();
            case 'E': return (string) $m->isoWeekday();

            case 'w': return (string) $m->week();
            case 'ww': return self::zeroFill($m->week(), 2);
            case 'wo': return $locale->ordinal($m->week(), 'w');
            case 'W': return (string) $m->isoWeek();
            case 'WW': return self::zeroFill($m->isoWeek(), 2);
            case 'Wo': return $locale->ordinal($m->isoWeek(), 'W');

            case 'Y':
                $y = $m->year();

                return $y <= 9999 ? self::zeroFill($y, 4) : '+' . $y;
            case 'YY': return self::zeroFill($m->year() % 100, 2);
            case 'YYYY': return self::zeroFill($m->year(), 4);
            case 'YYYYY': return self::zeroFill($m->year(), 5);
            case 'YYYYYY': return self::zeroFill($m->year(), 6, true);

            case 'gg': return self::zeroFill($m->weekYear() % 100, 2);
            case 'gggg': return self::zeroFill($m->weekYear(), 4);
            case 'ggggg': return self::zeroFill($m->weekYear(), 5);
            case 'GG': return self::zeroFill($m->isoWeekYear() % 100, 2);
            case 'GGGG': return self::zeroFill($m->isoWeekYear(), 4);
            case 'GGGGG': return self::zeroFill($m->isoWeekYear(), 5);

            case 'Q': return (string) $m->quarter();
            case 'Qo': return $locale->ordinal($m->quarter(), 'Q');

            case 'H': return (string) $m->hour();
            case 'HH': return self::zeroFill($m->hour(), 2);
            case 'h': return (string) ($m->hour() % 12 ?: 12);
            case 'hh': return self::zeroFill($m->hour() % 12 ?: 12, 2);
            case 'k': return (string) ($m->hour() ?: 24);
            case 'kk': return self::zeroFill($m->hour() ?: 24, 2);
            case 'hmm': return ($m->hour() % 12 ?: 12) . self::zeroFill($m->minute(), 2);
            case 'hmmss': return ($m->hour() % 12 ?: 12) . self::zeroFill($m->minute(), 2) . self::zeroFill($m->second(), 2);
            case 'Hmm': return $m->hour() . self::zeroFill($m->minute(), 2);
            case 'Hmmss': return $m->hour() . self::zeroFill($m->minute(), 2) . self::zeroFill($m->second(), 2);
            case 'a': return $locale->meridiem($m->hour(), $m->minute(), true);
            case 'A': return $locale->meridiem($m->hour(), $m->minute(), false);

            case 'm': return (string) $m->minute();
            case 'mm': return self::zeroFill($m->minute(), 2);
            case 's': return (string) $m->second();
            case 'ss': return self::zeroFill($m->second(), 2);

            case 'S': return (string) (int) ($m->millisecond() / 100);
            case 'SS': return self::zeroFill((int) ($m->millisecond() / 10), 2);
            case 'SSS': return self::zeroFill($m->millisecond(), 3);
            case 'SSSS': return self::zeroFill($m->millisecond() * 10, 4);
            case 'SSSSS': return self::zeroFill($m->millisecond() * 100, 5);
            case 'SSSSSS': return self::zeroFill($m->millisecond() * 1000, 6);
            case 'SSSSSSS': return self::zeroFill($m->millisecond() * 10000, 7);
            case 'SSSSSSSS': return self::zeroFill($m->millisecond() * 100000, 8);
            case 'SSSSSSSSS': return self::zeroFill($m->millisecond() * 1000000, 9);

            case 'X': return Calendar::str($m->unixSeconds());
            case 'x': return Calendar::str($m->valueOf());

            case 'Z': return self::offset($m->utcOffset(), ':');
            case 'ZZ': return self::offset($m->utcOffset(), '');
            case 'z': return $m->zoneAbbr();
            case 'zz': return $m->zoneName();

            case 'N':
            case 'NN':
            case 'NNN': return self::era($m, 'abbr');
            case 'NNNN': return self::era($m, 'name');
            case 'NNNNN': return self::era($m, 'narrow');

            case 'y': return (string) self::eraYear($m);
            case 'yy': return self::zeroFill(self::eraYear($m), 2);
            case 'yyy': return self::zeroFill(self::eraYear($m), 3);
            case 'yyyy': return self::zeroFill(self::eraYear($m), 4);
            case 'yo': return $locale->ordinal(self::eraYear($m), 'y');
        }

        return null;
    }

    protected static function offset($offset, $separator)
    {
        $sign = '+';
        if ($offset < 0) {
            $offset = -$offset;
            $sign = '-';
        }

        return $sign . self::zeroFill((int) ($offset / 60), 2) . $separator . self::zeroFill((int) $offset % 60, 2);
    }

    /** The era whose date range contains the moment's start of day, or null. */
    protected static function eraFor(Moment $m)
    {
        if (!$m->isValid()) {
            return null; // (NaN comparisons are true on PHP 5)
        }
        $val = $m->copy()->startOf('day')->valueOf();
        foreach ($m->getLocaleData()->eras() as $era) {
            if (($era['since'] <= $val && $val <= $era['until']) || ($era['until'] <= $val && $val <= $era['since'])) {
                return $era;
            }
        }

        return null;
    }

    public static function era(Moment $m, $field)
    {
        $era = self::eraFor($m);

        return $era ? $era[$field] : '';
    }

    public static function eraYear(Moment $m)
    {
        if (!$m->isValid()) {
            return NAN;
        }
        $val = $m->copy()->startOf('day')->valueOf();
        foreach ($m->getLocaleData()->eras() as $era) {
            $dir = $era['since'] <= $era['until'] ? 1 : -1;
            if (($era['since'] <= $val && $val <= $era['until']) || ($era['until'] <= $val && $val <= $era['since'])) {
                $sinceFields = Calendar::fields($era['since']);
                $sinceYear = $sinceFields[0];

                return ($m->year() - $sinceYear) * $dir + $era['offset'];
            }
        }

        return $m->year();
    }
}
