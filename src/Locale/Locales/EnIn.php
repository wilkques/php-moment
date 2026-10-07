<?php

namespace Wilkques\Moment\Locale\Locales;

use Wilkques\Moment\Locale\Locale;

class EnIn extends Locale
{
    public $abbr = 'en-in';

    public static function config()
    {
        return array(
            'longDateFormat' => array(
                'LT' => 'h:mm A', 'LTS' => 'h:mm:ss A', 'L' => 'DD/MM/YYYY', 'LL' => 'D MMMM YYYY',
                'LLL' => 'D MMMM YYYY h:mm A', 'LLLL' => 'dddd, D MMMM YYYY h:mm A',
            ),
            'relativeTime' => array(
                'future' => 'in %s', 'past' => '%s ago', 's' => 'a few seconds', 'ss' => '%d seconds',
                'm' => 'a minute', 'mm' => '%d minutes', 'h' => 'an hour', 'hh' => '%d hours',
                'd' => 'a day', 'dd' => '%d days', 'M' => 'a month', 'MM' => '%d months',
                'y' => 'a year', 'yy' => '%d years',
            ),
            'dayOfMonthOrdinalParse' => '~\d{1,2}(st|nd|rd|th)~u',
            'week' => array('dow' => 0, 'doy' => 6),
        );
    }

    public function ordinal($number, $token = '')
    {
        $b = $number % 10;
        $tens = (int) (($number % 100) / 10);

        return $number . ($tens === 1 ? 'th' : ($b === 1 ? 'st' : ($b === 2 ? 'nd' : ($b === 3 ? 'rd' : 'th'))));
    }
}
