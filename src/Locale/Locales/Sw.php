<?php

namespace Wilkques\Moment\Locale\Locales;

use Wilkques\Moment\Locale\Locale;

class Sw extends Locale
{
    public $abbr = 'sw';

    public static function config()
    {
        return array(
            'calendar' => array(
                'sameDay' => '[leo saa] LT',
                'nextDay' => '[kesho saa] LT',
                'nextWeek' => '[wiki ijayo] dddd [saat] LT',
                'lastDay' => '[jana] LT',
                'lastWeek' => '[wiki iliyopita] dddd [saat] LT',
                'sameElse' => 'L',
            ),
            'longDateFormat' => array(
                'LTS' => 'HH:mm:ss',
                'LT' => 'hh:mm A',
                'L' => 'DD.MM.YYYY',
                'LL' => 'D MMMM YYYY',
                'LLL' => 'D MMMM YYYY HH:mm',
                'LLLL' => 'dddd, D MMMM YYYY HH:mm',
            ),
            'ordinal' => '%d',
            'dayOfMonthOrdinalParse' => '~\\d{1,2}~u',
            'relativeTime' => array(
                'future' => '%s baadaye',
                'past' => 'tokea %s',
                's' => 'hivi punde',
                'ss' => 'sekunde %d',
                'm' => 'dakika moja',
                'mm' => 'dakika %d',
                'h' => 'saa limoja',
                'hh' => 'masaa %d',
                'd' => 'siku moja',
                'dd' => 'siku %d',
                'w' => 'a week',
                'ww' => '%d weeks',
                'M' => 'mwezi mmoja',
                'MM' => 'miezi %d',
                'y' => 'mwaka mmoja',
                'yy' => 'miaka %d',
            ),
            'months' => array('Januari', 'Februari', 'Machi', 'Aprili', 'Mei', 'Juni', 'Julai', 'Agosti', 'Septemba', 'Oktoba', 'Novemba', 'Desemba'),
            'monthsShort' => array('Jan', 'Feb', 'Mac', 'Apr', 'Mei', 'Jun', 'Jul', 'Ago', 'Sep', 'Okt', 'Nov', 'Des'),
            'week' => array(
                'dow' => 1,
                'doy' => 7,
            ),
            'weekdays' => array('Jumapili', 'Jumatatu', 'Jumanne', 'Jumatano', 'Alhamisi', 'Ijumaa', 'Jumamosi'),
            'weekdaysMin' => array('J2', 'J3', 'J4', 'J5', 'Al', 'Ij', 'J1'),
            'weekdaysShort' => array('Jpl', 'Jtat', 'Jnne', 'Jtan', 'Alh', 'Ijm', 'Jmos'),
            'weekdaysParseExact' => true,
        );
    }
}
