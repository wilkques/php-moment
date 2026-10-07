<?php

namespace Wilkques\Moment\Locale\Locales;

use Wilkques\Moment\Locale\Locale;

class FrCh extends Locale
{
    public $abbr = 'fr-ch';

    public static function config()
    {
        return array(
            'months' => array('janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre'),
            'monthsShort' => array('janv.', 'févr.', 'mars', 'avr.', 'mai', 'juin', 'juil.', 'août', 'sept.', 'oct.', 'nov.', 'déc.'),
            'monthsParseExact' => true,
            'weekdays' => array('dimanche', 'lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi'),
            'weekdaysShort' => array('dim.', 'lun.', 'mar.', 'mer.', 'jeu.', 'ven.', 'sam.'),
            'weekdaysMin' => array('di', 'lu', 'ma', 'me', 'je', 've', 'sa'),
            'weekdaysParseExact' => true,
            'longDateFormat' => array(
                'LT' => 'HH:mm', 'LTS' => 'HH:mm:ss', 'L' => 'DD.MM.YYYY',
                'LL' => 'D MMMM YYYY', 'LLL' => 'D MMMM YYYY HH:mm', 'LLLL' => 'dddd D MMMM YYYY HH:mm',
            ),
            'calendar' => array(
                'sameDay' => '[Aujourd’hui à] LT', 'nextDay' => '[Demain à] LT', 'nextWeek' => 'dddd [à] LT',
                'lastDay' => '[Hier à] LT', 'lastWeek' => 'dddd [dernier à] LT', 'sameElse' => 'L',
            ),
            'relativeTime' => array(
                'future' => 'dans %s', 'past' => 'il y a %s',
                's' => 'quelques secondes', 'ss' => '%d secondes', 'm' => 'une minute', 'mm' => '%d minutes',
                'h' => 'une heure', 'hh' => '%d heures', 'd' => 'un jour', 'dd' => '%d jours',
                'M' => 'un mois', 'MM' => '%d mois', 'y' => 'un an', 'yy' => '%d ans',
            ),
            'dayOfMonthOrdinalParse' => '~\d{1,2}(er|e)~u',
            'week' => array('dow' => 1, 'doy' => 4),
        );
    }

    public function ordinal($number, $token = '')
    {
        switch ($token) {
            case 'w':
            case 'W':
                return $number . ($number === 1 ? 're' : 'e');
            default:
                return $number . ($number === 1 ? 'er' : 'e');
        }
    }
}
