<?php

namespace Wilkques\Moment\Locale\Locales;

use Wilkques\Moment\Locale\Locale;

class EnIe extends Locale
{
    public $abbr = 'en-ie';

    public static function config()
    {
        return array(
            'longDateFormat' => array(
                'LT' => 'HH:mm', 'LTS' => 'HH:mm:ss', 'L' => 'DD/MM/YYYY',
                'LL' => 'D MMMM YYYY', 'LLL' => 'D MMMM YYYY HH:mm', 'LLLL' => 'dddd D MMMM YYYY HH:mm',
            ),
            'dayOfMonthOrdinalParse' => '~\d{1,2}(st|nd|rd|th)~u',
            'week' => array('dow' => 1, 'doy' => 4),
        );
    }

    public function ordinal($number, $token = '')
    {
        $b = $number % 10;
        if ((int) (($number % 100) / 10) === 1) {
            $output = 'th';
        } elseif ($b === 1) {
            $output = 'st';
        } elseif ($b === 2) {
            $output = 'nd';
        } elseif ($b === 3) {
            $output = 'rd';
        } else {
            $output = 'th';
        }

        return $number . $output;
    }
}
