<?php

namespace Wilkques\Moment\Locale\Locales;

use Wilkques\Moment\Locale\Locale;

class It extends Locale
{
    public $abbr = 'it';

    public static function config()
    {
        return array(
            'months' => array('gennaio', 'febbraio', 'marzo', 'aprile', 'maggio', 'giugno', 'luglio', 'agosto', 'settembre', 'ottobre', 'novembre', 'dicembre'),
            'monthsShort' => array('gen', 'feb', 'mar', 'apr', 'mag', 'giu', 'lug', 'ago', 'set', 'ott', 'nov', 'dic'),
            'weekdays' => array('domenica', 'lunedì', 'martedì', 'mercoledì', 'giovedì', 'venerdì', 'sabato'),
            'weekdaysShort' => array('dom', 'lun', 'mar', 'mer', 'gio', 'ven', 'sab'),
            'weekdaysMin' => array('do', 'lu', 'ma', 'me', 'gi', 've', 'sa'),
            'longDateFormat' => array(
                'LT' => 'HH:mm', 'LTS' => 'HH:mm:ss', 'L' => 'DD/MM/YYYY', 'LL' => 'D MMMM YYYY',
                'LLL' => 'D MMMM YYYY HH:mm', 'LLLL' => 'dddd D MMMM YYYY HH:mm',
            ),
            'relativeTime' => array(
                'future' => 'tra %s', 'past' => '%s fa', 's' => 'alcuni secondi', 'ss' => '%d secondi',
                'm' => 'un minuto', 'mm' => '%d minuti', 'h' => "un'ora", 'hh' => '%d ore',
                'd' => 'un giorno', 'dd' => '%d giorni', 'w' => 'una settimana', 'ww' => '%d settimane',
                'M' => 'un mese', 'MM' => '%d mesi', 'y' => 'un anno', 'yy' => '%d anni',
            ),
            'dayOfMonthOrdinalParse' => '~\d{1,2}º~u',
            'ordinal' => '%dº',
            'week' => array('dow' => 1, 'doy' => 4),
        );
    }

    public function calendar($key, $mom = null, $now = null)
    {
        $h = $mom === null ? 0 : $mom->hour();
        $a = $h > 1 ? 'lle ' : ($h === 0 ? ' ' : "ll'");
        switch ($key) {
            case 'sameDay':
                return '[Oggi a' . $a . ']LT';
            case 'nextDay':
                return '[Domani a' . $a . ']LT';
            case 'nextWeek':
                return 'dddd [a' . $a . ']LT';
            case 'lastDay':
                return '[Ieri a' . $a . ']LT';
            case 'lastWeek':
                return ($mom->day() === 0 ? '[La scorsa]' : '[Lo scorso]') . ' dddd [a' . $a . ']LT';
        }

        return parent::calendar($key, $mom, $now);
    }
}
