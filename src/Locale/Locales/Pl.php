<?php

namespace Wilkques\Moment\Locale\Locales;

use Wilkques\Moment\Locale\Locale;

class Pl extends Locale
{
    public $abbr = 'pl';

    private static $nominative = array('styczeń', 'luty', 'marzec', 'kwiecień', 'maj', 'czerwiec', 'lipiec', 'sierpień', 'wrzesień', 'październik', 'listopad', 'grudzień');
    private static $subjective = array('stycznia', 'lutego', 'marca', 'kwietnia', 'maja', 'czerwca', 'lipca', 'sierpnia', 'września', 'października', 'listopada', 'grudnia');

    public static function config()
    {
        $parse = array('~^sty~iu', '~^lut~iu', '~^mar~iu', '~^kwi~iu', '~^maj~iu', '~^cze~iu', '~^lip~iu', '~^sie~iu', '~^wrz~iu', '~^paź~iu', '~^lis~iu', '~^gru~iu');

        return array(
            'monthsShort' => explode('_', 'sty_lut_mar_kwi_maj_cze_lip_sie_wrz_paź_lis_gru'),
            'monthsParse' => $parse,
            'longMonthsParse' => $parse,
            'shortMonthsParse' => $parse,
            'weekdays' => explode('_', 'niedziela_poniedziałek_wtorek_środa_czwartek_piątek_sobota'),
            'weekdaysShort' => explode('_', 'ndz_pon_wt_śr_czw_pt_sob'),
            'weekdaysMin' => explode('_', 'Nd_Pn_Wt_Śr_Cz_Pt_So'),
            'longDateFormat' => array(
                'LT' => 'HH:mm', 'LTS' => 'HH:mm:ss', 'L' => 'DD.MM.YYYY', 'LL' => 'D MMMM YYYY',
                'LLL' => 'D MMMM YYYY HH:mm', 'LLLL' => 'dddd, D MMMM YYYY HH:mm',
            ),
            'calendar' => array(
                'sameDay' => '[Dziś o] LT', 'nextDay' => '[Jutro o] LT',
                'lastDay' => '[Wczoraj o] LT', 'sameElse' => 'L',
            ),
            'relativeTime' => array(
                'future' => 'za %s', 'past' => '%s temu',
                's' => 'kilka sekund', 'd' => '1 dzień', 'dd' => '%d dni',
                'w' => 'tydzień', 'M' => 'miesiąc', 'y' => 'rok',
            ),
            'dayOfMonthOrdinalParse' => '~\d{1,2}\.~u',
            'ordinal' => '%d.',
            'week' => array('dow' => 1, 'doy' => 4),
        );
    }

    public function months($m = null, $format = '')
    {
        if ($m === null) {
            return self::$nominative;
        } elseif (preg_match('~D MMMM~u', (string) $format)) {
            return self::$subjective[self::monthIndex($m)];
        }

        return self::$nominative[self::monthIndex($m)];
    }

    protected static function plural($n)
    {
        return $n % 10 < 5 && $n % 10 > 1 && (int) ($n / 10) % 10 !== 1;
    }

    public function calendar($key, $mom = null, $now = null)
    {
        if ($key === 'nextWeek') {
            switch ($mom->day()) {
                case 0:
                    return '[W niedzielę o] LT';
                case 2:
                    return '[We wtorek o] LT';
                case 3:
                    return '[W środę o] LT';
                case 6:
                    return '[W sobotę o] LT';
                default:
                    return '[W] dddd [o] LT';
            }
        } elseif ($key === 'lastWeek') {
            switch ($mom->day()) {
                case 0:
                    return '[W zeszłą niedzielę o] LT';
                case 3:
                    return '[W zeszłą środę o] LT';
                case 6:
                    return '[W zeszłą sobotę o] LT';
                default:
                    return '[W zeszły] dddd [o] LT';
            }
        }

        return parent::calendar($key, $mom, $now);
    }

    public function relativeTime($number, $withoutSuffix, $key, $isFuture)
    {
        $result = $number . ' ';
        $p = self::plural($number);
        switch ($key) {
            case 'ss':
                return $result . ($p ? 'sekundy' : 'sekund');
            case 'm':
                return $withoutSuffix ? 'minuta' : 'minutę';
            case 'mm':
                return $result . ($p ? 'minuty' : 'minut');
            case 'h':
                return $withoutSuffix ? 'godzina' : 'godzinę';
            case 'hh':
                return $result . ($p ? 'godziny' : 'godzin');
            case 'ww':
                return $result . ($p ? 'tygodnie' : 'tygodni');
            case 'MM':
                return $result . ($p ? 'miesiące' : 'miesięcy');
            case 'yy':
                return $result . ($p ? 'lata' : 'lat');
        }

        return parent::relativeTime($number, $withoutSuffix, $key, $isFuture);
    }
}
