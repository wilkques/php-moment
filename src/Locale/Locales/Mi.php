<?php

namespace Wilkques\Moment\Locale\Locales;

use Wilkques\Moment\Locale\Locale;

class Mi extends Locale
{
    public $abbr = 'mi';

    public static function config()
    {
        return array(
            'months' => explode('_', 'Kohi-tāte_Hui-tanguru_Poutū-te-rangi_Paenga-whāwhā_Haratua_Pipiri_Hōngoingoi_Here-turi-kōkā_Mahuru_Whiringa-ā-nuku_Whiringa-ā-rangi_Hakihea'),
            'monthsShort' => explode('_', 'Kohi_Hui_Pou_Pae_Hara_Pipi_Hōngoi_Here_Mahu_Whi-nu_Whi-ra_Haki'),
            'monthsRegex' => '~(?:[\'a-z\x{0101}\x{014D}\x{016B}]+\-?){1,3}~iu',
            'monthsStrictRegex' => '~(?:[\'a-z\x{0101}\x{014D}\x{016B}]+\-?){1,3}~iu',
            'monthsShortRegex' => '~(?:[\'a-z\x{0101}\x{014D}\x{016B}]+\-?){1,3}~iu',
            'monthsShortStrictRegex' => '~(?:[\'a-z\x{0101}\x{014D}\x{016B}]+\-?){1,2}~iu',
            'weekdays' => explode('_', 'Rātapu_Mane_Tūrei_Wenerei_Tāite_Paraire_Hātarei'),
            'weekdaysShort' => explode('_', 'Ta_Ma_Tū_We_Tāi_Pa_Hā'),
            'weekdaysMin' => explode('_', 'Ta_Ma_Tū_We_Tāi_Pa_Hā'),
            'longDateFormat' => array(
                'LT' => 'HH:mm', 'LTS' => 'HH:mm:ss', 'L' => 'DD/MM/YYYY', 'LL' => 'D MMMM YYYY',
                'LLL' => 'D MMMM YYYY [i] HH:mm', 'LLLL' => 'dddd, D MMMM YYYY [i] HH:mm',
            ),
            'calendar' => array(
                'sameDay' => '[i teie mahana, i] LT', 'nextDay' => '[apopo i] LT', 'nextWeek' => 'dddd [i] LT',
                'lastDay' => '[inanahi i] LT', 'lastWeek' => 'dddd [whakamutunga i] LT', 'sameElse' => 'L',
            ),
            'relativeTime' => array(
                'future' => 'i roto i %s', 'past' => '%s i mua', 's' => 'te hēkona ruarua', 'ss' => '%d hēkona',
                'm' => 'he meneti', 'mm' => '%d meneti', 'h' => 'te haora', 'hh' => '%d haora',
                'd' => 'he ra', 'dd' => '%d ra', 'M' => 'he marama', 'MM' => '%d marama',
                'y' => 'he tau', 'yy' => '%d tau',
            ),
            'dayOfMonthOrdinalParse' => '~\d{1,2}º~u',
            'ordinal' => '%dº',
            'week' => array('dow' => 1, 'doy' => 4),
        );
    }
}
