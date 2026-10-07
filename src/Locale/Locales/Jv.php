<?php

namespace Wilkques\Moment\Locale\Locales;

use Wilkques\Moment\Locale\Locale;

class Jv extends Locale
{
    public $abbr = 'jv';

    public static function config()
    {
        return array(
            'months' => array('Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'Nopember', 'Desember'),
            'monthsShort' => array('Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nop', 'Des'),
            'weekdays' => array('Minggu', 'Senen', 'Seloso', 'Rebu', 'Kemis', 'Jemuwah', 'Septu'),
            'weekdaysShort' => array('Min', 'Sen', 'Sel', 'Reb', 'Kem', 'Jem', 'Sep'),
            'weekdaysMin' => array('Mg', 'Sn', 'Sl', 'Rb', 'Km', 'Jm', 'Sp'),
            'longDateFormat' => array(
                'LT' => 'HH.mm', 'LTS' => 'HH.mm.ss', 'L' => 'DD/MM/YYYY', 'LL' => 'D MMMM YYYY',
                'LLL' => 'D MMMM YYYY [pukul] HH.mm', 'LLLL' => 'dddd, D MMMM YYYY [pukul] HH.mm',
            ),
            'meridiemParse' => '~enjing|siyang|sonten|ndalu~u',
            'calendar' => array(
                'sameDay' => '[Dinten puniko pukul] LT', 'nextDay' => '[Mbenjang pukul] LT', 'nextWeek' => 'dddd [pukul] LT',
                'lastDay' => '[Kala wingi pukul] LT', 'lastWeek' => 'dddd [kepengker pukul] LT', 'sameElse' => 'L',
            ),
            'relativeTime' => array(
                'future' => 'wonten ing %s', 'past' => '%s ingkang kepengker', 's' => 'sawetawis detik', 'ss' => '%d detik',
                'm' => 'setunggal menit', 'mm' => '%d menit', 'h' => 'setunggal jam', 'hh' => '%d jam',
                'd' => 'sedinten', 'dd' => '%d dinten', 'M' => 'sewulan', 'MM' => '%d wulan', 'y' => 'setaun', 'yy' => '%d taun',
            ),
            'week' => array('dow' => 1, 'doy' => 7),
        );
    }

    public function meridiemHour($hour, $meridiem)
    {
        if ($hour === 12) {
            $hour = 0;
        }
        if ($meridiem === 'enjing') {
            return $hour;
        } elseif ($meridiem === 'siyang') {
            return $hour >= 11 ? $hour : $hour + 12;
        } elseif ($meridiem === 'sonten' || $meridiem === 'ndalu') {
            return $hour + 12;
        }

        return null;
    }

    public function meridiem($hours, $minutes, $isLower)
    {
        if ($hours < 11) {
            return 'enjing';
        } elseif ($hours < 15) {
            return 'siyang';
        } elseif ($hours < 19) {
            return 'sonten';
        }

        return 'ndalu';
    }
}
