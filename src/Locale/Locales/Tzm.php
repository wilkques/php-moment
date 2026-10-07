<?php

namespace Wilkques\Moment\Locale\Locales;

use Wilkques\Moment\Locale\Locale;

class Tzm extends Locale
{
    public $abbr = 'tzm';

    public static function config()
    {
        $months = explode('_', 'ⵉⵏⵏⴰⵢⵔ_ⴱⵕⴰⵢⵕ_ⵎⴰⵕⵚ_ⵉⴱⵔⵉⵔ_ⵎⴰⵢⵢⵓ_ⵢⵓⵏⵢⵓ_ⵢⵓⵍⵢⵓⵣ_ⵖⵓⵛⵜ_ⵛⵓⵜⴰⵏⴱⵉⵔ_ⴽⵟⵓⴱⵕ_ⵏⵓⵡⴰⵏⴱⵉⵔ_ⴷⵓⵊⵏⴱⵉⵔ');
        $days = explode('_', 'ⴰⵙⴰⵎⴰⵙ_ⴰⵢⵏⴰⵙ_ⴰⵙⵉⵏⴰⵙ_ⴰⴽⵔⴰⵙ_ⴰⴽⵡⴰⵙ_ⴰⵙⵉⵎⵡⴰⵙ_ⴰⵙⵉⴹⵢⴰⵙ');

        return array(
            'months' => $months,
            'monthsShort' => $months,
            'weekdays' => $days,
            'weekdaysShort' => $days,
            'weekdaysMin' => $days,
            'longDateFormat' => array(
                'LT' => 'HH:mm', 'LTS' => 'HH:mm:ss', 'L' => 'DD/MM/YYYY', 'LL' => 'D MMMM YYYY',
                'LLL' => 'D MMMM YYYY HH:mm', 'LLLL' => 'dddd D MMMM YYYY HH:mm',
            ),
            'calendar' => array(
                'sameDay' => '[ⴰⵙⴷⵅ ⴴ] LT', 'nextDay' => '[ⴰⵙⴽⴰ ⴴ] LT', 'nextWeek' => 'dddd [ⴴ] LT',
                'lastDay' => '[ⴰⵚⴰⵏⵜ ⴴ] LT', 'lastWeek' => 'dddd [ⴴ] LT', 'sameElse' => 'L',
            ),
            'relativeTime' => array(
                'future' => 'ⴷⴰⴷⵅ ⵙ ⵢⴰⵏ %s', 'past' => 'ⵢⴰⵏ %s',
                's' => 'ⵉⵎⵉⴽ', 'ss' => '%d ⵉⵎⵉⴽ', 'm' => 'ⵎⵉⵏⵓⴺ', 'mm' => '%d ⵎⵉⵏⵓⴺ',
                'h' => 'ⵙⴰⵄⴰ', 'hh' => '%d ⵜⴰⵙⵙⴰⵄⵉⵏ', 'd' => 'ⴰⵙⵙ', 'dd' => '%d oⵙⵙⴰⵏ',
                'M' => 'ⴰⵢoⵓⵔ', 'MM' => '%d ⵉⵢⵢⵉⵔⵏ', 'y' => 'ⴰⵙⴳⴰⵙ', 'yy' => '%d ⵉⵙⴳⴰⵙⵏ',
            ),
            'week' => array('dow' => 6, 'doy' => 12),
        );
    }
}
