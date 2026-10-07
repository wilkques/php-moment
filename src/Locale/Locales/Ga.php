<?php

namespace Wilkques\Moment\Locale\Locales;

use Wilkques\Moment\Locale\Locale;

class Ga extends Locale
{
    public $abbr = 'ga';

    public static function config()
    {
        return array(
            'months' => array('Eanáir', 'Feabhra', 'Márta', 'Aibreán', 'Bealtaine', 'Meitheamh', 'Iúil', 'Lúnasa', 'Meán Fómhair', 'Deireadh Fómhair', 'Samhain', 'Nollaig'),
            'monthsShort' => array('Ean', 'Feabh', 'Márt', 'Aib', 'Beal', 'Meith', 'Iúil', 'Lún', 'M.F.', 'D.F.', 'Samh', 'Noll'),
            'monthsParseExact' => true,
            'weekdays' => array('Dé Domhnaigh', 'Dé Luain', 'Dé Máirt', 'Dé Céadaoin', 'Déardaoin', 'Dé hAoine', 'Dé Sathairn'),
            'weekdaysShort' => array('Domh', 'Luan', 'Máirt', 'Céad', 'Déar', 'Aoine', 'Sath'),
            'weekdaysMin' => array('Do', 'Lu', 'Má', 'Cé', 'Dé', 'A', 'Sa'),
            'longDateFormat' => array(
                'LT' => 'HH:mm',
                'LTS' => 'HH:mm:ss',
                'L' => 'DD/MM/YYYY',
                'LL' => 'D MMMM YYYY',
                'LLL' => 'D MMMM YYYY HH:mm',
                'LLLL' => 'dddd, D MMMM YYYY HH:mm',
            ),
            'calendar' => array(
                'sameDay' => '[Inniu ag] LT',
                'nextDay' => '[Amárach ag] LT',
                'nextWeek' => 'dddd [ag] LT',
                'lastDay' => '[Inné ag] LT',
                'lastWeek' => 'dddd [seo caite] [ag] LT',
                'sameElse' => 'L',
            ),
            'relativeTime' => array(
                'future' => 'i %s',
                'past' => '%s ó shin',
                's' => 'cúpla soicind',
                'ss' => '%d soicind',
                'm' => 'nóiméad',
                'mm' => '%d nóiméad',
                'h' => 'uair an chloig',
                'hh' => '%d uair an chloig',
                'd' => 'lá',
                'dd' => '%d lá',
                'M' => 'mí',
                'MM' => '%d míonna',
                'y' => 'bliain',
                'yy' => '%d bliain',
            ),
            'dayOfMonthOrdinalParse' => '~\d{1,2}(d|na|mh)~u',
            'week' => array('dow' => 1, 'doy' => 4),
        );
    }

    public function ordinal($number, $token = '')
    {
        return $number . ($number === 1 ? 'd' : ($number % 10 === 2 ? 'na' : 'mh'));
    }
}
