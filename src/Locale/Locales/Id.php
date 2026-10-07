<?php

namespace Wilkques\Moment\Locale\Locales;

use Wilkques\Moment\Locale\Locale;

class Id extends Locale
{
    public $abbr = 'id';

    public static function config()
    {
        return array(
            'months' => explode('_', 'Januari_Februari_Maret_April_Mei_Juni_Juli_Agustus_September_Oktober_November_Desember'),
            'monthsShort' => explode('_', 'Jan_Feb_Mar_Apr_Mei_Jun_Jul_Agt_Sep_Okt_Nov_Des'),
            'weekdays' => explode('_', 'Minggu_Senin_Selasa_Rabu_Kamis_Jumat_Sabtu'),
            'weekdaysShort' => explode('_', 'Min_Sen_Sel_Rab_Kam_Jum_Sab'),
            'weekdaysMin' => explode('_', 'Mg_Sn_Sl_Rb_Km_Jm_Sb'),
            'longDateFormat' => array(
                'LT' => 'HH.mm', 'LTS' => 'HH.mm.ss', 'L' => 'DD/MM/YYYY', 'LL' => 'D MMMM YYYY',
                'LLL' => 'D MMMM YYYY [pukul] HH.mm', 'LLLL' => 'dddd, D MMMM YYYY [pukul] HH.mm',
            ),
            'meridiemParse' => '~pagi|siang|sore|malam~u',
            'calendar' => array(
                'sameDay' => '[Hari ini pukul] LT', 'nextDay' => '[Besok pukul] LT', 'nextWeek' => 'dddd [pukul] LT',
                'lastDay' => '[Kemarin pukul] LT', 'lastWeek' => 'dddd [lalu pukul] LT', 'sameElse' => 'L',
            ),
            'relativeTime' => array(
                'future' => 'dalam %s', 'past' => '%s yang lalu', 's' => 'beberapa detik', 'ss' => '%d detik',
                'm' => 'semenit', 'mm' => '%d menit', 'h' => 'sejam', 'hh' => '%d jam',
                'd' => 'sehari', 'dd' => '%d hari', 'M' => 'sebulan', 'MM' => '%d bulan',
                'y' => 'setahun', 'yy' => '%d tahun',
            ),
            'week' => array('dow' => 0, 'doy' => 6),
        );
    }

    public function meridiemHour($hour, $meridiem)
    {
        if ($hour === 12) {
            $hour = 0;
        }
        if ($meridiem === 'pagi') {
            return $hour;
        } elseif ($meridiem === 'siang') {
            return $hour >= 11 ? $hour : $hour + 12;
        } elseif ($meridiem === 'sore' || $meridiem === 'malam') {
            return $hour + 12;
        }

        return null;
    }

    public function meridiem($hours, $minutes, $isLower)
    {
        if ($hours < 11) {
            return 'pagi';
        } elseif ($hours < 15) {
            return 'siang';
        } elseif ($hours < 19) {
            return 'sore';
        }

        return 'malam';
    }
}
