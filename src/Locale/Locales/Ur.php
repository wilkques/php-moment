<?php

namespace Wilkques\Moment\Locale\Locales;

use Wilkques\Moment\Locale\Locale;

class Ur extends Locale
{
    public $abbr = 'ur';

    public static function config()
    {
        $months = explode('_', 'جنوری_فروری_مارچ_اپریل_مئی_جون_جولائی_اگست_ستمبر_اکتوبر_نومبر_دسمبر');
        $days = explode('_', 'اتوار_پیر_منگل_بدھ_جمعرات_جمعہ_ہفتہ');

        return array(
            'months' => $months,
            'monthsShort' => $months,
            'weekdays' => $days,
            'weekdaysShort' => $days,
            'weekdaysMin' => $days,
            'longDateFormat' => array(
                'LT' => 'HH:mm', 'LTS' => 'HH:mm:ss', 'L' => 'DD/MM/YYYY', 'LL' => 'D MMMM YYYY',
                'LLL' => 'D MMMM YYYY HH:mm', 'LLLL' => 'dddd، D MMMM YYYY HH:mm',
            ),
            'meridiemParse' => '~صبح|شام~u',
            'calendar' => array(
                'sameDay' => '[آج بوقت] LT', 'nextDay' => '[کل بوقت] LT', 'nextWeek' => 'dddd [بوقت] LT',
                'lastDay' => '[گذشتہ روز بوقت] LT', 'lastWeek' => '[گذشتہ] dddd [بوقت] LT', 'sameElse' => 'L',
            ),
            'relativeTime' => array(
                'future' => '%s بعد', 'past' => '%s قبل', 's' => 'چند سیکنڈ', 'ss' => '%d سیکنڈ',
                'm' => 'ایک منٹ', 'mm' => '%d منٹ', 'h' => 'ایک گھنٹہ', 'hh' => '%d گھنٹے',
                'd' => 'ایک دن', 'dd' => '%d دن', 'M' => 'ایک ماہ', 'MM' => '%d ماہ',
                'y' => 'ایک سال', 'yy' => '%d سال',
            ),
            'week' => array('dow' => 1, 'doy' => 4),
        );
    }

    public function isPM($input)
    {
        return 'شام' === $input;
    }

    public function meridiem($hour, $minute, $isLower)
    {
        return $hour < 12 ? 'صبح' : 'شام';
    }

    public function preparse($string)
    {
        return str_replace('،', ',', $string);
    }

    public function postformat($string)
    {
        return str_replace(',', '،', $string);
    }
}
