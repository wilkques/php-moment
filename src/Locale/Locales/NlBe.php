<?php

namespace Wilkques\Moment\Locale\Locales;

use Wilkques\Moment\Locale\Locale;

class NlBe extends Locale
{
    public $abbr = 'nl-be';

    public static function config()
    {
        $monthsParse = array(
            '~^jan~iu', '~^feb~iu', '~^(maart|mrt\.?)$~iu', '~^apr~iu', '~^mei$~iu', '~^jun[i.]?$~iu',
            '~^jul[i.]?$~iu', '~^aug~iu', '~^sep~iu', '~^okt~iu', '~^nov~iu', '~^dec~iu',
        );
        $monthsRegex = '~^(januari|februari|maart|april|mei|ju[nl]i|augustus|september|oktober|november|december|jan\.?|feb\.?|mrt\.?|apr\.?|ju[nl]\.?|aug\.?|sep\.?|okt\.?|nov\.?|dec\.?)~iu';

        return array(
            'months' => explode('_', 'januari_februari_maart_april_mei_juni_juli_augustus_september_oktober_november_december'),
            'monthsRegex' => $monthsRegex,
            'monthsShortRegex' => $monthsRegex,
            'monthsStrictRegex' => '~^(januari|februari|maart|april|mei|ju[nl]i|augustus|september|oktober|november|december)~iu',
            'monthsShortStrictRegex' => '~^(jan\.?|feb\.?|mrt\.?|apr\.?|mei|ju[nl]\.?|aug\.?|sep\.?|okt\.?|nov\.?|dec\.?)~iu',
            'monthsParse' => $monthsParse,
            'longMonthsParse' => $monthsParse,
            'shortMonthsParse' => $monthsParse,
            'weekdays' => explode('_', 'zondag_maandag_dinsdag_woensdag_donderdag_vrijdag_zaterdag'),
            'weekdaysShort' => explode('_', 'zo._ma._di._wo._do._vr._za.'),
            'weekdaysMin' => explode('_', 'zo_ma_di_wo_do_vr_za'),
            'weekdaysParseExact' => true,
            'longDateFormat' => array(
                'LT' => 'HH:mm', 'LTS' => 'HH:mm:ss', 'L' => 'DD/MM/YYYY', 'LL' => 'D MMMM YYYY',
                'LLL' => 'D MMMM YYYY HH:mm', 'LLLL' => 'dddd D MMMM YYYY HH:mm',
            ),
            'calendar' => array(
                'sameDay' => '[vandaag om] LT', 'nextDay' => '[morgen om] LT', 'nextWeek' => 'dddd [om] LT',
                'lastDay' => '[gisteren om] LT', 'lastWeek' => '[afgelopen] dddd [om] LT', 'sameElse' => 'L',
            ),
            'relativeTime' => array(
                'future' => 'over %s', 'past' => '%s geleden', 's' => 'een paar seconden', 'ss' => '%d seconden',
                'm' => 'één minuut', 'mm' => '%d minuten', 'h' => 'één uur', 'hh' => '%d uur',
                'd' => 'één dag', 'dd' => '%d dagen', 'M' => 'één maand', 'MM' => '%d maanden',
                'y' => 'één jaar', 'yy' => '%d jaar',
            ),
            'dayOfMonthOrdinalParse' => '~\d{1,2}(ste|de)~u',
            'week' => array('dow' => 1, 'doy' => 4),
        );
    }

    public function monthsShort($m = null, $format = '')
    {
        if ($m === null) {
            return explode('_', 'jan._feb._mrt._apr._mei_jun._jul._aug._sep._okt._nov._dec.');
        }
        if (preg_match('~-MMM-~u', (string) $format)) {
            $list = explode('_', 'jan_feb_mrt_apr_mei_jun_jul_aug_sep_okt_nov_dec');
        } else {
            $list = explode('_', 'jan._feb._mrt._apr._mei_jun._jul._aug._sep._okt._nov._dec.');
        }

        return $list[self::monthIndex($m)];
    }

    public function ordinal($number, $token = '')
    {
        return $number . ($number === 1 || $number === 8 || $number >= 20 ? 'ste' : 'de');
    }
}
