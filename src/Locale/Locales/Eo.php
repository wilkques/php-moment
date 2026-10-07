<?php

namespace Wilkques\Moment\Locale\Locales;

use Wilkques\Moment\Locale\Locale;

class Eo extends Locale
{
    public $abbr = 'eo';

    public static function config()
    {
        return array(
            'months' => array('januaro', 'februaro', 'marto', 'aprilo', 'majo', 'junio', 'julio', 'aŭgusto', 'septembro', 'oktobro', 'novembro', 'decembro'),
            'monthsShort' => array('jan', 'feb', 'mart', 'apr', 'maj', 'jun', 'jul', 'aŭg', 'sept', 'okt', 'nov', 'dec'),
            'weekdays' => array('dimanĉo', 'lundo', 'mardo', 'merkredo', 'ĵaŭdo', 'vendredo', 'sabato'),
            'weekdaysShort' => array('dim', 'lun', 'mard', 'merk', 'ĵaŭ', 'ven', 'sab'),
            'weekdaysMin' => array('di', 'lu', 'ma', 'me', 'ĵa', 've', 'sa'),
            'longDateFormat' => array(
                'LT' => 'HH:mm', 'LTS' => 'HH:mm:ss', 'L' => 'YYYY-MM-DD',
                'LL' => '[la] D[-an de] MMMM, YYYY', 'LLL' => '[la] D[-an de] MMMM, YYYY HH:mm',
                'LLLL' => 'dddd[n], [la] D[-an de] MMMM, YYYY HH:mm', 'llll' => 'ddd, [la] D[-an de] MMM, YYYY HH:mm',
            ),
            'meridiemParse' => '~[ap]\.t\.m~iu',
            'calendar' => array(
                'sameDay' => '[Hodiaŭ je] LT', 'nextDay' => '[Morgaŭ je] LT', 'nextWeek' => 'dddd[n je] LT',
                'lastDay' => '[Hieraŭ je] LT', 'lastWeek' => '[pasintan] dddd[n je] LT', 'sameElse' => 'L',
            ),
            'relativeTime' => array(
                'future' => 'post %s', 'past' => 'antaŭ %s', 's' => 'kelkaj sekundoj', 'ss' => '%d sekundoj',
                'm' => 'unu minuto', 'mm' => '%d minutoj', 'h' => 'unu horo', 'hh' => '%d horoj',
                'd' => 'unu tago', 'dd' => '%d tagoj', 'M' => 'unu monato', 'MM' => '%d monatoj',
                'y' => 'unu jaro', 'yy' => '%d jaroj',
            ),
            'dayOfMonthOrdinalParse' => '~\d{1,2}a~u',
            'ordinal' => '%da',
            'week' => array('dow' => 1, 'doy' => 7),
        );
    }

    public function isPM($input)
    {
        return strtolower(substr($input, 0, 1)) === 'p';
    }

    public function meridiem($hours, $minutes, $isLower)
    {
        if ($hours > 11) {
            return $isLower ? 'p.t.m.' : 'P.T.M.';
        }

        return $isLower ? 'a.t.m.' : 'A.T.M.';
    }
}
