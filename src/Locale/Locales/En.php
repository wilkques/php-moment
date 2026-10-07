<?php

namespace Wilkques\Moment\Locale\Locales;

use Wilkques\Moment\Locale\Locale;

class En extends Locale
{
    public $abbr = 'en';

    public static function config()
    {
        return array(
            'eras' => array(
                array('since' => '0001-01-01', 'until' => INF, 'offset' => 1, 'name' => 'Anno Domini', 'narrow' => 'AD', 'abbr' => 'AD'),
                array('since' => '0000-12-31', 'until' => -INF, 'offset' => 1, 'name' => 'Before Christ', 'narrow' => 'BC', 'abbr' => 'BC'),
            ),
            'dayOfMonthOrdinalParse' => '~\d{1,2}(th|st|nd|rd)~u',
        );
    }

    public function ordinal($number, $token = '')
    {
        $b = $number % 10;
        $tens = (int) (($number % 100) / 10);

        return $number . ($tens === 1 ? 'th' : ($b === 1 ? 'st' : ($b === 2 ? 'nd' : ($b === 3 ? 'rd' : 'th'))));
    }
}
