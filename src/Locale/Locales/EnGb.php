<?php

namespace Wilkques\Moment\Locale\Locales;

use Wilkques\Moment\Locale\Locale;

class EnGb extends Locale
{
    public $abbr = 'en-gb';

    public static function config()
    {
        return array(
            'longDateFormat' => array(
                'LT' => 'HH:mm',
                'LTS' => 'HH:mm:ss',
                'L' => 'DD/MM/YYYY',
                'LL' => 'D MMMM YYYY',
                'LLL' => 'D MMMM YYYY HH:mm',
                'LLLL' => 'dddd, D MMMM YYYY HH:mm',
            ),
            'dayOfMonthOrdinalParse' => '~\d{1,2}(st|nd|rd|th)~u',
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
