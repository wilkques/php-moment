<?php

namespace Wilkques\Moment\Locale\Locales;

use Wilkques\Moment\Locale\Locale;

class EnIl extends Locale
{
    public $abbr = 'en-il';

    public static function config()
    {
        return array(
            'calendar' => array(
                'sameDay' => '[Today at] LT',
                'nextDay' => '[Tomorrow at] LT',
                'nextWeek' => 'dddd [at] LT',
                'lastDay' => '[Yesterday at] LT',
                'lastWeek' => '[Last] dddd [at] LT',
                'sameElse' => 'L',
            ),
            'longDateFormat' => array(
                'LTS' => 'HH:mm:ss',
                'LT' => 'HH:mm',
                'L' => 'DD/MM/YYYY',
                'LL' => 'D MMMM YYYY',
                'LLL' => 'D MMMM YYYY HH:mm',
                'LLLL' => 'dddd, D MMMM YYYY HH:mm',
            ),
            'dayOfMonthOrdinalParse' => '~\\d{1,2}(st|nd|rd|th)~u',
            'relativeTime' => array(
                'future' => 'in %s',
                'past' => '%s ago',
                's' => 'a few seconds',
                'ss' => '%d seconds',
                'm' => 'a minute',
                'mm' => '%d minutes',
                'h' => 'an hour',
                'hh' => '%d hours',
                'd' => 'a day',
                'dd' => '%d days',
                'w' => 'a week',
                'ww' => '%d weeks',
                'M' => 'a month',
                'MM' => '%d months',
                'y' => 'a year',
                'yy' => '%d years',
            ),
            'week' => array(
                'dow' => 0,
                'doy' => 6,
            ),
        );
    }

    public function ordinal($number, $token = '')
    {
        $b = $number % 10;
        $tens = (int) (($number % 100) / 10);

        return $number . ($tens === 1 ? 'th' : ($b === 1 ? 'st' : ($b === 2 ? 'nd' : ($b === 3 ? 'rd' : 'th'))));
    }
}
