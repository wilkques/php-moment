<?php

namespace Wilkques\Moment\Locale\Locales;

use Wilkques\Moment\Locale\Locale;

class Nb extends Locale
{
    public $abbr = 'nb';

    public static function config()
    {
        return array(
            'months' => array('januar', 'februar', 'mars', 'april', 'mai', 'juni', 'juli', 'august', 'september', 'oktober', 'november', 'desember'),
            'monthsShort' => array('jan.', 'feb.', 'mars', 'apr.', 'mai', 'juni', 'juli', 'aug.', 'sep.', 'okt.', 'nov.', 'des.'),
            'monthsParseExact' => true,
            'weekdays' => array('søndag', 'mandag', 'tirsdag', 'onsdag', 'torsdag', 'fredag', 'lørdag'),
            'weekdaysShort' => array('sø.', 'ma.', 'ti.', 'on.', 'to.', 'fr.', 'lø.'),
            'weekdaysMin' => array('sø', 'ma', 'ti', 'on', 'to', 'fr', 'lø'),
            'weekdaysParseExact' => true,
            'longDateFormat' => array(
                'LT' => 'HH:mm', 'LTS' => 'HH:mm:ss', 'L' => 'DD.MM.YYYY', 'LL' => 'D. MMMM YYYY',
                'LLL' => 'D. MMMM YYYY [kl.] HH:mm', 'LLLL' => 'dddd D. MMMM YYYY [kl.] HH:mm',
            ),
            'calendar' => array(
                'sameDay' => '[i dag kl.] LT', 'nextDay' => '[i morgen kl.] LT', 'nextWeek' => 'dddd [kl.] LT',
                'lastDay' => '[i går kl.] LT', 'lastWeek' => '[forrige] dddd [kl.] LT', 'sameElse' => 'L',
            ),
            'relativeTime' => array(
                'future' => 'om %s', 'past' => '%s siden', 's' => 'noen sekunder', 'ss' => '%d sekunder',
                'm' => 'ett minutt', 'mm' => '%d minutter', 'h' => 'én time', 'hh' => '%d timer',
                'd' => 'én dag', 'dd' => '%d dager', 'w' => 'én uke', 'ww' => '%d uker',
                'M' => 'én måned', 'MM' => '%d måneder', 'y' => 'ett år', 'yy' => '%d år',
            ),
            'dayOfMonthOrdinalParse' => '~\d{1,2}\.~u',
            'ordinal' => '%d.',
            'week' => array('dow' => 1, 'doy' => 4),
        );
    }
}
