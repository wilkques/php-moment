<?php

namespace Wilkques\Moment\Locale\Locales;

use Wilkques\Moment\Locale\Locale;

class Ms extends Locale
{
    public $abbr = 'ms';

    public static function config()
    {
        return array(
            'calendar' => array(
                'sameDay' => '[Hari ini pukul] LT',
                'nextDay' => '[Esok pukul] LT',
                'nextWeek' => 'dddd [pukul] LT',
                'lastDay' => '[Kelmarin pukul] LT',
                'lastWeek' => 'dddd [lepas pukul] LT',
                'sameElse' => 'L',
            ),
            'longDateFormat' => array(
                'LTS' => 'HH.mm.ss',
                'LT' => 'HH.mm',
                'L' => 'DD/MM/YYYY',
                'LL' => 'D MMMM YYYY',
                'LLL' => 'D MMMM YYYY [pukul] HH.mm',
                'LLLL' => 'dddd, D MMMM YYYY [pukul] HH.mm',
            ),
            'ordinal' => '%d',
            'dayOfMonthOrdinalParse' => '~\\d{1,2}~u',
            'relativeTime' => array(
                'future' => 'dalam %s',
                'past' => '%s yang lepas',
                's' => 'beberapa saat',
                'ss' => '%d saat',
                'm' => 'seminit',
                'mm' => '%d minit',
                'h' => 'sejam',
                'hh' => '%d jam',
                'd' => 'sehari',
                'dd' => '%d hari',
                'w' => 'a week',
                'ww' => '%d weeks',
                'M' => 'sebulan',
                'MM' => '%d bulan',
                'y' => 'setahun',
                'yy' => '%d tahun',
            ),
            'months' => array('Januari', 'Februari', 'Mac', 'April', 'Mei', 'Jun', 'Julai', 'Ogos', 'September', 'Oktober', 'November', 'Disember'),
            'monthsShort' => array('Jan', 'Feb', 'Mac', 'Apr', 'Mei', 'Jun', 'Jul', 'Ogs', 'Sep', 'Okt', 'Nov', 'Dis'),
            'week' => array(
                'dow' => 1,
                'doy' => 7,
            ),
            'weekdays' => array('Ahad', 'Isnin', 'Selasa', 'Rabu', 'Khamis', 'Jumaat', 'Sabtu'),
            'weekdaysMin' => array('Ah', 'Is', 'Sl', 'Rb', 'Km', 'Jm', 'Sb'),
            'weekdaysShort' => array('Ahd', 'Isn', 'Sel', 'Rab', 'Kha', 'Jum', 'Sab'),
            'meridiemParse' => '~pagi|tengahari|petang|malam~u',
        );
    }

    public function meridiemHour($hour, $meridiem)
    {
        if ($hour === 12) {
            $hour = 0;
        }
        if ($meridiem === 'pagi') {
            return $hour;
        } elseif ($meridiem === 'tengahari') {
            return $hour >= 11 ? $hour : $hour + 12;
        } elseif ($meridiem === 'petang' || $meridiem === 'malam') {
            return $hour + 12;
        }

        return null;
    }

    public function meridiem($hours, $minutes, $isLower)
    {
        if ($hours < 11) {
            return 'pagi';
        } elseif ($hours < 15) {
            return 'tengahari';
        } elseif ($hours < 19) {
            return 'petang';
        }

        return 'malam';
    }
}
