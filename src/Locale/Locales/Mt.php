<?php

namespace Wilkques\Moment\Locale\Locales;

use Wilkques\Moment\Locale\Locale;

class Mt extends Locale
{
    public $abbr = 'mt';

    public static function config()
    {
        return array(
            'months' => explode('_', 'Jannar_Frar_Marzu_April_Mejju_Ġunju_Lulju_Awwissu_Settembru_Ottubru_Novembru_Diċembru'),
            'monthsShort' => explode('_', 'Jan_Fra_Mar_Apr_Mej_Ġun_Lul_Aww_Set_Ott_Nov_Diċ'),
            'weekdays' => explode('_', 'Il-Ħadd_It-Tnejn_It-Tlieta_L-Erbgħa_Il-Ħamis_Il-Ġimgħa_Is-Sibt'),
            'weekdaysShort' => explode('_', 'Ħad_Tne_Tli_Erb_Ħam_Ġim_Sib'),
            'weekdaysMin' => explode('_', 'Ħa_Tn_Tl_Er_Ħa_Ġi_Si'),
            'longDateFormat' => array(
                'LT' => 'HH:mm', 'LTS' => 'HH:mm:ss', 'L' => 'DD/MM/YYYY', 'LL' => 'D MMMM YYYY',
                'LLL' => 'D MMMM YYYY HH:mm', 'LLLL' => 'dddd, D MMMM YYYY HH:mm',
            ),
            'calendar' => array(
                'sameDay' => '[Illum fil-]LT', 'nextDay' => '[Għada fil-]LT', 'nextWeek' => 'dddd [fil-]LT',
                'lastDay' => '[Il-bieraħ fil-]LT', 'lastWeek' => 'dddd [li għadda] [fil-]LT', 'sameElse' => 'L',
            ),
            'relativeTime' => array(
                'future' => 'f’ %s', 'past' => '%s ilu', 's' => 'ftit sekondi', 'ss' => '%d sekondi',
                'm' => 'minuta', 'mm' => '%d minuti', 'h' => 'siegħa', 'hh' => '%d siegħat',
                'd' => 'ġurnata', 'dd' => '%d ġranet', 'M' => 'xahar', 'MM' => '%d xhur',
                'y' => 'sena', 'yy' => '%d sni',
            ),
            'dayOfMonthOrdinalParse' => '~\d{1,2}º~u',
            'ordinal' => '%dº',
            'week' => array('dow' => 1, 'doy' => 4),
        );
    }
}
