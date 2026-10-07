<?php

namespace Wilkques\Moment\Locale\Locales;

use Wilkques\Moment\Locale\Locale;

class OcLnc extends Locale
{
    public $abbr = 'oc-lnc';

    public static function config()
    {
        return array(
            'months' => array(
                'standalone' => array('genièr', 'febrièr', 'març', 'abril', 'mai', 'junh', 'julhet', 'agost', 'setembre', 'octòbre', 'novembre', 'decembre'),
                'format' => array('de genièr', 'de febrièr', 'de març', "d'abril", 'de mai', 'de junh', 'de julhet', "d'agost", 'de setembre', "d'octòbre", 'de novembre', 'de decembre'),
                'isFormat' => '~D[oD]?(\s)+MMMM~u',
            ),
            'monthsShort' => array('gen.', 'febr.', 'març', 'abr.', 'mai', 'junh', 'julh.', 'ago.', 'set.', 'oct.', 'nov.', 'dec.'),
            'monthsParseExact' => true,
            'weekdays' => array('dimenge', 'diluns', 'dimars', 'dimècres', 'dijòus', 'divendres', 'dissabte'),
            'weekdaysShort' => array('dg.', 'dl.', 'dm.', 'dc.', 'dj.', 'dv.', 'ds.'),
            'weekdaysMin' => array('dg', 'dl', 'dm', 'dc', 'dj', 'dv', 'ds'),
            'weekdaysParseExact' => true,
            'longDateFormat' => array(
                'LT' => 'H:mm', 'LTS' => 'H:mm:ss', 'L' => 'DD/MM/YYYY', 'LL' => 'D MMMM [de] YYYY', 'll' => 'D MMM YYYY',
                'LLL' => 'D MMMM [de] YYYY [a] H:mm', 'lll' => 'D MMM YYYY, H:mm',
                'LLLL' => 'dddd D MMMM [de] YYYY [a] H:mm', 'llll' => 'ddd D MMM YYYY, H:mm',
            ),
            'calendar' => array(
                'sameDay' => '[uèi a] LT', 'nextDay' => '[deman a] LT', 'nextWeek' => 'dddd [a] LT',
                'lastDay' => '[ièr a] LT', 'lastWeek' => 'dddd [passat a] LT', 'sameElse' => 'L',
            ),
            'relativeTime' => array(
                'future' => "d'aquí %s", 'past' => 'fa %s', 's' => 'unas segondas', 'ss' => '%d segondas',
                'm' => 'una minuta', 'mm' => '%d minutas', 'h' => 'una ora', 'hh' => '%d oras',
                'd' => 'un jorn', 'dd' => '%d jorns', 'M' => 'un mes', 'MM' => '%d meses', 'y' => 'un an', 'yy' => '%d ans',
            ),
            'dayOfMonthOrdinalParse' => '~\d{1,2}(r|n|t|è|a)~u',
            'week' => array('dow' => 1, 'doy' => 4),
        );
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
