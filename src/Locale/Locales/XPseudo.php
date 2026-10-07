<?php

namespace Wilkques\Moment\Locale\Locales;

use Wilkques\Moment\Locale\Locale;

class XPseudo extends Locale
{
    public $abbr = 'x-pseudo';

    public static function config()
    {
        return array(
            'months' => explode('_', 'J~áñúá~rý_F~ébrú~árý_~Márc~h_Áp~ríl_~Máý_~Júñé~_Júl~ý_Áú~gúst~_Sép~témb~ér_Ó~ctób~ér_Ñ~óvém~bér_~Décé~mbér'),
            'monthsShort' => explode('_', 'J~áñ_~Féb_~Már_~Ápr_~Máý_~Júñ_~Júl_~Áúg_~Sép_~Óct_~Ñóv_~Déc'),
            'monthsParseExact' => true,
            'weekdays' => explode('_', 'S~úñdá~ý_Mó~ñdáý~_Túé~sdáý~_Wéd~ñésd~áý_T~húrs~dáý_~Fríd~áý_S~átúr~dáý'),
            'weekdaysShort' => explode('_', 'S~úñ_~Móñ_~Túé_~Wéd_~Thú_~Frí_~Sát'),
            'weekdaysMin' => explode('_', 'S~ú_Mó~_Tú_~Wé_T~h_Fr~_Sá'),
            'weekdaysParseExact' => true,
            'longDateFormat' => array(
                'LT' => 'HH:mm',
                'LTS' => 'h:mm:ss A',
                'L' => 'DD/MM/YYYY',
                'LL' => 'D MMMM YYYY',
                'LLL' => 'D MMMM YYYY HH:mm',
                'LLLL' => 'dddd, D MMMM YYYY HH:mm',
            ),
            'calendar' => array(
                'sameDay' => '[T~ódá~ý át] LT',
                'nextDay' => '[T~ómó~rró~w át] LT',
                'nextWeek' => 'dddd [át] LT',
                'lastDay' => '[Ý~ést~érdá~ý át] LT',
                'lastWeek' => '[L~ást] dddd [át] LT',
                'sameElse' => 'L',
            ),
            'relativeTime' => array(
                'future' => 'í~ñ %s',
                'past' => '%s á~gó',
                's' => 'á ~féw ~sécó~ñds',
                'ss' => '%d s~écóñ~ds',
                'm' => 'á ~míñ~úté',
                'mm' => '%d m~íñú~tés',
                'h' => 'á~ñ hó~úr',
                'hh' => '%d h~óúrs',
                'd' => 'á ~dáý',
                'dd' => '%d d~áýs',
                'M' => 'á ~móñ~th',
                'MM' => '%d m~óñt~hs',
                'y' => 'á ~ýéár',
                'yy' => '%d ý~éárs',
            ),
            'dayOfMonthOrdinalParse' => '~\d{1,2}(th|st|nd|rd)~u',
            'week' => array('dow' => 1, 'doy' => 4),
        );
    }

    public function ordinal($number, $token = '')
    {
        $b = $number % 10;
        $tens = (int) (($number % 100) / 10);

        return $number . ($tens === 1 ? 'th' : ($b === 1 ? 'st' : ($b === 2 ? 'nd' : ($b === 3 ? 'rd' : 'th'))));
    }
}
