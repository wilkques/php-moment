<?php

namespace Wilkques\Moment\Locale\Locales;

use Wilkques\Moment\Locale\Locale;

class Ca extends Locale
{
    public $abbr = 'ca';

    public static function config()
    {
        return array(
            'months' => array(
                'standalone' => array('gener', 'febrer', 'març', 'abril', 'maig', 'juny', 'juliol', 'agost', 'setembre', 'octubre', 'novembre', 'desembre'),
                'format' => array('de gener', 'de febrer', 'de març', "d'abril", 'de maig', 'de juny', 'de juliol', "d'agost", 'de setembre', "d'octubre", 'de novembre', 'de desembre'),
                'isFormat' => '~D[oD]?(\s)+MMMM~u',
            ),
            'monthsShort' => array('gen.', 'febr.', 'març', 'abr.', 'maig', 'juny', 'jul.', 'ag.', 'set.', 'oct.', 'nov.', 'des.'),
            'monthsParseExact' => true,
            'weekdays' => array('diumenge', 'dilluns', 'dimarts', 'dimecres', 'dijous', 'divendres', 'dissabte'),
            'weekdaysShort' => array('dg.', 'dl.', 'dt.', 'dc.', 'dj.', 'dv.', 'ds.'),
            'weekdaysMin' => array('dg', 'dl', 'dt', 'dc', 'dj', 'dv', 'ds'),
            'weekdaysParseExact' => true,
            'longDateFormat' => array(
                'LT' => 'H:mm', 'LTS' => 'H:mm:ss', 'L' => 'DD/MM/YYYY', 'LL' => 'D MMMM [de] YYYY',
                'll' => 'D MMM YYYY', 'LLL' => 'D MMMM [de] YYYY [a les] H:mm', 'lll' => 'D MMM YYYY, H:mm',
                'LLLL' => 'dddd D MMMM [de] YYYY [a les] H:mm', 'llll' => 'ddd D MMM YYYY, H:mm',
            ),
            'relativeTime' => array(
                'future' => "d'aquí %s", 'past' => 'fa %s', 's' => 'uns segons', 'ss' => '%d segons',
                'm' => 'un minut', 'mm' => '%d minuts', 'h' => 'una hora', 'hh' => '%d hores',
                'd' => 'un dia', 'dd' => '%d dies', 'M' => 'un mes', 'MM' => '%d mesos', 'y' => 'un any', 'yy' => '%d anys',
            ),
            'dayOfMonthOrdinalParse' => '~\d{1,2}(r|n|t|è|a)~u',
            'week' => array('dow' => 1, 'doy' => 4),
        );
    }

    public function calendar($key, $mom = null, $now = null)
    {
        $art = $mom->hour() !== 1 ? 'les' : 'la';
        switch ($key) {
            case 'sameDay':
                return '[avui a ' . $art . '] LT';
            case 'nextDay':
                return '[demà a ' . $art . '] LT';
            case 'nextWeek':
                return 'dddd [a ' . $art . '] LT';
            case 'lastDay':
                return '[ahir a ' . $art . '] LT';
            case 'lastWeek':
                return '[el] dddd [passat a ' . $art . '] LT';
        }

        return parent::calendar($key, $mom, $now);
    }

    public function ordinal($number, $token = '')
    {
        $output = $number === 1 ? 'r' : ($number === 2 ? 'n' : ($number === 3 ? 'r' : ($number === 4 ? 't' : 'è')));
        if ($token === 'w' || $token === 'W') {
            $output = 'a';
        }

        return $number . $output;
    }
}
