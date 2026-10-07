<?php

namespace Wilkques\Moment\Locale\Locales;

use Wilkques\Moment\Locale\Locale;

class TzmLatn extends Locale
{
    public $abbr = 'tzm-latn';

    public static function config()
    {
        return array(
            'months' => explode('_', 'innayr_brˤayrˤ_marˤsˤ_ibrir_mayyw_ywnyw_ywlywz_ɣwšt_šwtanbir_ktˤwbrˤ_nwwanbir_dwjnbir'),
            'monthsShort' => explode('_', 'innayr_brˤayrˤ_marˤsˤ_ibrir_mayyw_ywnyw_ywlywz_ɣwšt_šwtanbir_ktˤwbrˤ_nwwanbir_dwjnbir'),
            'weekdays' => explode('_', 'asamas_aynas_asinas_akras_akwas_asimwas_asiḍyas'),
            'weekdaysShort' => explode('_', 'asamas_aynas_asinas_akras_akwas_asimwas_asiḍyas'),
            'weekdaysMin' => explode('_', 'asamas_aynas_asinas_akras_akwas_asimwas_asiḍyas'),
            'longDateFormat' => array(
                'LT' => 'HH:mm', 'LTS' => 'HH:mm:ss', 'L' => 'DD/MM/YYYY', 'LL' => 'D MMMM YYYY',
                'LLL' => 'D MMMM YYYY HH:mm', 'LLLL' => 'dddd D MMMM YYYY HH:mm',
            ),
            'calendar' => array(
                'sameDay' => '[asdkh g] LT', 'nextDay' => '[aska g] LT', 'nextWeek' => 'dddd [g] LT',
                'lastDay' => '[assant g] LT', 'lastWeek' => 'dddd [g] LT', 'sameElse' => 'L',
            ),
            'relativeTime' => array(
                'future' => 'dadkh s yan %s', 'past' => 'yan %s', 's' => 'imik', 'ss' => '%d imik',
                'm' => 'minuḍ', 'mm' => '%d minuḍ', 'h' => 'saɛa', 'hh' => '%d tassaɛin',
                'd' => 'ass', 'dd' => '%d ossan', 'M' => 'ayowr', 'MM' => '%d iyyirn', 'y' => 'asgas', 'yy' => '%d isgasn',
            ),
            'week' => array('dow' => 6, 'doy' => 12),
        );
    }
}
