<?php

namespace Wilkques\Moment\Locale\Locales;

use Wilkques\Moment\Locale\Locale;

class Eu extends Locale
{
    public $abbr = 'eu';

    public static function config()
    {
        return array(
            'months' => array('urtarrila', 'otsaila', 'martxoa', 'apirila', 'maiatza', 'ekaina', 'uztaila', 'abuztua', 'iraila', 'urria', 'azaroa', 'abendua'),
            'monthsShort' => array('urt.', 'ots.', 'mar.', 'api.', 'mai.', 'eka.', 'uzt.', 'abu.', 'ira.', 'urr.', 'aza.', 'abe.'),
            'monthsParseExact' => true,
            'weekdays' => array('igandea', 'astelehena', 'asteartea', 'asteazkena', 'osteguna', 'ostirala', 'larunbata'),
            'weekdaysShort' => array('ig.', 'al.', 'ar.', 'az.', 'og.', 'ol.', 'lr.'),
            'weekdaysMin' => array('ig', 'al', 'ar', 'az', 'og', 'ol', 'lr'),
            'weekdaysParseExact' => true,
            'longDateFormat' => array(
                'LT' => 'HH:mm', 'LTS' => 'HH:mm:ss', 'L' => 'YYYY-MM-DD', 'LL' => 'YYYY[ko] MMMM[ren] D[a]',
                'LLL' => 'YYYY[ko] MMMM[ren] D[a] HH:mm', 'LLLL' => 'dddd, YYYY[ko] MMMM[ren] D[a] HH:mm',
                'l' => 'YYYY-M-D', 'll' => 'YYYY[ko] MMM D[a]', 'lll' => 'YYYY[ko] MMM D[a] HH:mm',
                'llll' => 'ddd, YYYY[ko] MMM D[a] HH:mm',
            ),
            'calendar' => array(
                'sameDay' => '[gaur] LT[etan]', 'nextDay' => '[bihar] LT[etan]', 'nextWeek' => 'dddd LT[etan]',
                'lastDay' => '[atzo] LT[etan]', 'lastWeek' => '[aurreko] dddd LT[etan]', 'sameElse' => 'L',
            ),
            'relativeTime' => array(
                'future' => '%s barru', 'past' => 'duela %s', 's' => 'segundo batzuk', 'ss' => '%d segundo',
                'm' => 'minutu bat', 'mm' => '%d minutu', 'h' => 'ordu bat', 'hh' => '%d ordu',
                'd' => 'egun bat', 'dd' => '%d egun', 'M' => 'hilabete bat', 'MM' => '%d hilabete', 'y' => 'urte bat', 'yy' => '%d urte',
            ),
            'dayOfMonthOrdinalParse' => '~\d{1,2}\.~u',
            'ordinal' => '%d.',
            'week' => array('dow' => 1, 'doy' => 7),
        );
    }
}
