<?php

namespace Wilkques\Moment\Locale\Locales;

use Wilkques\Moment\Locale\Locale;

class Fo extends Locale
{
    public $abbr = 'fo';

    public static function config()
    {
        return array(
            'months' => explode('_', 'januar_februar_mars_apríl_mai_juni_juli_august_september_oktober_november_desember'),
            'monthsShort' => explode('_', 'jan_feb_mar_apr_mai_jun_jul_aug_sep_okt_nov_des'),
            'weekdays' => explode('_', 'sunnudagur_mánadagur_týsdagur_mikudagur_hósdagur_fríggjadagur_leygardagur'),
            'weekdaysShort' => explode('_', 'sun_mán_týs_mik_hós_frí_ley'),
            'weekdaysMin' => explode('_', 'su_má_tý_mi_hó_fr_le'),
            'longDateFormat' => array(
                'LT' => 'HH:mm', 'LTS' => 'HH:mm:ss', 'L' => 'DD/MM/YYYY', 'LL' => 'D MMMM YYYY',
                'LLL' => 'D MMMM YYYY HH:mm', 'LLLL' => 'dddd D. MMMM, YYYY HH:mm',
            ),
            'calendar' => array(
                'sameDay' => '[Í dag kl.] LT', 'nextDay' => '[Í morgin kl.] LT', 'nextWeek' => 'dddd [kl.] LT',
                'lastDay' => '[Í gjár kl.] LT', 'lastWeek' => '[síðstu] dddd [kl] LT', 'sameElse' => 'L',
            ),
            'relativeTime' => array(
                'future' => 'um %s', 'past' => '%s síðani',
                's' => 'fá sekund', 'ss' => '%d sekundir',
                'm' => 'ein minuttur', 'mm' => '%d minuttir',
                'h' => 'ein tími', 'hh' => '%d tímar',
                'd' => 'ein dagur', 'dd' => '%d dagar',
                'M' => 'ein mánaður', 'MM' => '%d mánaðir',
                'y' => 'eitt ár', 'yy' => '%d ár',
            ),
            'dayOfMonthOrdinalParse' => '~\d{1,2}\.~u',
            'ordinal' => '%d.',
            'week' => array('dow' => 1, 'doy' => 4),
        );
    }
}
