<?php

namespace Wilkques\Moment\Locale\Locales;

use Wilkques\Moment\Locale\Locale;

class Fr extends Locale
{
    public $abbr = 'fr';

    public static function config()
    {
        return array(
            'calendar' => array(
                'sameDay' => '[Aujourd’hui à] LT',
                'nextDay' => '[Demain à] LT',
                'nextWeek' => 'dddd [à] LT',
                'lastDay' => '[Hier à] LT',
                'lastWeek' => 'dddd [dernier à] LT',
                'sameElse' => 'L',
            ),
            'longDateFormat' => array(
                'LTS' => 'HH:mm:ss',
                'LT' => 'HH:mm',
                'L' => 'DD/MM/YYYY',
                'LL' => 'D MMMM YYYY',
                'LLL' => 'D MMMM YYYY HH:mm',
                'LLLL' => 'dddd D MMMM YYYY HH:mm',
            ),
            'dayOfMonthOrdinalParse' => '~\\d{1,2}(er|)~u',
            'relativeTime' => array(
                'future' => 'dans %s',
                'past' => 'il y a %s',
                's' => 'quelques secondes',
                'ss' => '%d secondes',
                'm' => 'une minute',
                'mm' => '%d minutes',
                'h' => 'une heure',
                'hh' => '%d heures',
                'd' => 'un jour',
                'dd' => '%d jours',
                'w' => 'une semaine',
                'ww' => '%d semaines',
                'M' => 'un mois',
                'MM' => '%d mois',
                'y' => 'un an',
                'yy' => '%d ans',
            ),
            'months' => array('janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre'),
            'monthsShort' => array('janv.', 'févr.', 'mars', 'avr.', 'mai', 'juin', 'juil.', 'août', 'sept.', 'oct.', 'nov.', 'déc.'),
            'week' => array(
                'dow' => 1,
                'doy' => 4,
            ),
            'weekdays' => array('dimanche', 'lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi'),
            'weekdaysMin' => array('di', 'lu', 'ma', 'me', 'je', 've', 'sa'),
            'weekdaysShort' => array('dim.', 'lun.', 'mar.', 'mer.', 'jeu.', 'ven.', 'sam.'),
            'monthsRegex' => '~(janv\\.?|févr\\.?|mars|avr\\.?|mai|juin|juil\\.?|août|sept\\.?|oct\\.?|nov\\.?|déc\\.?|janvier|février|mars|avril|mai|juin|juillet|août|septembre|octobre|novembre|décembre)~iu',
            'monthsShortRegex' => '~(janv\\.?|févr\\.?|mars|avr\\.?|mai|juin|juil\\.?|août|sept\\.?|oct\\.?|nov\\.?|déc\\.?|janvier|février|mars|avril|mai|juin|juillet|août|septembre|octobre|novembre|décembre)~iu',
            'monthsStrictRegex' => '~^(janvier|février|mars|avril|mai|juin|juillet|août|septembre|octobre|novembre|décembre)~iu',
            'monthsShortStrictRegex' => '~(janv\\.?|févr\\.?|mars|avr\\.?|mai|juin|juil\\.?|août|sept\\.?|oct\\.?|nov\\.?|déc\\.?)~iu',
            'monthsParse' => array(
                '~^janv~iu',
                '~^févr~iu',
                '~^mars~iu',
                '~^avr~iu',
                '~^mai~iu',
                '~^juin~iu',
                '~^juil~iu',
                '~^août~iu',
                '~^sept~iu',
                '~^oct~iu',
                '~^nov~iu',
                '~^déc~iu',
            ),
            'longMonthsParse' => array(
                '~^janv~iu',
                '~^févr~iu',
                '~^mars~iu',
                '~^avr~iu',
                '~^mai~iu',
                '~^juin~iu',
                '~^juil~iu',
                '~^août~iu',
                '~^sept~iu',
                '~^oct~iu',
                '~^nov~iu',
                '~^déc~iu',
            ),
            'shortMonthsParse' => array(
                '~^janv~iu',
                '~^févr~iu',
                '~^mars~iu',
                '~^avr~iu',
                '~^mai~iu',
                '~^juin~iu',
                '~^juil~iu',
                '~^août~iu',
                '~^sept~iu',
                '~^oct~iu',
                '~^nov~iu',
                '~^déc~iu',
            ),
            'weekdaysParseExact' => true,
        );
    }

    public function ordinal($number, $token = '')
    {
        switch ($token) {
            case 'D':
                return $number . ($number === 1 ? 'er' : '');
            case 'w':
            case 'W':
                return $number . ($number === 1 ? 're' : 'e');
            default:
                return $number . ($number === 1 ? 'er' : 'e');
        }
    }
}
