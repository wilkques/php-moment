<?php

namespace Wilkques\Moment\Locale\Locales;

use Wilkques\Moment\Locale\Locale;

class Sl extends Locale
{
    public $abbr = 'sl';

    public static function config()
    {
        return array(
            'months' => array('januar', 'februar', 'marec', 'april', 'maj', 'junij', 'julij', 'avgust', 'september', 'oktober', 'november', 'december'),
            'monthsShort' => array('jan.', 'feb.', 'mar.', 'apr.', 'maj.', 'jun.', 'jul.', 'avg.', 'sep.', 'okt.', 'nov.', 'dec.'),
            'monthsParseExact' => true,
            'weekdays' => array('nedelja', 'ponedeljek', 'torek', 'sreda', 'četrtek', 'petek', 'sobota'),
            'weekdaysShort' => array('ned.', 'pon.', 'tor.', 'sre.', 'čet.', 'pet.', 'sob.'),
            'weekdaysMin' => array('ne', 'po', 'to', 'sr', 'če', 'pe', 'so'),
            'weekdaysParseExact' => true,
            'longDateFormat' => array(
                'LT' => 'H:mm', 'LTS' => 'H:mm:ss', 'L' => 'DD. MM. YYYY', 'LL' => 'D. MMMM YYYY',
                'LLL' => 'D. MMMM YYYY H:mm', 'LLLL' => 'dddd, D. MMMM YYYY H:mm',
            ),
            'calendar' => array(
                'sameDay' => '[danes ob] LT', 'nextDay' => '[jutri ob] LT', 'nextWeek' => '',
                'lastDay' => '[včeraj ob] LT', 'lastWeek' => '', 'sameElse' => 'L',
            ),
            'relativeTime' => array(
                'future' => 'čez %s', 'past' => 'pred %s',
                's' => '', 'ss' => '', 'm' => '', 'mm' => '', 'h' => '', 'hh' => '',
                'd' => '', 'dd' => '', 'M' => '', 'MM' => '', 'y' => '', 'yy' => '',
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
                    return '[v] [nedeljo] [ob] LT';
                case 3:
                    return '[v] [sredo] [ob] LT';
                case 6:
                    return '[v] [soboto] [ob] LT';
                default:
                    return '[v] dddd [ob] LT';
            }
        } elseif ($key === 'lastWeek') {
            switch ($mom->day()) {
                case 0:
                    return '[prejšnjo] [nedeljo] [ob] LT';
                case 3:
                    return '[prejšnjo] [sredo] [ob] LT';
                case 6:
                    return '[prejšnjo] [soboto] [ob] LT';
                default:
                    return '[prejšnji] dddd [ob] LT';
            }
        }

        return parent::calendar($key, $mom, $now);
    }

    public function relativeTime($number, $withoutSuffix, $key, $isFuture)
    {
        if ($key === 'w' || $key === 'ww') {
            return parent::relativeTime($number, $withoutSuffix, $key, $isFuture); // not set in the JS locale: moment's defaults
        }
        $result = $number . ' ';
        $ws = $withoutSuffix || $isFuture;
        switch ($key) {
            case 's':
                return $ws ? 'nekaj sekund' : 'nekaj sekundami';
            case 'ss':
                if ($number === 1) {
                    $result .= $withoutSuffix ? 'sekundo' : 'sekundi';
                } elseif ($number === 2) {
                    $result .= $ws ? 'sekundi' : 'sekundah';
                } elseif ($number < 5) {
                    $result .= $ws ? 'sekunde' : 'sekundah';
                } else {
                    $result .= 'sekund';
                }

                return $result;
            case 'm':
                return $withoutSuffix ? 'ena minuta' : 'eno minuto';
            case 'mm':
                if ($number === 1) {
                    $result .= $withoutSuffix ? 'minuta' : 'minuto';
                } elseif ($number === 2) {
                    $result .= $ws ? 'minuti' : 'minutama';
                } elseif ($number < 5) {
                    $result .= $ws ? 'minute' : 'minutami';
                } else {
                    $result .= $ws ? 'minut' : 'minutami';
                }

                return $result;
            case 'h':
                return $withoutSuffix ? 'ena ura' : 'eno uro';
            case 'hh':
                if ($number === 1) {
                    $result .= $withoutSuffix ? 'ura' : 'uro';
                } elseif ($number === 2) {
                    $result .= $ws ? 'uri' : 'urama';
                } elseif ($number < 5) {
                    $result .= $ws ? 'ure' : 'urami';
                } else {
                    $result .= $ws ? 'ur' : 'urami';
                }

                return $result;
            case 'd':
                return $ws ? 'en dan' : 'enim dnem';
            case 'dd':
                if ($number === 1) {
                    $result .= $ws ? 'dan' : 'dnem';
                } elseif ($number === 2) {
                    $result .= $ws ? 'dni' : 'dnevoma';
                } else {
                    $result .= $ws ? 'dni' : 'dnevi';
                }

                return $result;
            case 'M':
                return $ws ? 'en mesec' : 'enim mesecem';
            case 'MM':
                if ($number === 1) {
                    $result .= $ws ? 'mesec' : 'mesecem';
                } elseif ($number === 2) {
                    $result .= $ws ? 'meseca' : 'mesecema';
                } elseif ($number < 5) {
                    $result .= $ws ? 'mesece' : 'meseci';
                } else {
                    $result .= $ws ? 'mesecev' : 'meseci';
                }

                return $result;
            case 'y':
                return $ws ? 'eno leto' : 'enim letom';
            case 'yy':
                if ($number === 1) {
                    $result .= $ws ? 'leto' : 'letom';
                } elseif ($number === 2) {
                    $result .= $ws ? 'leti' : 'letoma';
                } elseif ($number < 5) {
                    $result .= $ws ? 'leta' : 'leti';
                } else {
                    $result .= $ws ? 'let' : 'leti';
                }

                return $result;
        }

        return null;
    }
}
