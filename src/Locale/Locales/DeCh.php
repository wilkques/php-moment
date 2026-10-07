<?php

namespace Wilkques\Moment\Locale\Locales;

use Wilkques\Moment\Locale\Locale;

class DeCh extends Locale
{
    public $abbr = 'de-ch';

    public static function config()
    {
        return array(
            'months' => explode('_', 'Januar_Februar_März_April_Mai_Juni_Juli_August_September_Oktober_November_Dezember'),
            'monthsShort' => explode('_', 'Jan._Feb._März_Apr._Mai_Juni_Juli_Aug._Sep._Okt._Nov._Dez.'),
            'monthsParseExact' => true,
            'weekdays' => explode('_', 'Sonntag_Montag_Dienstag_Mittwoch_Donnerstag_Freitag_Samstag'),
            'weekdaysShort' => explode('_', 'So_Mo_Di_Mi_Do_Fr_Sa'),
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
                'future' => 'in %s', 'past' => 'vor %s',
                's' => 'ein paar Sekunden', 'ss' => '%d Sekunden', 'mm' => '%d Minuten',
                'hh' => '%d Stunden', 'ww' => '%d Wochen',
            ),
            'dayOfMonthOrdinalParse' => '~\d{1,2}\.~u',
            'ordinal' => '%d.',
            'week' => array('dow' => 1, 'doy' => 4),
        );
    }

    public function relativeTime($number, $withoutSuffix, $key, $isFuture)
    {
        switch ($key) {
            case 'm':
                $f = array('eine Minute', 'einer Minute');
                break;
            case 'h':
                $f = array('eine Stunde', 'einer Stunde');
                break;
            case 'd':
                $f = array('ein Tag', 'einem Tag');
                break;
            case 'dd':
                $f = array($number . ' Tage', $number . ' Tagen');
                break;
            case 'w':
                $f = array('eine Woche', 'einer Woche');
                break;
            case 'M':
                $f = array('ein Monat', 'einem Monat');
                break;
            case 'MM':
                $f = array($number . ' Monate', $number . ' Monaten');
                break;
            case 'y':
                $f = array('ein Jahr', 'einem Jahr');
                break;
            case 'yy':
                $f = array($number . ' Jahre', $number . ' Jahren');
                break;
            default:
                return parent::relativeTime($number, $withoutSuffix, $key, $isFuture);
        }

        return $withoutSuffix ? $f[0] : $f[1];
    }
}
