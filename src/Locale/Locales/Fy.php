<?php

namespace Wilkques\Moment\Locale\Locales;

use Wilkques\Moment\Locale\Locale;

class Fy extends Locale
{
    public $abbr = 'fy';

    private static $monthsShortWithDots = array('jan.', 'feb.', 'mrt.', 'apr.', 'mai', 'jun.', 'jul.', 'aug.', 'sep.', 'okt.', 'nov.', 'des.');
    private static $monthsShortWithoutDots = array('jan', 'feb', 'mrt', 'apr', 'mai', 'jun', 'jul', 'aug', 'sep', 'okt', 'nov', 'des');

    public static function config()
    {
        return array(
            'months' => array('jannewaris', 'febrewaris', 'maart', 'april', 'maaie', 'juny', 'july', 'augustus', 'septimber', 'oktober', 'novimber', 'desimber'),
            'monthsParseExact' => true,
            'weekdays' => array('snein', 'moandei', 'tiisdei', 'woansdei', 'tongersdei', 'freed', 'sneon'),
            'weekdaysShort' => array('si.', 'mo.', 'ti.', 'wo.', 'to.', 'fr.', 'so.'),
            'weekdaysMin' => array('Si', 'Mo', 'Ti', 'Wo', 'To', 'Fr', 'So'),
            'weekdaysParseExact' => true,
            'longDateFormat' => array(
                'LT' => 'HH:mm', 'LTS' => 'HH:mm:ss', 'L' => 'DD-MM-YYYY', 'LL' => 'D MMMM YYYY',
                'LLL' => 'D MMMM YYYY HH:mm', 'LLLL' => 'dddd D MMMM YYYY HH:mm',
            ),
            'calendar' => array(
                'sameDay' => '[hjoed om] LT', 'nextDay' => '[moarn om] LT', 'nextWeek' => 'dddd [om] LT',
                'lastDay' => '[juster om] LT', 'lastWeek' => '[ôfrûne] dddd [om] LT', 'sameElse' => 'L',
            ),
            'relativeTime' => array(
                'future' => 'oer %s', 'past' => '%s lyn', 's' => 'in pear sekonden', 'ss' => '%d sekonden',
                'm' => 'ien minút', 'mm' => '%d minuten', 'h' => 'ien oere', 'hh' => '%d oeren',
                'd' => 'ien dei', 'dd' => '%d dagen', 'M' => 'ien moanne', 'MM' => '%d moannen', 'y' => 'ien jier', 'yy' => '%d jierren',
            ),
            'dayOfMonthOrdinalParse' => '~\d{1,2}(ste|de)~u',
            'week' => array('dow' => 1, 'doy' => 4),
        );
    }

    public function monthsShort($m = null, $format = '')
    {
        if ($m === null) {
            return self::$monthsShortWithDots;
        }
        $i = self::monthIndex($m);
        if (preg_match('~-MMM-~u', (string) $format)) {
            return self::$monthsShortWithoutDots[$i];
        }

        return self::$monthsShortWithDots[$i];
    }

    public function ordinal($number, $token = '')
    {
        return $number . ($number === 1 || $number === 8 || $number >= 20 ? 'ste' : 'de');
    }
}
