<?php

namespace Wilkques\Moment\Locale\Locales;

use Wilkques\Moment\Locale\Locale;

class DeAt extends Locale
{
    public $abbr = 'de-at';

    public static function config()
    {
        return array(
            'months' => explode('_', 'Jänner_Februar_März_April_Mai_Juni_Juli_August_September_Oktober_November_Dezember'),
            'monthsShort' => explode('_', 'Jän._Feb._März_Apr._Mai_Juni_Juli_Aug._Sep._Okt._Nov._Dez.'),
            'monthsParseExact' => true,
            'weekdays' => explode('_', 'Sonntag_Montag_Dienstag_Mittwoch_Donnerstag_Freitag_Samstag'),
            'weekdaysShort' => explode('_', 'So._Mo._Di._Mi._Do._Fr._Sa.'),
            'weekdaysMin' => explode('_', 'So_Mo_Di_Mi_Do_Fr_Sa'),
            'weekdaysParseExact' => true,
            'longDateFormat' => array(
                'LT' => 'HH:mm', 'LTS' => 'HH:mm:ss', 'L' => 'DD.MM.YYYY', 'LL' => 'D. MMMM YYYY',
                'LLL' => 'D. MMMM YYYY HH:mm', 'LLLL' => 'dddd, D. MMMM YYYY HH:mm',
            ),
            'calendar' => array(
                'sameDay' => '[heute um] LT [Uhr]', 'sameElse' => 'L', 'nextDay' => '[morgen um] LT [Uhr]',
                'nextWeek' => 'dddd [um] LT [Uhr]', 'lastDay' => '[gestern um] LT [Uhr]',
                'lastWeek' => '[letzten] dddd [um] LT [Uhr]',
            ),
            'relativeTime' => array(
                'future' => 'in %s', 'past' => 'vor %s', 's' => 'ein paar Sekunden', 'ss' => '%d Sekunden',
                'mm' => '%d Minuten', 'hh' => '%d Stunden', 'ww' => '%d Wochen',
            ),
            'dayOfMonthOrdinalParse' => '~\d{1,2}\.~u',
            'ordinal' => '%d.',
            'week' => array('dow' => 1, 'doy' => 4),
        );
    }

    public function relativeTime($number, $withoutSuffix, $key, $isFuture)
    {
        $format = array(
            'm' => array('eine Minute', 'einer Minute'),
            'h' => array('eine Stunde', 'einer Stunde'),
            'd' => array('ein Tag', 'einem Tag'),
            'dd' => array($number . ' Tage', $number . ' Tagen'),
            'w' => array('eine Woche', 'einer Woche'),
            'M' => array('ein Monat', 'einem Monat'),
            'MM' => array($number . ' Monate', $number . ' Monaten'),
            'y' => array('ein Jahr', 'einem Jahr'),
            'yy' => array($number . ' Jahre', $number . ' Jahren'),
        );
        if (!isset($format[$key])) {
            return parent::relativeTime($number, $withoutSuffix, $key, $isFuture);
        }

        return $format[$key][$withoutSuffix ? 0 : 1];
    }
}
