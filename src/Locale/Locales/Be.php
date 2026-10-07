<?php

namespace Wilkques\Moment\Locale\Locales;

use Wilkques\Moment\Locale\Locale;

class Be extends Locale
{
    public $abbr = 'be';

    public static function config()
    {
        return array(
            'calendar' => array(
                'sameDay' => '[Сёння ў] LT',
                'nextDay' => '[Заўтра ў] LT',
                'lastDay' => '[Учора ў] LT',
                'sameElse' => 'L',
            ),
            'longDateFormat' => array(
                'LTS' => 'HH:mm:ss',
                'LT' => 'HH:mm',
                'L' => 'DD.MM.YYYY',
                'LL' => 'D MMMM YYYY г.',
                'LLL' => 'D MMMM YYYY г., HH:mm',
                'LLLL' => 'dddd, D MMMM YYYY г., HH:mm',
            ),
            'dayOfMonthOrdinalParse' => '~\\d{1,2}-(і|ы|га)~u',
            'relativeTime' => array(
                'future' => 'праз %s',
                'past' => '%s таму',
                's' => 'некалькі секунд',
                'ss' => '%d seconds',
                'd' => 'дзень',
                'w' => 'a week',
                'ww' => '%d weeks',
                'M' => 'месяц',
                'y' => 'год',
            ),
            'months' => array(
                'format' => array('студзеня', 'лютага', 'сакавіка', 'красавіка', 'траўня', 'чэрвеня', 'ліпеня', 'жніўня', 'верасня', 'кастрычніка', 'лістапада', 'снежня'),
                'standalone' => array('студзень', 'люты', 'сакавік', 'красавік', 'травень', 'чэрвень', 'ліпень', 'жнівень', 'верасень', 'кастрычнік', 'лістапад', 'снежань'),
            ),
            'monthsShort' => array('студ', 'лют', 'сак', 'крас', 'трав', 'чэрв', 'ліп', 'жнів', 'вер', 'каст', 'ліст', 'снеж'),
            'week' => array(
                'dow' => 1,
                'doy' => 7,
            ),
            'weekdays' => array(
                'format' => array('нядзелю', 'панядзелак', 'аўторак', 'сераду', 'чацвер', 'пятніцу', 'суботу'),
                'standalone' => array('нядзеля', 'панядзелак', 'аўторак', 'серада', 'чацвер', 'пятніца', 'субота'),
                'isFormat' => '~\\[ ?[Ууў] ?(?:мінулую|наступную)? ?\\] ?dddd~u',
            ),
            'weekdaysMin' => array('нд', 'пн', 'ат', 'ср', 'чц', 'пт', 'сб'),
            'weekdaysShort' => array('нд', 'пн', 'ат', 'ср', 'чц', 'пт', 'сб'),
            'meridiemParse' => '~ночы|раніцы|дня|вечара~u',
        );
    }

    protected static function plural($word, $num)
    {
        $forms = explode('_', $word);

        return $num % 10 === 1 && $num % 100 !== 11
            ? $forms[0]
            : ($num % 10 >= 2 && $num % 10 <= 4 && ($num % 100 < 10 || $num % 100 >= 20)
                ? $forms[1]
                : $forms[2]);
    }

    public function relativeTime($number, $withoutSuffix, $key, $isFuture)
    {
        $format = array(
            'ss' => $withoutSuffix ? 'секунда_секунды_секунд' : 'секунду_секунды_секунд',
            'mm' => $withoutSuffix ? 'хвіліна_хвіліны_хвілін' : 'хвіліну_хвіліны_хвілін',
            'hh' => $withoutSuffix ? 'гадзіна_гадзіны_гадзін' : 'гадзіну_гадзіны_гадзін',
            'dd' => 'дзень_дні_дзён',
            'MM' => 'месяц_месяцы_месяцаў',
            'yy' => 'год_гады_гадоў',
        );
        if ($key === 'm') {
            return $withoutSuffix ? 'хвіліна' : 'хвіліну';
        } elseif ($key === 'h') {
            return $withoutSuffix ? 'гадзіна' : 'гадзіну';
        } elseif (isset($format[$key]) && $key !== 'ss') {
            return $number . ' ' . self::plural($format[$key], $number);
        }

        return parent::relativeTime($number, $withoutSuffix, $key, $isFuture);
    }

    public function calendar($key, $mom = null, $now = null)
    {
        if ($key === 'nextWeek') {
            return '[У] dddd [ў] LT';
        } elseif ($key === 'lastWeek') {
            switch ($mom->day()) {
                case 0:
                case 3:
                case 5:
                case 6:
                    return '[У мінулую] dddd [ў] LT';
                default:
                    return '[У мінулы] dddd [ў] LT';
            }
        }

        return parent::calendar($key, $mom, $now);
    }

    public function isPM($input)
    {
        return (bool) preg_match('~^(дня|вечара)$~u', $input);
    }

    public function meridiem($hour, $minute, $isLower)
    {
        if ($hour < 4) {
            return 'ночы';
        } elseif ($hour < 12) {
            return 'раніцы';
        } elseif ($hour < 17) {
            return 'дня';
        }

        return 'вечара';
    }

    public function ordinal($number, $token = '')
    {
        switch ($token) {
            case 'M':
            case 'd':
            case 'DDD':
            case 'w':
            case 'W':
                return ($number % 10 === 2 || $number % 10 === 3) && $number % 100 !== 12 && $number % 100 !== 13
                    ? $number . '-і'
                    : $number . '-ы';
            case 'D':
                return $number . '-га';
            default:
                return $number;
        }
    }
}
