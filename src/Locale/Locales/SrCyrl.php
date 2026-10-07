<?php

namespace Wilkques\Moment\Locale\Locales;

use Wilkques\Moment\Locale\Locale;

class SrCyrl extends Locale
{
    public $abbr = 'sr-cyrl';

    public static function config()
    {
        return array(
            'months' => explode('_', 'јануар_фебруар_март_април_мај_јун_јул_август_септембар_октобар_новембар_децембар'),
            'monthsShort' => explode('_', 'јан._феб._мар._апр._мај_јун_јул_авг._сеп._окт._нов._дец.'),
            'monthsParseExact' => true,
            'weekdays' => explode('_', 'недеља_понедељак_уторак_среда_четвртак_петак_субота'),
            'weekdaysShort' => explode('_', 'нед._пон._уто._сре._чет._пет._суб.'),
            'weekdaysMin' => explode('_', 'не_по_ут_ср_че_пе_су'),
            'weekdaysParseExact' => true,
            'longDateFormat' => array(
                'LT' => 'H:mm', 'LTS' => 'H:mm:ss', 'L' => 'D. M. YYYY.', 'LL' => 'D. MMMM YYYY.',
                'LLL' => 'D. MMMM YYYY. H:mm', 'LLLL' => 'dddd, D. MMMM YYYY. H:mm',
            ),
            'calendar' => array(
                'sameDay' => '[данас у] LT', 'nextDay' => '[сутра у] LT', 'lastDay' => '[јуче у] LT', 'sameElse' => 'L',
            ),
            'relativeTime' => array('future' => 'за %s', 'past' => 'пре %s', 's' => 'неколико секунди'),
            'dayOfMonthOrdinalParse' => '~\d{1,2}\.~u',
            'ordinal' => '%d.',
            'week' => array('dow' => 1, 'doy' => 7),
        );
    }

    protected static function words()
    {
        return array(
            'ss' => array('секунда', 'секунде', 'секунди'),
            'm' => array('један минут', 'једног минута'),
            'mm' => array('минут', 'минута', 'минута'),
            'h' => array('један сат', 'једног сата'),
            'hh' => array('сат', 'сата', 'сати'),
            'd' => array('један дан', 'једног дана'),
            'dd' => array('дан', 'дана', 'дана'),
            'M' => array('један месец', 'једног месеца'),
            'MM' => array('месец', 'месеца', 'месеци'),
            'y' => array('једну годину', 'једне године'),
            'yy' => array('годину', 'године', 'година'),
        );
    }

    protected static function correctGrammaticalCase($number, $wordKey)
    {
        if ($number % 10 >= 1 && $number % 10 <= 4 && ($number % 100 < 10 || $number % 100 >= 20)) {
            return $number % 10 === 1 ? $wordKey[0] : $wordKey[1];
        }

        return $wordKey[2];
    }

    public function relativeTime($number, $withoutSuffix, $key, $isFuture)
    {
        if ($key === 'w' || $key === 'ww') {
            return parent::relativeTime($number, $withoutSuffix, $key, $isFuture); // not set in the JS locale: moment's defaults
        }
        if ($key === 's') {
            return parent::relativeTime($number, $withoutSuffix, $key, $isFuture);
        }
        $words = self::words();
        $wordKey = $words[$key];

        if (strlen($key) === 1) {
            if ($key === 'y' && $withoutSuffix) {
                return 'једна година';
            }

            return $isFuture || $withoutSuffix ? $wordKey[0] : $wordKey[1];
        }

        $word = self::correctGrammaticalCase($number, $wordKey);
        if ($key === 'yy' && $withoutSuffix && $word === 'годину') {
            return $number . ' година';
        }

        return $number . ' ' . $word;
    }

    public function calendar($key, $mom = null, $now = null)
    {
        if ($key === 'nextWeek') {
            switch ($mom->day()) {
                case 0:
                    return '[у] [недељу] [у] LT';
                case 3:
                    return '[у] [среду] [у] LT';
                case 6:
                    return '[у] [суботу] [у] LT';
                default:
                    return '[у] dddd [у] LT';
            }
        } elseif ($key === 'lastWeek') {
            $lastWeekDays = array(
                '[прошле] [недеље] [у] LT',
                '[прошлог] [понедељка] [у] LT',
                '[прошлог] [уторка] [у] LT',
                '[прошле] [среде] [у] LT',
                '[прошлог] [четвртка] [у] LT',
                '[прошлог] [петка] [у] LT',
                '[прошле] [суботе] [у] LT',
            );

            return $lastWeekDays[$mom->day()];
        }

        return parent::calendar($key, $mom, $now);
    }
}
