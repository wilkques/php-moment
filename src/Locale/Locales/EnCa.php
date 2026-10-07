<?php

namespace Wilkques\Moment\Locale\Locales;

use Wilkques\Moment\Locale\Locale;

class EnCa extends Locale
{
    public $abbr = 'en-ca';

    public static function config()
    {
        return array(
            'months' => array('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'),
            'monthsShort' => array('Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'),
            'weekdays' => array('Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'),
            'weekdaysShort' => array('Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'),
            'weekdaysMin' => array('Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'),
            'longDateFormat' => array(
                'LT' => 'h:mm A', 'LTS' => 'h:mm:ss A', 'L' => 'YYYY-MM-DD', 'LL' => 'MMMM D, YYYY',
                'LLL' => 'MMMM D, YYYY h:mm A', 'LLLL' => 'dddd, MMMM D, YYYY h:mm A',
            ),
            'calendar' => array(
                'sameDay' => '[Today at] LT', 'nextDay' => '[Tomorrow at] LT', 'nextWeek' => 'dddd [at] LT',
                'lastDay' => '[Yesterday at] LT', 'lastWeek' => '[Last] dddd [at] LT', 'sameElse' => 'L',
            ),
            'relativeTime' => array(
                'future' => 'in %s', 'past' => '%s ago', 's' => 'a few seconds', 'ss' => '%d seconds',
                'm' => 'a minute', 'mm' => '%d minutes', 'h' => 'an hour', 'hh' => '%d hours',
                'd' => 'a day', 'dd' => '%d days', 'M' => 'a month', 'MM' => '%d months',
                'y' => 'a year', 'yy' => '%d years',
            ),
            'dayOfMonthOrdinalParse' => '~\d{1,2}(st|nd|rd|th)~u',
        );
    }

    public function ordinal($number, $token = '')
    {
        $b = $number % 10;
        $tens = (int) (($number % 100) / 10);

        return $number . ($tens === 1 ? 'th' : ($b === 1 ? 'st' : ($b === 2 ? 'nd' : ($b === 3 ? 'rd' : 'th'))));
    }
}
