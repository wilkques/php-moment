<?php

namespace Wilkques\Moment\Locale\Locales;

use Wilkques\Moment\Locale\Locale;

class Af extends Locale
{
    public $abbr = 'af';

    public static function config()
    {
        return array(
            'months' => explode('_', 'Januarie_Februarie_Maart_April_Mei_Junie_Julie_Augustus_September_Oktober_November_Desember'),
            'monthsShort' => explode('_', 'Jan_Feb_Mrt_Apr_Mei_Jun_Jul_Aug_Sep_Okt_Nov_Des'),
            'weekdays' => explode('_', 'Sondag_Maandag_Dinsdag_Woensdag_Donderdag_Vrydag_Saterdag'),
            'weekdaysShort' => explode('_', 'Son_Maa_Din_Woe_Don_Vry_Sat'),
            'weekdaysMin' => explode('_', 'So_Ma_Di_Wo_Do_Vr_Sa'),
            'meridiemParse' => '~vm|nm~iu',
            'longDateFormat' => array(
                'LT' => 'HH:mm', 'LTS' => 'HH:mm:ss', 'L' => 'DD/MM/YYYY', 'LL' => 'D MMMM YYYY',
                'LLL' => 'D MMMM YYYY HH:mm', 'LLLL' => 'dddd, D MMMM YYYY HH:mm',
            ),
            'calendar' => array(
                'sameDay' => '[Vandag om] LT', 'nextDay' => '[Môre om] LT', 'nextWeek' => 'dddd [om] LT',
                'lastDay' => '[Gister om] LT', 'lastWeek' => '[Laas] dddd [om] LT', 'sameElse' => 'L',
            ),
            'relativeTime' => array(
                'future' => 'oor %s', 'past' => '%s gelede',
                's' => "'n paar sekondes", 'ss' => '%d sekondes',
                'm' => "'n minuut", 'mm' => '%d minute',
                'h' => "'n uur", 'hh' => '%d ure',
                'd' => "'n dag", 'dd' => '%d dae',
                'M' => "'n maand", 'MM' => '%d maande',
                'y' => "'n jaar", 'yy' => '%d jaar',
            ),
            'dayOfMonthOrdinalParse' => '~\d{1,2}(ste|de)~u',
            'week' => array('dow' => 1, 'doy' => 4),
        );
    }

    public function isPM($input)
    {
        return (bool) preg_match('~^nm$~iu', $input);
    }

    public function meridiem($hours, $minutes, $isLower)
    {
        if ($hours < 12) {
            return $isLower ? 'vm' : 'VM';
        }

        return $isLower ? 'nm' : 'NM';
    }

    public function ordinal($number, $token = '')
    {
        return $number . ($number === 1 || $number === 8 || $number >= 20 ? 'ste' : 'de');
    }
}
