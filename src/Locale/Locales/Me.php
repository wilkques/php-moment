<?php

namespace Wilkques\Moment\Locale\Locales;

use Wilkques\Moment\Locale\Locale;

class Me extends Locale
{
    public $abbr = 'me';

    private static $words = array(
        'ss' => array('sekund', 'sekunda', 'sekundi'),
        'm' => array('jedan minut', 'jednog minuta'),
        'mm' => array('minut', 'minuta', 'minuta'),
        'h' => array('jedan sat', 'jednog sata'),
        'hh' => array('sat', 'sata', 'sati'),
        'dd' => array('dan', 'dana', 'dana'),
        'MM' => array('mjesec', 'mjeseca', 'mjeseci'),
        'yy' => array('godina', 'godine', 'godina'),
    );

    public static function config()
    {
        return array(
            'months' => array('januar', 'februar', 'mart', 'april', 'maj', 'jun', 'jul', 'avgust', 'septembar', 'oktobar', 'novembar', 'decembar'),
            'monthsShort' => array('jan.', 'feb.', 'mar.', 'apr.', 'maj', 'jun', 'jul', 'avg.', 'sep.', 'okt.', 'nov.', 'dec.'),
            'monthsParseExact' => true,
            'weekdays' => array('nedjelja', 'ponedjeljak', 'utorak', 'srijeda', 'četvrtak', 'petak', 'subota'),
            'weekdaysShort' => array('ned.', 'pon.', 'uto.', 'sri.', 'čet.', 'pet.', 'sub.'),
            'weekdaysMin' => array('ne', 'po', 'ut', 'sr', 'če', 'pe', 'su'),
            'weekdaysParseExact' => true,
            'longDateFormat' => array(
                'LT' => 'H:mm', 'LTS' => 'H:mm:ss', 'L' => 'DD.MM.YYYY', 'LL' => 'D. MMMM YYYY',
                'LLL' => 'D. MMMM YYYY H:mm', 'LLLL' => 'dddd, D. MMMM YYYY H:mm',
            ),
            'calendar' => array(
                'sameDay' => '[danas u] LT', 'nextDay' => '[sjutra u] LT', 'nextWeek' => '',
                'lastDay' => '[juče u] LT', 'lastWeek' => '', 'sameElse' => 'L',
            ),
            'relativeTime' => array(
                'future' => 'za %s', 'past' => 'prije %s', 's' => 'nekoliko sekundi', 'ss' => '',
                'm' => '', 'mm' => '', 'h' => '', 'hh' => '', 'd' => 'dan', 'dd' => '',
                'M' => 'mjesec', 'MM' => '', 'y' => 'godinu', 'yy' => '',
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
            $days = array(
                '[prošle] [nedjelje] [u] LT',
                '[prošlog] [ponedjeljka] [u] LT',
                '[prošlog] [utorka] [u] LT',
                '[prošle] [srijede] [u] LT',
                '[prošlog] [četvrtka] [u] LT',
                '[prošlog] [petka] [u] LT',
                '[prošle] [subote] [u] LT',
            );

            return $days[$mom->day()];
        }

        return parent::calendar($key, $mom, $now);
    }

    public function relativeTime($number, $withoutSuffix, $key, $isFuture)
    {
        if (!isset(self::$words[$key])) {
            return parent::relativeTime($number, $withoutSuffix, $key, $isFuture);
        }
        $wordKey = self::$words[$key];
        if (strlen($key) === 1) {
            return $withoutSuffix ? $wordKey[0] : $wordKey[1];
        }

        return $number . ' ' . ($number === 1 ? $wordKey[0] : ($number >= 2 && $number <= 4 ? $wordKey[1] : $wordKey[2]));
    }
}
