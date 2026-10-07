<?php

namespace Wilkques\Moment\Locale\Locales;

use Wilkques\Moment\Locale\Locale;

class Se extends Locale
{
    public $abbr = 'se';

    public static function config()
    {
        return array(
            'months' => explode('_', 'ođđajagemánnu_guovvamánnu_njukčamánnu_cuoŋománnu_miessemánnu_geassemánnu_suoidnemánnu_borgemánnu_čakčamánnu_golggotmánnu_skábmamánnu_juovlamánnu'),
            'monthsShort' => explode('_', 'ođđj_guov_njuk_cuo_mies_geas_suoi_borg_čakč_golg_skáb_juov'),
            'weekdays' => explode('_', 'sotnabeaivi_vuossárga_maŋŋebárga_gaskavahkku_duorastat_bearjadat_lávvardat'),
            'weekdaysShort' => explode('_', 'sotn_vuos_maŋ_gask_duor_bear_láv'),
            'weekdaysMin' => explode('_', 's_v_m_g_d_b_L'),
            'longDateFormat' => array(
                'LT' => 'HH:mm', 'LTS' => 'HH:mm:ss', 'L' => 'DD.MM.YYYY', 'LL' => 'MMMM D. [b.] YYYY',
                'LLL' => 'MMMM D. [b.] YYYY [ti.] HH:mm', 'LLLL' => 'dddd, MMMM D. [b.] YYYY [ti.] HH:mm',
            ),
            'calendar' => array(
                'sameDay' => '[otne ti] LT', 'nextDay' => '[ihttin ti] LT', 'nextWeek' => 'dddd [ti] LT',
                'lastDay' => '[ikte ti] LT', 'lastWeek' => '[ovddit] dddd [ti] LT', 'sameElse' => 'L',
            ),
            'relativeTime' => array(
                'future' => '%s geažes', 'past' => 'maŋit %s', 's' => 'moadde sekunddat', 'ss' => '%d sekunddat',
                'm' => 'okta minuhta', 'mm' => '%d minuhtat', 'h' => 'okta diimmu', 'hh' => '%d diimmut',
                'd' => 'okta beaivi', 'dd' => '%d beaivvit', 'M' => 'okta mánnu', 'MM' => '%d mánut',
                'y' => 'okta jahki', 'yy' => '%d jagit',
            ),
            'dayOfMonthOrdinalParse' => '~\d{1,2}\.~u',
            'ordinal' => '%d.',
            'week' => array('dow' => 1, 'doy' => 4),
        );
    }
}
