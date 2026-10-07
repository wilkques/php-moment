<?php

namespace Wilkques\Moment\Locale\Locales;

use Wilkques\Moment\Locale\Locale;

class ItCh extends Locale
{
    public $abbr = 'it-ch';

    public static function config()
    {
        return array(
            'calendar' => array(
                'sameDay' => '[Oggi alle] LT',
                'nextDay' => '[Domani alle] LT',
                'nextWeek' => 'dddd [alle] LT',
                'lastDay' => '[Ieri alle] LT',
                'sameElse' => 'L',
            ),
            'longDateFormat' => array(
                'LTS' => 'HH:mm:ss',
                'LT' => 'HH:mm',
                'L' => 'DD.MM.YYYY',
                'LL' => 'D MMMM YYYY',
                'LLL' => 'D MMMM YYYY HH:mm',
                'LLLL' => 'dddd D MMMM YYYY HH:mm',
            ),
            'ordinal' => '%dº',
            'dayOfMonthOrdinalParse' => '~\\d{1,2}º~u',
            'relativeTime' => array(
                'past' => '%s fa',
                's' => 'alcuni secondi',
                'ss' => '%d secondi',
                'm' => 'un minuto',
                'mm' => '%d minuti',
                'h' => 'un\'ora',
                'hh' => '%d ore',
                'd' => 'un giorno',
                'dd' => '%d giorni',
                'w' => 'a week',
                'ww' => '%d weeks',
                'M' => 'un mese',
                'MM' => '%d mesi',
                'y' => 'un anno',
                'yy' => '%d anni',
            ),
            'months' => array('gennaio', 'febbraio', 'marzo', 'aprile', 'maggio', 'giugno', 'luglio', 'agosto', 'settembre', 'ottobre', 'novembre', 'dicembre'),
            'monthsShort' => array('gen', 'feb', 'mar', 'apr', 'mag', 'giu', 'lug', 'ago', 'set', 'ott', 'nov', 'dic'),
            'week' => array(
                'dow' => 1,
                'doy' => 4,
            ),
            'weekdays' => array('domenica', 'lunedì', 'martedì', 'mercoledì', 'giovedì', 'venerdì', 'sabato'),
            'weekdaysMin' => array('do', 'lu', 'ma', 'me', 'gi', 've', 'sa'),
            'weekdaysShort' => array('dom', 'lun', 'mar', 'mer', 'gio', 'ven', 'sab'),
        );
    }

    public function calendar($key, $mom = null, $now = null)
    {
        if ($key === 'lastWeek') {
            return $mom->day() === 0 ? '[la scorsa] dddd [alle] LT' : '[lo scorso] dddd [alle] LT';
        }

        return parent::calendar($key, $mom, $now);
    }

    public function pastFuture($diff, $output)
    {
        if ($diff > 0) {
            return (preg_match('~^[0-9].+$~u', $output) ? 'tra' : 'in') . ' ' . $output;
        }

        return parent::pastFuture($diff, $output);
    }
}
