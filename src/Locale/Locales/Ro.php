<?php

namespace Wilkques\Moment\Locale\Locales;

use Wilkques\Moment\Locale\Locale;

class Ro extends Locale
{
    public $abbr = 'ro';

    private static $format = array(
        'ss' => 'secunde', 'mm' => 'minute', 'hh' => 'ore', 'dd' => 'zile',
        'ww' => 'săptămâni', 'MM' => 'luni', 'yy' => 'ani',
    );

    public static function config()
    {
        return array(
            'months' => array('ianuarie', 'februarie', 'martie', 'aprilie', 'mai', 'iunie', 'iulie', 'august', 'septembrie', 'octombrie', 'noiembrie', 'decembrie'),
            'monthsShort' => array('ian.', 'feb.', 'mart.', 'apr.', 'mai', 'iun.', 'iul.', 'aug.', 'sept.', 'oct.', 'nov.', 'dec.'),
            'monthsParseExact' => true,
            'weekdays' => array('duminică', 'luni', 'marți', 'miercuri', 'joi', 'vineri', 'sâmbătă'),
            'weekdaysShort' => array('Dum', 'Lun', 'Mar', 'Mie', 'Joi', 'Vin', 'Sâm'),
            'weekdaysMin' => array('Du', 'Lu', 'Ma', 'Mi', 'Jo', 'Vi', 'Sâ'),
            'longDateFormat' => array(
                'LT' => 'H:mm', 'LTS' => 'H:mm:ss', 'L' => 'DD.MM.YYYY', 'LL' => 'D MMMM YYYY',
                'LLL' => 'D MMMM YYYY H:mm', 'LLLL' => 'dddd, D MMMM YYYY H:mm',
            ),
            'calendar' => array(
                'sameDay' => '[azi la] LT', 'nextDay' => '[mâine la] LT', 'nextWeek' => 'dddd [la] LT',
                'lastDay' => '[ieri la] LT', 'lastWeek' => '[fosta] dddd [la] LT', 'sameElse' => 'L',
            ),
            'relativeTime' => array(
                'future' => 'peste %s', 'past' => '%s în urmă', 's' => 'câteva secunde', 'ss' => '',
                'm' => 'un minut', 'mm' => '', 'h' => 'o oră', 'hh' => '', 'd' => 'o zi', 'dd' => '',
                'w' => 'o săptămână', 'ww' => '', 'M' => 'o lună', 'MM' => '', 'y' => 'un an', 'yy' => '',
            ),
            'week' => array('dow' => 1, 'doy' => 7),
        );
    }

    public function relativeTime($number, $withoutSuffix, $key, $isFuture)
    {
        if (!isset(self::$format[$key])) {
            return parent::relativeTime($number, $withoutSuffix, $key, $isFuture);
        }
        $separator = ' ';
        if ($number % 100 >= 20 || ($number >= 100 && $number % 100 === 0)) {
            $separator = ' de ';
        }

        return $number . $separator . self::$format[$key];
    }
}
