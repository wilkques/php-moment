<?php

namespace Wilkques\Moment\Locale\Locales;

use Wilkques\Moment\Locale\Locale;

class Lv extends Locale
{
    public $abbr = 'lv';

    private static $units = array(
        'ss' => 'sekundes_sekundēm_sekunde_sekundes',
        'm' => 'minūtes_minūtēm_minūte_minūtes',
        'mm' => 'minūtes_minūtēm_minūte_minūtes',
        'h' => 'stundas_stundām_stunda_stundas',
        'hh' => 'stundas_stundām_stunda_stundas',
        'd' => 'dienas_dienām_diena_dienas',
        'dd' => 'dienas_dienām_diena_dienas',
        'M' => 'mēneša_mēnešiem_mēnesis_mēneši',
        'MM' => 'mēneša_mēnešiem_mēnesis_mēneši',
        'y' => 'gada_gadiem_gads_gadi',
        'yy' => 'gada_gadiem_gads_gadi',
    );

    public static function config()
    {
        return array(
            'months' => explode('_', 'janvāris_februāris_marts_aprīlis_maijs_jūnijs_jūlijs_augusts_septembris_oktobris_novembris_decembris'),
            'monthsShort' => explode('_', 'jan_feb_mar_apr_mai_jūn_jūl_aug_sep_okt_nov_dec'),
            'weekdays' => explode('_', 'svētdiena_pirmdiena_otrdiena_trešdiena_ceturtdiena_piektdiena_sestdiena'),
            'weekdaysShort' => explode('_', 'Sv_P_O_T_C_Pk_S'),
            'weekdaysMin' => explode('_', 'Sv_P_O_T_C_Pk_S'),
            'weekdaysParseExact' => true,
            'longDateFormat' => array(
                'LT' => 'HH:mm', 'LTS' => 'HH:mm:ss', 'L' => 'DD.MM.YYYY.', 'LL' => 'YYYY. [gada] D. MMMM',
                'LLL' => 'YYYY. [gada] D. MMMM, HH:mm', 'LLLL' => 'YYYY. [gada] D. MMMM, dddd, HH:mm',
            ),
            'calendar' => array(
                'sameDay' => '[Šodien pulksten] LT', 'nextDay' => '[Rīt pulksten] LT',
                'nextWeek' => 'dddd [pulksten] LT', 'lastDay' => '[Vakar pulksten] LT',
                'lastWeek' => '[Pagājušā] dddd [pulksten] LT', 'sameElse' => 'L',
            ),
            'relativeTime' => array('future' => 'pēc %s', 'past' => 'pirms %s'),
            'dayOfMonthOrdinalParse' => '~\d{1,2}\.~u',
            'ordinal' => '%d.',
            'week' => array('dow' => 1, 'doy' => 4),
        );
    }

    protected static function form($key, $number, $withoutSuffix)
    {
        $forms = explode('_', self::$units[$key]);
        $one = $number % 10 === 1 && $number % 100 !== 11;
        if ($withoutSuffix) {
            return $one ? $forms[2] : $forms[3];
        }

        return $one ? $forms[0] : $forms[1];
    }

    public function relativeTime($number, $withoutSuffix, $key, $isFuture)
    {
        if ($key === 'w' || $key === 'ww') {
            return parent::relativeTime($number, $withoutSuffix, $key, $isFuture); // not set in the JS locale: moment's defaults
        }
        switch ($key) {
            case 's':
                return $withoutSuffix ? 'dažas sekundes' : 'dažām sekundēm';
            case 'ss':
            case 'mm':
            case 'hh':
            case 'dd':
            case 'MM':
            case 'yy':
                return $number . ' ' . self::form($key, $number, $withoutSuffix);
            default:
                return self::form($key, $number, $withoutSuffix);
        }
    }
}
