<?php

namespace Wilkques\Moment\Locale\Locales;

use Wilkques\Moment\Locale\Locale;

class Sr extends Locale
{
    public $abbr = 'sr';

    private static $words = array(
        'ss' => array('sekunda', 'sekunde', 'sekundi'),
        'm' => array('jedan minut', 'jednog minuta'),
        'mm' => array('minut', 'minuta', 'minuta'),
        'h' => array('jedan sat', 'jednog sata'),
        'hh' => array('sat', 'sata', 'sati'),
        'd' => array('jedan dan', 'jednog dana'),
        'dd' => array('dan', 'dana', 'dana'),
        'M' => array('jedan mesec', 'jednog meseca'),
        'MM' => array('mesec', 'meseca', 'meseci'),
        'y' => array('jednu godinu', 'jedne godine'),
        'yy' => array('godinu', 'godine', 'godina'),
    );

    public static function config()
    {
        return array(
            'months' => array('januar', 'februar', 'mart', 'april', 'maj', 'jun', 'jul', 'avgust', 'septembar', 'oktobar', 'novembar', 'decembar'),
            'monthsShort' => array('jan.', 'feb.', 'mar.', 'apr.', 'maj', 'jun', 'jul', 'avg.', 'sep.', 'okt.', 'nov.', 'dec.'),
            'monthsParseExact' => true,
            'weekdays' => array('nedelja', 'ponedeljak', 'utorak', 'sreda', 'četvrtak', 'petak', 'subota'),
            'weekdaysShort' => array('ned.', 'pon.', 'uto.', 'sre.', 'čet.', 'pet.', 'sub.'),
            'weekdaysMin' => array('ne', 'po', 'ut', 'sr', 'če', 'pe', 'su'),
            'weekdaysParseExact' => true,
            'longDateFormat' => array(
                'LT' => 'H:mm', 'LTS' => 'H:mm:ss', 'L' => 'D. M. YYYY.',
                'LL' => 'D. MMMM YYYY.', 'LLL' => 'D. MMMM YYYY. H:mm', 'LLLL' => 'dddd, D. MMMM YYYY. H:mm',
            ),
            'calendar' => array(
                'sameDay' => '[danas u] LT', 'nextDay' => '[sutra u] LT', 'lastDay' => '[juče u] LT', 'sameElse' => 'L',
            ),
            'relativeTime' => array('future' => 'za %s', 'past' => 'pre %s', 's' => 'nekoliko sekundi'),
            'dayOfMonthOrdinalParse' => '~\d{1,2}\.~u',
            'ordinal' => '%d.',
            'week' => array('dow' => 1, 'doy' => 7),
        );
    }

    public function relativeTime($number, $withoutSuffix, $key, $isFuture)
    {
        if (!isset(self::$words[$key])) {
            return parent::relativeTime($number, $withoutSuffix, $key, $isFuture);
        }
        $wordKey = self::$words[$key];

        if (strlen($key) === 1) {
            if ($key === 'y' && $withoutSuffix) {
                return 'jedna godina';
            }

            return $isFuture || $withoutSuffix ? $wordKey[0] : $wordKey[1];
        }

        $word = $this->correctGrammaticalCase($number, $wordKey);
        if ($key === 'yy' && $withoutSuffix && $word === 'godinu') {
            return $number . ' godina';
        }

        return $number . ' ' . $word;
    }

    protected function correctGrammaticalCase($number, $wordKey)
    {
        if ($number % 10 >= 1 && $number % 10 <= 4 && ($number % 100 < 10 || $number % 100 >= 20)) {
            return $number % 10 === 1 ? $wordKey[0] : $wordKey[1];
        }

        return $wordKey[2];
    }

    public function calendar($key, $mom = null, $now = null)
    {
        if ($key === 'nextWeek') {
            switch ($mom->day()) {
                case 0:
                    return '[u] [nedelju] [u] LT';
                case 3:
                    return '[u] [sredu] [u] LT';
                case 6:
                    return '[u] [subotu] [u] LT';
                default:
                    return '[u] dddd [u] LT';
            }
        } elseif ($key === 'lastWeek') {
            $days = array(
                '[prošle] [nedelje] [u] LT',
                '[prošlog] [ponedeljka] [u] LT',
                '[prošlog] [utorka] [u] LT',
                '[prošle] [srede] [u] LT',
                '[prošlog] [četvrtka] [u] LT',
                '[prošlog] [petka] [u] LT',
                '[prošle] [subote] [u] LT',
            );

            return $days[$mom->day()];
        }

        return parent::calendar($key, $mom, $now);
    }
}
