<?php

namespace Wilkques\Moment\Locale\Locales;

use Wilkques\Moment\Locale\Locale;

class Bs extends Locale
{
    public $abbr = 'bs';

    public static function config()
    {
        return array(
            'months' => explode('_', 'januar_februar_mart_april_maj_juni_juli_august_septembar_oktobar_novembar_decembar'),
            'monthsShort' => explode('_', 'jan._feb._mar._apr._maj._jun._jul._aug._sep._okt._nov._dec.'),
            'monthsParseExact' => true,
            'weekdays' => explode('_', 'nedjelja_ponedjeljak_utorak_srijeda_četvrtak_petak_subota'),
            'weekdaysShort' => explode('_', 'ned._pon._uto._sri._čet._pet._sub.'),
            'weekdaysMin' => explode('_', 'ne_po_ut_sr_če_pe_su'),
            'weekdaysParseExact' => true,
            'longDateFormat' => array(
                'LT' => 'H:mm',
                'LTS' => 'H:mm:ss',
                'L' => 'DD.MM.YYYY',
                'LL' => 'D. MMMM YYYY',
                'LLL' => 'D. MMMM YYYY H:mm',
                'LLLL' => 'dddd, D. MMMM YYYY H:mm',
            ),
            'calendar' => array(
                'sameDay' => '[danas u] LT',
                'nextDay' => '[sutra u] LT',
                'lastDay' => '[jučer u] LT',
                'sameElse' => 'L',
            ),
            'relativeTime' => array(
                'future' => 'za %s',
                'past' => 'prije %s',
                's' => 'par sekundi',
                'd' => 'dan',
                'M' => 'mjesec',
                'y' => 'godinu',
            ),
            'dayOfMonthOrdinalParse' => '~\d{1,2}\.~u',
            'ordinal' => '%d.',
            'week' => array('dow' => 1, 'doy' => 7),
        );
    }

    public function calendar($key, $mom = null, $now = null)
    {
        if ($key === 'nextWeek') {
            switch ($mom->day()) {
                case 0:
                    return '[u] [nedjelju] [u] LT';
                case 3:
                    return '[u] [srijedu] [u] LT';
                case 6:
                    return '[u] [subotu] [u] LT';
                default:
                    return '[u] dddd [u] LT';
            }
        } elseif ($key === 'lastWeek') {
            switch ($mom->day()) {
                case 0:
                case 3:
                    return '[prošlu] dddd [u] LT';
                case 6:
                    return '[prošle] [subote] [u] LT';
                default:
                    return '[prošli] dddd [u] LT';
            }
        }

        return parent::calendar($key, $mom, $now);
    }

    public function relativeTime($number, $withoutSuffix, $key, $isFuture)
    {
        $result = $number . ' ';
        $few = ($number === 2 || $number === 3 || $number === 4);
        switch ($key) {
            case 'm':
                return $withoutSuffix ? 'jedna minuta' : ($isFuture ? 'jednu minutu' : 'jedne minute');
            case 'ss':
                return $result . ($number === 1 ? 'sekunda' : ($few ? 'sekunde' : 'sekundi'));
            case 'mm':
                return $result . ($few && $number !== 1 ? 'minute' : 'minuta');
            case 'h':
                return 'jedan sat';
            case 'hh':
                return $result . ($number === 1 ? 'sat' : ($few ? 'sata' : 'sati'));
            case 'dd':
                return $result . ($number === 1 ? 'dan' : 'dana');
            case 'MM':
                return $result . ($number === 1 ? 'mjesec' : ($few ? 'mjeseca' : 'mjeseci'));
            case 'yy':
                return $result . ($number === 1 ? 'godina' : ($few ? 'godine' : 'godina'));
        }

        return parent::relativeTime($number, $withoutSuffix, $key, $isFuture);
    }
}
