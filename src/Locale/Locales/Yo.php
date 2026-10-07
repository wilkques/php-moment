<?php

namespace Wilkques\Moment\Locale\Locales;

use Wilkques\Moment\Locale\Locale;

class Yo extends Locale
{
    public $abbr = 'yo';

    public static function config()
    {
        return array(
            'months' => explode('_', 'Sẹ́rẹ́_Èrèlè_Ẹrẹ̀nà_Ìgbé_Èbibi_Òkùdu_Agẹmo_Ògún_Owewe_Ọ̀wàrà_Bélú_Ọ̀pẹ̀̀'),
            'monthsShort' => explode('_', 'Sẹ́r_Èrl_Ẹrn_Ìgb_Èbi_Òkù_Agẹ_Ògú_Owe_Ọ̀wà_Bél_Ọ̀pẹ̀̀'),
            'weekdays' => explode('_', 'Àìkú_Ajé_Ìsẹ́gun_Ọjọ́rú_Ọjọ́bọ_Ẹtì_Àbámẹ́ta'),
            'weekdaysShort' => explode('_', 'Àìk_Ajé_Ìsẹ́_Ọjr_Ọjb_Ẹtì_Àbá'),
            'weekdaysMin' => explode('_', 'Àì_Aj_Ìs_Ọr_Ọb_Ẹt_Àb'),
            'longDateFormat' => array(
                'LT' => 'h:mm A',
                'LTS' => 'h:mm:ss A',
                'L' => 'DD/MM/YYYY',
                'LL' => 'D MMMM YYYY',
                'LLL' => 'D MMMM YYYY h:mm A',
                'LLLL' => 'dddd, D MMMM YYYY h:mm A',
            ),
            'calendar' => array(
                'sameDay' => '[Ònì ni] LT',
                'nextDay' => '[Ọ̀la ni] LT',
                'nextWeek' => "dddd [Ọsẹ̀ tón'bọ] [ni] LT",
                'lastDay' => '[Àna ni] LT',
                'lastWeek' => 'dddd [Ọsẹ̀ tólọ́] [ni] LT',
                'sameElse' => 'L',
            ),
            'relativeTime' => array(
                'future' => 'ní %s',
                'past' => '%s kọjá',
                's' => 'ìsẹjú aayá die',
                'ss' => 'aayá %d',
                'm' => 'ìsẹjú kan',
                'mm' => 'ìsẹjú %d',
                'h' => 'wákati kan',
                'hh' => 'wákati %d',
                'd' => 'ọjọ́ kan',
                'dd' => 'ọjọ́ %d',
                'M' => 'osù kan',
                'MM' => 'osù %d',
                'y' => 'ọdún kan',
                'yy' => 'ọdún %d',
            ),
            'dayOfMonthOrdinalParse' => '~ọjọ́\s\d{1,2}~u',
            'ordinal' => 'ọjọ́ %d',
            'week' => array('dow' => 1, 'doy' => 4),
        );
    }
}
