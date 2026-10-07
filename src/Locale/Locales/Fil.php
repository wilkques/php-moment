<?php

namespace Wilkques\Moment\Locale\Locales;

use Wilkques\Moment\Locale\Locale;

class Fil extends Locale
{
    public $abbr = 'fil';

    public static function config()
    {
        return array(
            'months' => explode('_', 'Enero_Pebrero_Marso_Abril_Mayo_Hunyo_Hulyo_Agosto_Setyembre_Oktubre_Nobyembre_Disyembre'),
            'monthsShort' => explode('_', 'Ene_Peb_Mar_Abr_May_Hun_Hul_Ago_Set_Okt_Nob_Dis'),
            'weekdays' => explode('_', 'Linggo_Lunes_Martes_Miyerkules_Huwebes_Biyernes_Sabado'),
            'weekdaysShort' => explode('_', 'Lin_Lun_Mar_Miy_Huw_Biy_Sab'),
            'weekdaysMin' => explode('_', 'Li_Lu_Ma_Mi_Hu_Bi_Sab'),
            'longDateFormat' => array(
                'LT' => 'HH:mm', 'LTS' => 'HH:mm:ss', 'L' => 'MM/D/YYYY', 'LL' => 'MMMM D, YYYY',
                'LLL' => 'MMMM D, YYYY HH:mm', 'LLLL' => 'dddd, MMMM DD, YYYY HH:mm',
            ),
            'calendar' => array(
                'sameDay' => 'LT [ngayong araw]', 'nextDay' => '[Bukas ng] LT', 'nextWeek' => 'LT [sa susunod na] dddd',
                'lastDay' => 'LT [kahapon]', 'lastWeek' => 'LT [noong nakaraang] dddd', 'sameElse' => 'L',
            ),
            'relativeTime' => array(
                'future' => 'sa loob ng %s', 'past' => '%s ang nakalipas', 's' => 'ilang segundo', 'ss' => '%d segundo',
                'm' => 'isang minuto', 'mm' => '%d minuto', 'h' => 'isang oras', 'hh' => '%d oras',
                'd' => 'isang araw', 'dd' => '%d araw', 'M' => 'isang buwan', 'MM' => '%d buwan',
                'y' => 'isang taon', 'yy' => '%d taon',
            ),
            'dayOfMonthOrdinalParse' => '~\d{1,2}~u',
            'week' => array('dow' => 1, 'doy' => 4),
        );
    }

    public function ordinal($number, $token = '')
    {
        return $number;
    }
}
