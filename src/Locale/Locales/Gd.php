<?php

namespace Wilkques\Moment\Locale\Locales;

use Wilkques\Moment\Locale\Locale;

class Gd extends Locale
{
    public $abbr = 'gd';

    public static function config()
    {
        return array(
            'months' => array('Am Faoilleach', 'An Gearran', 'Am Màrt', 'An Giblean', 'An Cèitean', 'An t-Ògmhios', 'An t-Iuchar', 'An Lùnastal', 'An t-Sultain', 'An Dàmhair', 'An t-Samhain', 'An Dùbhlachd'),
            'monthsShort' => array('Faoi', 'Gear', 'Màrt', 'Gibl', 'Cèit', 'Ògmh', 'Iuch', 'Lùn', 'Sult', 'Dàmh', 'Samh', 'Dùbh'),
            'monthsParseExact' => true,
            'weekdays' => array('Didòmhnaich', 'Diluain', 'Dimàirt', 'Diciadain', 'Diardaoin', 'Dihaoine', 'Disathairne'),
            'weekdaysShort' => array('Did', 'Dil', 'Dim', 'Dic', 'Dia', 'Dih', 'Dis'),
            'weekdaysMin' => array('Dò', 'Lu', 'Mà', 'Ci', 'Ar', 'Ha', 'Sa'),
            'longDateFormat' => array(
                'LT' => 'HH:mm', 'LTS' => 'HH:mm:ss', 'L' => 'DD/MM/YYYY', 'LL' => 'D MMMM YYYY',
                'LLL' => 'D MMMM YYYY HH:mm', 'LLLL' => 'dddd, D MMMM YYYY HH:mm',
            ),
            'calendar' => array(
                'sameDay' => '[An-diugh aig] LT', 'nextDay' => '[A-màireach aig] LT', 'nextWeek' => 'dddd [aig] LT',
                'lastDay' => '[An-dè aig] LT', 'lastWeek' => 'dddd [seo chaidh] [aig] LT', 'sameElse' => 'L',
            ),
            'relativeTime' => array(
                'future' => 'ann an %s', 'past' => 'bho chionn %s', 's' => 'beagan diogan', 'ss' => '%d diogan',
                'm' => 'mionaid', 'mm' => '%d mionaidean', 'h' => 'uair', 'hh' => '%d uairean',
                'd' => 'latha', 'dd' => '%d latha', 'M' => 'mìos', 'MM' => '%d mìosan', 'y' => 'bliadhna', 'yy' => '%d bliadhna',
            ),
            'dayOfMonthOrdinalParse' => '~\d{1,2}(d|na|mh)~u',
            'week' => array('dow' => 1, 'doy' => 4),
        );
    }

    public function ordinal($number, $token = '')
    {
        return $number . ($number === 1 ? 'd' : ($number % 10 === 2 ? 'na' : 'mh'));
    }
}
