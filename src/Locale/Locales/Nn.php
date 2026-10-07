<?php

namespace Wilkques\Moment\Locale\Locales;

use Wilkques\Moment\Locale\Locale;

class Nn extends Locale
{
    public $abbr = 'nn';

    public static function config()
    {
        return array(
            'months' => explode('_', 'januar_februar_mars_april_mai_juni_juli_august_september_oktober_november_desember'),
            'monthsShort' => explode('_', 'jan._feb._mars_apr._mai_juni_juli_aug._sep._okt._nov._des.'),
            'monthsParseExact' => true,
            'weekdays' => explode('_', 'sundag_måndag_tysdag_onsdag_torsdag_fredag_laurdag'),
            'weekdaysShort' => explode('_', 'su._må._ty._on._to._fr._lau.'),
            'weekdaysMin' => explode('_', 'su_må_ty_on_to_fr_la'),
            'weekdaysParseExact' => true,
            'longDateFormat' => array(
                'LT' => 'HH:mm',
                'LTS' => 'HH:mm:ss',
                'L' => 'DD.MM.YYYY',
                'LL' => 'D. MMMM YYYY',
                'LLL' => 'D. MMMM YYYY [kl.] H:mm',
                'LLLL' => 'dddd D. MMMM YYYY [kl.] HH:mm',
            ),
            'calendar' => array(
                'sameDay' => '[I dag klokka] LT',
                'nextDay' => '[I morgon klokka] LT',
                'nextWeek' => 'dddd [klokka] LT',
                'lastDay' => '[I går klokka] LT',
                'lastWeek' => '[Føregåande] dddd [klokka] LT',
                'sameElse' => 'L',
            ),
            'relativeTime' => array(
                'future' => 'om %s',
                'past' => '%s sidan',
                's' => 'nokre sekund',
                'ss' => '%d sekund',
                'm' => 'eit minutt',
                'mm' => '%d minutt',
                'h' => 'ein time',
                'hh' => '%d timar',
                'd' => 'ein dag',
                'dd' => '%d dagar',
                'w' => 'ei veke',
                'ww' => '%d veker',
                'M' => 'ein månad',
                'MM' => '%d månader',
                'y' => 'eit år',
                'yy' => '%d år',
            ),
            'dayOfMonthOrdinalParse' => '~\d{1,2}\.~u',
            'ordinal' => '%d.',
            'week' => array('dow' => 1, 'doy' => 4),
        );
    }
}
