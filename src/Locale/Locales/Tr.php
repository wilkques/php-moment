<?php

namespace Wilkques\Moment\Locale\Locales;

use Wilkques\Moment\Locale\Locale;

class Tr extends Locale
{
    public $abbr = 'tr';

    private static $suffixes = array(
        1 => "'inci", 5 => "'inci", 8 => "'inci", 70 => "'inci", 80 => "'inci",
        2 => "'nci", 7 => "'nci", 20 => "'nci", 50 => "'nci",
        3 => "'üncü", 4 => "'üncü", 100 => "'üncü",
        6 => "'ncı", 9 => "'uncu", 10 => "'uncu", 30 => "'uncu", 60 => "'ıncı", 90 => "'ıncı",
    );

    public static function config()
    {
        return array(
            'months' => explode('_', 'Ocak_Şubat_Mart_Nisan_Mayıs_Haziran_Temmuz_Ağustos_Eylül_Ekim_Kasım_Aralık'),
            'monthsShort' => explode('_', 'Oca_Şub_Mar_Nis_May_Haz_Tem_Ağu_Eyl_Eki_Kas_Ara'),
            'weekdays' => explode('_', 'Pazar_Pazartesi_Salı_Çarşamba_Perşembe_Cuma_Cumartesi'),
            'weekdaysShort' => explode('_', 'Paz_Pzt_Sal_Çar_Per_Cum_Cmt'),
            'weekdaysMin' => explode('_', 'Pz_Pt_Sa_Ça_Pe_Cu_Ct'),
            'meridiemParse' => '~öö|ÖÖ|ös|ÖS~u',
            'longDateFormat' => array(
                'LT' => 'HH:mm', 'LTS' => 'HH:mm:ss', 'L' => 'DD.MM.YYYY', 'LL' => 'D MMMM YYYY',
                'LLL' => 'D MMMM YYYY HH:mm', 'LLLL' => 'dddd, D MMMM YYYY HH:mm',
            ),
            'calendar' => array(
                'sameDay' => '[bugün saat] LT', 'nextDay' => '[yarın saat] LT', 'nextWeek' => '[gelecek] dddd [saat] LT',
                'lastDay' => '[dün] LT', 'lastWeek' => '[geçen] dddd [saat] LT', 'sameElse' => 'L',
            ),
            'relativeTime' => array(
                'future' => '%s sonra', 'past' => '%s önce',
                's' => 'birkaç saniye', 'ss' => '%d saniye', 'm' => 'bir dakika', 'mm' => '%d dakika',
                'h' => 'bir saat', 'hh' => '%d saat', 'd' => 'bir gün', 'dd' => '%d gün',
                'w' => 'bir hafta', 'ww' => '%d hafta', 'M' => 'bir ay', 'MM' => '%d ay',
                'y' => 'bir yıl', 'yy' => '%d yıl',
            ),
            'week' => array('dow' => 1, 'doy' => 7),
        );
    }

    public function meridiem($hours, $minutes, $isLower)
    {
        if ($hours < 12) {
            return $isLower ? 'öö' : 'ÖÖ';
        }

        return $isLower ? 'ös' : 'ÖS';
    }

    public function isPM($input)
    {
        return $input === 'ös' || $input === 'ÖS';
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
                    return $number . "'ıncı";
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
