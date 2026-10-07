<?php

namespace Wilkques\Moment\Locale\Locales;

use Wilkques\Moment\Locale\Locale;

class GomLatn extends Locale
{
    public $abbr = 'gom-latn';

    public static function config()
    {
        return array(
            'months' => array(
                'standalone' => explode('_', 'Janer_Febrer_Mars_Abril_Mai_Jun_Julai_Agost_Setembr_Otubr_Novembr_Dezembr'),
                'format' => explode('_', 'Janerachea_Febrerachea_Marsachea_Abrilachea_Maiachea_Junachea_Julaiachea_Agostachea_Setembrachea_Otubrachea_Novembrachea_Dezembrachea'),
                'isFormat' => '~MMMM(\s)+D[oD]?~u',
            ),
            'monthsShort' => explode('_', 'Jan._Feb._Mars_Abr._Mai_Jun_Jul._Ago._Set._Otu._Nov._Dez.'),
            'monthsParseExact' => true,
            'weekdays' => explode('_', "Aitar_Somar_Mongllar_Budhvar_Birestar_Sukrar_Son'var"),
            'weekdaysShort' => explode('_', 'Ait._Som._Mon._Bud._Bre._Suk._Son.'),
            'weekdaysMin' => explode('_', 'Ai_Sm_Mo_Bu_Br_Su_Sn'),
            'weekdaysParseExact' => true,
            'longDateFormat' => array(
                'LT' => 'A h:mm [vazta]', 'LTS' => 'A h:mm:ss [vazta]', 'L' => 'DD-MM-YYYY', 'LL' => 'D MMMM YYYY',
                'LLL' => 'D MMMM YYYY A h:mm [vazta]', 'LLLL' => 'dddd, MMMM Do, YYYY, A h:mm [vazta]',
                'llll' => 'ddd, D MMM YYYY, A h:mm [vazta]',
            ),
            'calendar' => array(
                'sameDay' => '[Aiz] LT', 'nextDay' => '[Faleam] LT', 'nextWeek' => '[Fuddlo] dddd[,] LT',
                'lastDay' => '[Kal] LT', 'lastWeek' => '[Fattlo] dddd[,] LT', 'sameElse' => 'L',
            ),
            'relativeTime' => array('future' => '%s', 'past' => '%s adim'),
            'dayOfMonthOrdinalParse' => '~\d{1,2}(er)~u',
            'week' => array('dow' => 0, 'doy' => 3),
            'meridiemParse' => '~rati|sokallim|donparam|sanje~u',
        );
    }

    public function relativeTime($number, $withoutSuffix, $key, $isFuture)
    {
        $format = array(
            's' => array('thoddea sekondamni', 'thodde sekond'),
            'ss' => array($number . ' sekondamni', $number . ' sekond'),
            'm' => array('eka mintan', 'ek minut'),
            'mm' => array($number . ' mintamni', $number . ' mintam'),
            'h' => array('eka voran', 'ek vor'),
            'hh' => array($number . ' voramni', $number . ' voram'),
            'd' => array('eka disan', 'ek dis'),
            'dd' => array($number . ' disamni', $number . ' dis'),
            'M' => array('eka mhoinean', 'ek mhoino'),
            'MM' => array($number . ' mhoineamni', $number . ' mhoine'),
            'y' => array('eka vorsan', 'ek voros'),
            'yy' => array($number . ' vorsamni', $number . ' vorsam'),
        );
        if (!isset($format[$key])) {
            return parent::relativeTime($number, $withoutSuffix, $key, $isFuture);
        }

        return $format[$key][$isFuture ? 0 : 1];
    }

    public function ordinal($number, $token = '')
    {
        // the ordinal 'er' only applies to day of the month
        return $token === 'D' ? $number . 'er' : $number;
    }

    public function meridiemHour($hour, $meridiem)
    {
        if ($hour === 12) {
            $hour = 0;
        }
        if ($meridiem === 'rati') {
            return $hour < 4 ? $hour : $hour + 12;
        } elseif ($meridiem === 'sokallim') {
            return $hour;
        } elseif ($meridiem === 'donparam') {
            return $hour > 12 ? $hour : $hour + 12;
        } elseif ($meridiem === 'sanje') {
            return $hour + 12;
        }

        return null;
    }

    public function meridiem($hour, $minute, $isLower)
    {
        if ($hour < 4) {
            return 'rati';
        } elseif ($hour < 12) {
            return 'sokallim';
        } elseif ($hour < 16) {
            return 'donparam';
        } elseif ($hour < 20) {
            return 'sanje';
        }

        return 'rati';
    }
}
