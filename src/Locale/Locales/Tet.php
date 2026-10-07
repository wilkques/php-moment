<?php

namespace Wilkques\Moment\Locale\Locales;

use Wilkques\Moment\Locale\Locale;

class Tet extends Locale
{
    public $abbr = 'tet';

    public static function config()
    {
        return array(
            'months' => explode('_', 'Janeiru_Fevereiru_Marsu_Abril_Maiu_Juñu_Jullu_Agustu_Setembru_Outubru_Novembru_Dezembru'),
            'monthsShort' => explode('_', 'Jan_Fev_Mar_Abr_Mai_Jun_Jul_Ago_Set_Out_Nov_Dez'),
            'weekdays' => explode('_', 'Domingu_Segunda_Tersa_Kuarta_Kinta_Sesta_Sabadu'),
            'weekdaysShort' => explode('_', 'Dom_Seg_Ters_Kua_Kint_Sest_Sab'),
            'weekdaysMin' => explode('_', 'Do_Seg_Te_Ku_Ki_Ses_Sa'),
            'longDateFormat' => array(
                'LT' => 'HH:mm', 'LTS' => 'HH:mm:ss', 'L' => 'DD/MM/YYYY', 'LL' => 'D MMMM YYYY',
                'LLL' => 'D MMMM YYYY HH:mm', 'LLLL' => 'dddd, D MMMM YYYY HH:mm',
            ),
            'calendar' => array(
                'sameDay' => '[Ohin iha] LT', 'nextDay' => '[Aban iha] LT', 'nextWeek' => 'dddd [iha] LT',
                'lastDay' => '[Horiseik iha] LT', 'lastWeek' => 'dddd [semana kotuk] [iha] LT', 'sameElse' => 'L',
            ),
            'relativeTime' => array(
                'future' => 'iha %s', 'past' => '%s liuba', 's' => 'segundu balun', 'ss' => 'segundu %d',
                'm' => 'minutu ida', 'mm' => 'minutu %d', 'h' => 'oras ida', 'hh' => 'oras %d',
                'd' => 'loron ida', 'dd' => 'loron %d', 'M' => 'fulan ida', 'MM' => 'fulan %d',
                'y' => 'tinan ida', 'yy' => 'tinan %d',
            ),
            'dayOfMonthOrdinalParse' => '~\d{1,2}(st|nd|rd|th)~u',
            'week' => array('dow' => 1, 'doy' => 4),
        );
    }

    public function ordinal($number, $token = '')
    {
        $b = $number % 10;
        $tens = (int) (($number % 100) / 10);

        return $number . ($tens === 1 ? 'th' : ($b === 1 ? 'st' : ($b === 2 ? 'nd' : ($b === 3 ? 'rd' : 'th'))));
    }
}
