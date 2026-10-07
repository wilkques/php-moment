<?php

namespace Wilkques\Moment\Locale\Locales;

use Wilkques\Moment\Locale\Locale;

class PaIn extends Locale
{
    public $abbr = 'pa-in';

    public static function config()
    {
        return array(
            'calendar' => array(
                'sameDay' => '[ਅਜ] LT',
                'nextDay' => '[ਕਲ] LT',
                'nextWeek' => '[ਅਗਲਾ] dddd, LT',
                'lastDay' => '[ਕਲ] LT',
                'lastWeek' => '[ਪਿਛਲੇ] dddd, LT',
                'sameElse' => 'L',
            ),
            'longDateFormat' => array(
                'LTS' => 'A h:mm:ss ਵਜੇ',
                'LT' => 'A h:mm ਵਜੇ',
                'L' => 'DD/MM/YYYY',
                'LL' => 'D MMMM YYYY',
                'LLL' => 'D MMMM YYYY, A h:mm ਵਜੇ',
                'LLLL' => 'dddd, D MMMM YYYY, A h:mm ਵਜੇ',
            ),
            'ordinal' => '%d',
            'dayOfMonthOrdinalParse' => '~\\d{1,2}~u',
            'relativeTime' => array(
                'future' => '%s ਵਿੱਚ',
                'past' => '%s ਪਿਛਲੇ',
                's' => 'ਕੁਝ ਸਕਿੰਟ',
                'ss' => '%d ਸਕਿੰਟ',
                'm' => 'ਇਕ ਮਿੰਟ',
                'mm' => '%d ਮਿੰਟ',
                'h' => 'ਇੱਕ ਘੰਟਾ',
                'hh' => '%d ਘੰਟੇ',
                'd' => 'ਇੱਕ ਦਿਨ',
                'dd' => '%d ਦਿਨ',
                'w' => 'a week',
                'ww' => '%d weeks',
                'M' => 'ਇੱਕ ਮਹੀਨਾ',
                'MM' => '%d ਮਹੀਨੇ',
                'y' => 'ਇੱਕ ਸਾਲ',
                'yy' => '%d ਸਾਲ',
            ),
            'months' => array('ਜਨਵਰੀ', 'ਫ਼ਰਵਰੀ', 'ਮਾਰਚ', 'ਅਪ੍ਰੈਲ', 'ਮਈ', 'ਜੂਨ', 'ਜੁਲਾਈ', 'ਅਗਸਤ', 'ਸਤੰਬਰ', 'ਅਕਤੂਬਰ', 'ਨਵੰਬਰ', 'ਦਸੰਬਰ'),
            'monthsShort' => array('ਜਨਵਰੀ', 'ਫ਼ਰਵਰੀ', 'ਮਾਰਚ', 'ਅਪ੍ਰੈਲ', 'ਮਈ', 'ਜੂਨ', 'ਜੁਲਾਈ', 'ਅਗਸਤ', 'ਸਤੰਬਰ', 'ਅਕਤੂਬਰ', 'ਨਵੰਬਰ', 'ਦਸੰਬਰ'),
            'week' => array(
                'dow' => 0,
                'doy' => 6,
            ),
            'weekdays' => array('ਐਤਵਾਰ', 'ਸੋਮਵਾਰ', 'ਮੰਗਲਵਾਰ', 'ਬੁਧਵਾਰ', 'ਵੀਰਵਾਰ', 'ਸ਼ੁੱਕਰਵਾਰ', 'ਸ਼ਨੀਚਰਵਾਰ'),
            'weekdaysMin' => array('ਐਤ', 'ਸੋਮ', 'ਮੰਗਲ', 'ਬੁਧ', 'ਵੀਰ', 'ਸ਼ੁਕਰ', 'ਸ਼ਨੀ'),
            'weekdaysShort' => array('ਐਤ', 'ਸੋਮ', 'ਮੰਗਲ', 'ਬੁਧ', 'ਵੀਰ', 'ਸ਼ੁਕਰ', 'ਸ਼ਨੀ'),
            'meridiemParse' => '~ਰਾਤ|ਸਵੇਰ|ਦੁਪਹਿਰ|ਸ਼ਾਮ~u',
        );
    }

    public function preparse($string)
    {
        return strtr($string, array('੧' => '1', '੨' => '2', '੩' => '3', '੪' => '4', '੫' => '5', '੬' => '6', '੭' => '7', '੮' => '8', '੯' => '9', '੦' => '0'));
    }

    public function postformat($string)
    {
        return strtr($string, array('1' => '੧', '2' => '੨', '3' => '੩', '4' => '੪', '5' => '੫', '6' => '੬', '7' => '੭', '8' => '੮', '9' => '੯', '0' => '੦'));
    }

    public function meridiemHour($hour, $meridiem)
    {
        if ($hour === 12) {
            $hour = 0;
        }
        if ($meridiem === 'ਰਾਤ') {
            return $hour < 4 ? $hour : $hour + 12;
        } elseif ($meridiem === 'ਸਵੇਰ') {
            return $hour;
        } elseif ($meridiem === 'ਦੁਪਹਿਰ') {
            return $hour >= 10 ? $hour : $hour + 12;
        } elseif ($meridiem === 'ਸ਼ਾਮ') {
            return $hour + 12;
        }

        return null;
    }

    public function meridiem($hour, $minute, $isLower)
    {
        if ($hour < 4) {
            return 'ਰਾਤ';
        } elseif ($hour < 10) {
            return 'ਸਵੇਰ';
        } elseif ($hour < 17) {
            return 'ਦੁਪਹਿਰ';
        } elseif ($hour < 20) {
            return 'ਸ਼ਾਮ';
        }

        return 'ਰਾਤ';
    }
}
