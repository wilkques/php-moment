<?php

namespace Wilkques\Moment\Locale\Locales;

use Wilkques\Moment\Locale\Locale;

class FrCa extends Locale
{
    public $abbr = 'fr-ca';

    public static function config()
    {
        return array(
            'months' => explode('_', 'janvier_février_mars_avril_mai_juin_juillet_août_septembre_octobre_novembre_décembre'),
            'monthsShort' => explode('_', 'janv._févr._mars_avr._mai_juin_juil._août_sept._oct._nov._déc.'),
            'monthsParseExact' => true,
            'weekdays' => explode('_', 'dimanche_lundi_mardi_mercredi_jeudi_vendredi_samedi'),
            'weekdaysShort' => explode('_', 'dim._lun._mar._mer._jeu._ven._sam.'),
            'weekdaysMin' => explode('_', 'di_lu_ma_me_je_ve_sa'),
            'weekdaysParseExact' => true,
            'longDateFormat' => array(
                'LT' => 'HH:mm', 'LTS' => 'HH:mm:ss', 'L' => 'YYYY-MM-DD', 'LL' => 'D MMMM YYYY',
                'LLL' => 'D MMMM YYYY HH:mm', 'LLLL' => 'dddd D MMMM YYYY HH:mm',
            ),
            'calendar' => array(
                'sameDay' => '[Aujourd’hui à] LT', 'nextDay' => '[Demain à] LT', 'nextWeek' => 'dddd [à] LT',
                'lastDay' => '[Hier à] LT', 'lastWeek' => 'dddd [dernier à] LT', 'sameElse' => 'L',
            ),
            'relativeTime' => array(
                'future' => 'dans %s', 'past' => 'il y a %s', 's' => 'quelques secondes', 'ss' => '%d secondes',
                'm' => 'une minute', 'mm' => '%d minutes', 'h' => 'une heure', 'hh' => '%d heures',
                'd' => 'un jour', 'dd' => '%d jours', 'M' => 'un mois', 'MM' => '%d mois', 'y' => 'un an', 'yy' => '%d ans',
            ),
            'dayOfMonthOrdinalParse' => '~\d{1,2}(er|e)~u',
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
