<?php

namespace Wilkques\Moment\Locale\Locales;

use Wilkques\Moment\Locale\Locale;

class Da extends Locale
{
    public $abbr = 'da';

    public static function config()
    {
        return array(
            'months' => array('januar', 'februar', 'marts', 'april', 'maj', 'juni', 'juli', 'august', 'september', 'oktober', 'november', 'december'),
            'monthsShort' => array('jan', 'feb', 'mar', 'apr', 'maj', 'jun', 'jul', 'aug', 'sep', 'okt', 'nov', 'dec'),
            'weekdays' => array('søndag', 'mandag', 'tirsdag', 'onsdag', 'torsdag', 'fredag', 'lørdag'),
            'weekdaysShort' => array('søn', 'man', 'tir', 'ons', 'tor', 'fre', 'lør'),
            'weekdaysMin' => array('sø', 'ma', 'ti', 'on', 'to', 'fr', 'lø'),
            'longDateFormat' => array(
                'LT' => 'HH:mm', 'LTS' => 'HH:mm:ss', 'L' => 'DD.MM.YYYY', 'LL' => 'D. MMMM YYYY',
                'LLL' => 'D. MMMM YYYY HH:mm', 'LLLL' => 'dddd [d.] D. MMMM YYYY [kl.] HH:mm',
            ),
            'calendar' => array(
                'sameDay' => '[i dag kl.] LT', 'nextDay' => '[i morgen kl.] LT', 'nextWeek' => 'på dddd [kl.] LT',
                'lastDay' => '[i går kl.] LT', 'lastWeek' => '[i] dddd[s kl.] LT', 'sameElse' => 'L',
            ),
            'relativeTime' => array(
                'future' => 'om %s', 'past' => '%s siden', 's' => 'få sekunder', 'ss' => '%d sekunder',
                'm' => 'et minut', 'mm' => '%d minutter', 'h' => 'en time', 'hh' => '%d timer',
                'd' => 'en dag', 'dd' => '%d dage', 'M' => 'en måned', 'MM' => '%d måneder', 'y' => 'et år', 'yy' => '%d år',
            ),
            'dayOfMonthOrdinalParse' => '~\d{1,2}\.~u',
            'ordinal' => '%d.',
            'week' => array('dow' => 1, 'doy' => 4),
        );
    }
}
