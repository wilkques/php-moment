<?php

namespace Wilkques\Moment\Locale\Locales;

use Wilkques\Moment\Locale\Locale;

class Fa extends Locale
{
    public $abbr = 'fa';

    public static function config()
    {
        return array(
            'calendar' => array(
                'sameDay' => '[امروز ساعت] LT',
                'nextDay' => '[فردا ساعت] LT',
                'nextWeek' => 'dddd [ساعت] LT',
                'lastDay' => '[دیروز ساعت] LT',
                'lastWeek' => 'dddd [پیش] [ساعت] LT',
                'sameElse' => 'L',
            ),
            'longDateFormat' => array(
                'LTS' => 'HH:mm:ss',
                'LT' => 'HH:mm',
                'L' => 'DD/MM/YYYY',
                'LL' => 'D MMMM YYYY',
                'LLL' => 'D MMMM YYYY HH:mm',
                'LLLL' => 'dddd, D MMMM YYYY HH:mm',
            ),
            'ordinal' => '%dم',
            'dayOfMonthOrdinalParse' => '~\\d{1,2}م~u',
            'relativeTime' => array(
                'future' => 'در %s',
                'past' => '%s پیش',
                's' => 'چند ثانیه',
                'ss' => '%d ثانیه',
                'm' => 'یک دقیقه',
                'mm' => '%d دقیقه',
                'h' => 'یک ساعت',
                'hh' => '%d ساعت',
                'd' => 'یک روز',
                'dd' => '%d روز',
                'w' => 'a week',
                'ww' => '%d weeks',
                'M' => 'یک ماه',
                'MM' => '%d ماه',
                'y' => 'یک سال',
                'yy' => '%d سال',
            ),
            'months' => array('ژانویه', 'فوریه', 'مارس', 'آوریل', 'مه', 'ژوئن', 'ژوئیه', 'اوت', 'سپتامبر', 'اکتبر', 'نوامبر', 'دسامبر'),
            'monthsShort' => array('ژانویه', 'فوریه', 'مارس', 'آوریل', 'مه', 'ژوئن', 'ژوئیه', 'اوت', 'سپتامبر', 'اکتبر', 'نوامبر', 'دسامبر'),
            'week' => array(
                'dow' => 6,
                'doy' => 12,
            ),
            'weekdays' => array('یک‌شنبه', 'دوشنبه', 'سه‌شنبه', 'چهارشنبه', 'پنج‌شنبه', 'جمعه', 'شنبه'),
            'weekdaysMin' => array('ی', 'د', 'س', 'چ', 'پ', 'ج', 'ش'),
            'weekdaysShort' => array('یک‌شنبه', 'دوشنبه', 'سه‌شنبه', 'چهارشنبه', 'پنج‌شنبه', 'جمعه', 'شنبه'),
            'meridiemParse' => '~قبل از ظهر|بعد از ظهر~u',
            'weekdaysParseExact' => true,
        );
    }

    private static $symbolMap = array('1' => '۱', '2' => '۲', '3' => '۳', '4' => '۴', '5' => '۵', '6' => '۶', '7' => '۷', '8' => '۸', '9' => '۹', '0' => '۰');

    public function isPM($input)
    {
        return (bool) preg_match('~بعد از ظهر~u', $input);
    }

    public function meridiem($hour, $minute, $isLower)
    {
        return $hour < 12 ? 'قبل از ظهر' : 'بعد از ظهر';
    }

    public function preparse($string)
    {
        $map = array('۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4', '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9', '۰' => '0', '،' => ',');

        return strtr($string, $map);
    }

    public function postformat($string)
    {
        $map = self::$symbolMap;
        $map[','] = '،';

        return strtr($string, $map);
    }
}
