<?php

namespace Wilkques\Moment\Locale\Locales;

use Wilkques\Moment\Locale\Locale;

class Tk extends Locale
{
    public $abbr = 'tk';

    private static $suffixes = array(
        1 => "'inji", 5 => "'inji", 8 => "'inji", 70 => "'inji", 80 => "'inji",
        2 => "'nji", 7 => "'nji", 20 => "'nji", 50 => "'nji",
        3 => "'ünji", 4 => "'ünji", 100 => "'ünji",
        6 => "'njy", 9 => "'unjy", 10 => "'unjy", 30 => "'unjy", 60 => "'ynjy", 90 => "'ynjy",
    );

    public static function config()
    {
        return array(
            'months' => explode('_', 'Ýanwar_Fewral_Mart_Aprel_Maý_Iýun_Iýul_Awgust_Sentýabr_Oktýabr_Noýabr_Dekabr'),
            'monthsShort' => explode('_', 'Ýan_Few_Mar_Apr_Maý_Iýn_Iýl_Awg_Sen_Okt_Noý_Dek'),
            'weekdays' => explode('_', 'Ýekşenbe_Duşenbe_Sişenbe_Çarşenbe_Penşenbe_Anna_Şenbe'),
            'weekdaysShort' => explode('_', 'Ýek_Duş_Siş_Çar_Pen_Ann_Şen'),
            'weekdaysMin' => explode('_', 'Ýk_Dş_Sş_Çr_Pn_An_Şn'),
            'longDateFormat' => array(
                'LT' => 'HH:mm', 'LTS' => 'HH:mm:ss', 'L' => 'DD.MM.YYYY', 'LL' => 'D MMMM YYYY',
                'LLL' => 'D MMMM YYYY HH:mm', 'LLLL' => 'dddd, D MMMM YYYY HH:mm',
            ),
            'calendar' => array(
                'sameDay' => '[bugün sagat] LT', 'nextDay' => '[ertir sagat] LT', 'nextWeek' => '[indiki] dddd [sagat] LT',
                'lastDay' => '[düýn] LT', 'lastWeek' => '[geçen] dddd [sagat] LT', 'sameElse' => 'L',
            ),
            'relativeTime' => array(
                'future' => '%s soň', 'past' => '%s öň',
                's' => 'birnäçe sekunt', 'm' => 'bir minut', 'mm' => '%d minut',
                'h' => 'bir sagat', 'hh' => '%d sagat', 'd' => 'bir gün', 'dd' => '%d gün',
                'M' => 'bir aý', 'MM' => '%d aý', 'y' => 'bir ýyl', 'yy' => '%d ýyl',
            ),
            'week' => array('dow' => 1, 'doy' => 7),
        );
    }

    public function ordinal($number, $token = '')
    {
        switch ($token) {
            case 'd':
            case 'D':
            case 'Do':
            case 'DD':
                return $number;
            default:
                if ($number === 0) {
                    return $number . "'unjy";
                }
                $a = $number % 10;
                $b = ($number % 100) - $a;
                $s = self::$suffixes;
                if (isset($s[$a])) {
                    $suffix = $s[$a];
                } elseif (isset($s[$b])) {
                    $suffix = $s[$b];
                } elseif ($number >= 100) {
                    $suffix = $s[100];
                } else {
                    return 'NaN';
                }

                return $number . $suffix;
        }
    }
}
