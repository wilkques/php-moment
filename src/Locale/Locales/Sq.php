<?php

namespace Wilkques\Moment\Locale\Locales;

use Wilkques\Moment\Locale\Locale;

class Sq extends Locale
{
    public $abbr = 'sq';

    public static function config()
    {
        return array(
            'months' => explode('_', 'Janar_Shkurt_Mars_Prill_Maj_Qershor_Korrik_Gusht_Shtator_Tetor_Nëntor_Dhjetor'),
            'monthsShort' => explode('_', 'Jan_Shk_Mar_Pri_Maj_Qer_Kor_Gus_Sht_Tet_Nën_Dhj'),
            'weekdays' => explode('_', 'E Diel_E Hënë_E Martë_E Mërkurë_E Enjte_E Premte_E Shtunë'),
            'weekdaysShort' => explode('_', 'Die_Hën_Mar_Mër_Enj_Pre_Sht'),
            'weekdaysMin' => explode('_', 'D_H_Ma_Më_E_P_Sh'),
            'weekdaysParseExact' => true,
            'meridiemParse' => '~PD|MD~u',
            'longDateFormat' => array(
                'LT' => 'HH:mm', 'LTS' => 'HH:mm:ss', 'L' => 'DD/MM/YYYY', 'LL' => 'D MMMM YYYY',
                'LLL' => 'D MMMM YYYY HH:mm', 'LLLL' => 'dddd, D MMMM YYYY HH:mm',
            ),
            'calendar' => array(
                'sameDay' => '[Sot në] LT', 'nextDay' => '[Nesër në] LT', 'nextWeek' => 'dddd [në] LT',
                'lastDay' => '[Dje në] LT', 'lastWeek' => 'dddd [e kaluar në] LT', 'sameElse' => 'L',
            ),
            'relativeTime' => array(
                'future' => 'në %s', 'past' => '%s më parë', 's' => 'disa sekonda', 'ss' => '%d sekonda',
                'm' => 'një minutë', 'mm' => '%d minuta', 'h' => 'një orë', 'hh' => '%d orë',
                'd' => 'një ditë', 'dd' => '%d ditë', 'M' => 'një muaj', 'MM' => '%d muaj',
                'y' => 'një vit', 'yy' => '%d vite',
            ),
            'dayOfMonthOrdinalParse' => '~\d{1,2}\.~u',
            'ordinal' => '%d.',
            'week' => array('dow' => 1, 'doy' => 4),
        );
    }

    public function isPM($input)
    {
        return substr($input . '', 0, 1) === 'M';
    }

    public function meridiem($hours, $minutes, $isLower)
    {
        return $hours < 12 ? 'PD' : 'MD';
    }
}
