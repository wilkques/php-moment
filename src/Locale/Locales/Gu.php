<?php

namespace Wilkques\Moment\Locale\Locales;

use Wilkques\Moment\Locale\Locale;

class Gu extends Locale
{
    public $abbr = 'gu';

    private static $symbolMap = array(
        '1' => '૧', '2' => '૨', '3' => '૩', '4' => '૪', '5' => '૫',
        '6' => '૬', '7' => '૭', '8' => '૮', '9' => '૯', '0' => '૦',
    );

    public static function config()
    {
        return array(
            'months' => explode('_', 'જાન્યુઆરી_ફેબ્રુઆરી_માર્ચ_એપ્રિલ_મે_જૂન_જુલાઈ_ઑગસ્ટ_સપ્ટેમ્બર_ઑક્ટ્બર_નવેમ્બર_ડિસેમ્બર'),
            'monthsShort' => explode('_', 'જાન્યુ._ફેબ્રુ._માર્ચ_એપ્રિ._મે_જૂન_જુલા._ઑગ._સપ્ટે._ઑક્ટ્._નવે._ડિસે.'),
            'monthsParseExact' => true,
            'weekdays' => explode('_', 'રવિવાર_સોમવાર_મંગળવાર_બુધ્વાર_ગુરુવાર_શુક્રવાર_શનિવાર'),
            'weekdaysShort' => explode('_', 'રવિ_સોમ_મંગળ_બુધ્_ગુરુ_શુક્ર_શનિ'),
            'weekdaysMin' => explode('_', 'ર_સો_મં_બુ_ગુ_શુ_શ'),
            'longDateFormat' => array(
                'LT' => 'A h:mm વાગ્યે', 'LTS' => 'A h:mm:ss વાગ્યે', 'L' => 'DD/MM/YYYY', 'LL' => 'D MMMM YYYY',
                'LLL' => 'D MMMM YYYY, A h:mm વાગ્યે', 'LLLL' => 'dddd, D MMMM YYYY, A h:mm વાગ્યે',
            ),
            'calendar' => array(
                'sameDay' => '[આજ] LT', 'nextDay' => '[કાલે] LT', 'nextWeek' => 'dddd, LT',
                'lastDay' => '[ગઇકાલે] LT', 'lastWeek' => '[પાછલા] dddd, LT', 'sameElse' => 'L',
            ),
            'relativeTime' => array(
                'future' => '%s મા', 'past' => '%s પહેલા',
                's' => 'અમુક પળો', 'ss' => '%d સેકંડ',
                'm' => 'એક મિનિટ', 'mm' => '%d મિનિટ',
                'h' => 'એક કલાક', 'hh' => '%d કલાક',
                'd' => 'એક દિવસ', 'dd' => '%d દિવસ',
                'M' => 'એક મહિનો', 'MM' => '%d મહિનો',
                'y' => 'એક વર્ષ', 'yy' => '%d વર્ષ',
            ),
            'meridiemParse' => '~રાત|બપોર|સવાર|સાંજ~u',
            'week' => array('dow' => 0, 'doy' => 6),
        );
    }

    public function preparse($string)
    {
        return strtr($string, array_flip(self::$symbolMap));
    }

    public function postformat($string)
    {
        return strtr($string, self::$symbolMap);
    }

    public function meridiemHour($hour, $meridiem)
    {
        if ($hour === 12) {
            $hour = 0;
        }
        if ($meridiem === 'રાત') {
            return $hour < 4 ? $hour : $hour + 12;
        } elseif ($meridiem === 'સવાર') {
            return $hour;
        } elseif ($meridiem === 'બપોર') {
            return $hour >= 10 ? $hour : $hour + 12;
        } elseif ($meridiem === 'સાંજ') {
            return $hour + 12;
        }

        return null;
    }

    public function meridiem($hour, $minute, $isLower)
    {
        if ($hour < 4) {
            return 'રાત';
        } elseif ($hour < 10) {
            return 'સવાર';
        } elseif ($hour < 17) {
            return 'બપોર';
        } elseif ($hour < 20) {
            return 'સાંજ';
        }

        return 'રાત';
    }
}
