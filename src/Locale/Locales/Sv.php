<?php

namespace Wilkques\Moment\Locale\Locales;

use Wilkques\Moment\Locale\Locale;

class Sv extends Locale
{
    public $abbr = 'sv';

    public static function config()
    {
        return array(
            'months' => explode('_', 'januari_februari_mars_april_maj_juni_juli_augusti_september_oktober_november_december'),
            'monthsShort' => explode('_', 'jan_feb_mar_apr_maj_jun_jul_aug_sep_okt_nov_dec'),
            'weekdays' => explode('_', 'söndag_måndag_tisdag_onsdag_torsdag_fredag_lördag'),
            'weekdaysShort' => explode('_', 'sön_mån_tis_ons_tor_fre_lör'),
            'weekdaysMin' => explode('_', 'sö_må_ti_on_to_fr_lö'),
            'longDateFormat' => array(
                'LT' => 'HH:mm', 'LTS' => 'HH:mm:ss', 'L' => 'YYYY-MM-DD', 'LL' => 'D MMMM YYYY',
                'LLL' => 'D MMMM YYYY [kl.] HH:mm', 'LLLL' => 'dddd D MMMM YYYY [kl.] HH:mm',
                'lll' => 'D MMM YYYY HH:mm', 'llll' => 'ddd D MMM YYYY HH:mm',
            ),
            'calendar' => array(
                'sameDay' => '[Idag] LT', 'nextDay' => '[Imorgon] LT', 'lastDay' => '[Igår] LT',
                'nextWeek' => '[På] dddd LT', 'lastWeek' => '[I] dddd[s] LT', 'sameElse' => 'L',
            ),
            'relativeTime' => array(
                'future' => 'om %s', 'past' => 'för %s sedan',
                's' => 'några sekunder', 'ss' => '%d sekunder',
                'm' => 'en minut', 'mm' => '%d minuter',
                'h' => 'en timme', 'hh' => '%d timmar',
                'd' => 'en dag', 'dd' => '%d dagar',
                'M' => 'en månad', 'MM' => '%d månader',
                'y' => 'ett år', 'yy' => '%d år',
            ),
            'dayOfMonthOrdinalParse' => '~\d{1,2}(\:e|\:a)~u',
            'week' => array('dow' => 1, 'doy' => 4),
        );
    }

    public function ordinal($number, $token = '')
    {
        $b = $number % 10;
        $tens = (int) (($number % 100) / 10);

        return $number . ($tens === 1 ? ':e' : ($b === 1 || $b === 2 ? ':a' : ':e'));
    }
}
