<?php

namespace Wilkques\Moment\Locale\Locales;

use Wilkques\Moment\Locale\Locale;

class Mk extends Locale
{
    public $abbr = 'mk';

    public static function config()
    {
        return array(
            'months' => array('јануари', 'февруари', 'март', 'април', 'мај', 'јуни', 'јули', 'август', 'септември', 'октомври', 'ноември', 'декември'),
            'monthsShort' => array('јан', 'фев', 'мар', 'апр', 'мај', 'јун', 'јул', 'авг', 'сеп', 'окт', 'ное', 'дек'),
            'weekdays' => array('недела', 'понеделник', 'вторник', 'среда', 'четврток', 'петок', 'сабота'),
            'weekdaysShort' => array('нед', 'пон', 'вто', 'сре', 'чет', 'пет', 'саб'),
            'weekdaysMin' => array('нe', 'пo', 'вт', 'ср', 'че', 'пе', 'сa'),
            'longDateFormat' => array(
                'LT' => 'H:mm', 'LTS' => 'H:mm:ss', 'L' => 'D.MM.YYYY', 'LL' => 'D MMMM YYYY',
                'LLL' => 'D MMMM YYYY H:mm', 'LLLL' => 'dddd, D MMMM YYYY H:mm',
            ),
            'calendar' => array(
                'sameDay' => '[Денес во] LT', 'nextDay' => '[Утре во] LT', 'nextWeek' => '[Во] dddd [во] LT',
                'lastDay' => '[Вчера во] LT', 'lastWeek' => '', 'sameElse' => 'L',
            ),
            'relativeTime' => array(
                'future' => 'за %s', 'past' => 'пред %s', 's' => 'неколку секунди', 'ss' => '%d секунди',
                'm' => 'една минута', 'mm' => '%d минути', 'h' => 'еден час', 'hh' => '%d часа',
                'd' => 'еден ден', 'dd' => '%d дена', 'M' => 'еден месец', 'MM' => '%d месеци',
                'y' => 'една година', 'yy' => '%d години',
            ),
            'dayOfMonthOrdinalParse' => '~\d{1,2}-(ев|ен|ти|ви|ри|ми)~u',
            'week' => array('dow' => 1, 'doy' => 7),
        );
    }

    public function calendar($key, $mom = null, $now = null)
    {
        if ($key === 'lastWeek') {
            switch ($mom->day()) {
                case 0:
                case 3:
                case 6:
                    return '[Изминатата] dddd [во] LT';
                default:
                    return '[Изминатиот] dddd [во] LT';
            }
        }

        return parent::calendar($key, $mom, $now);
    }

    public function ordinal($number, $token = '')
    {
        $lastDigit = $number % 10;
        $last2Digits = $number % 100;
        if ($number === 0) {
            return $number . '-ев';
        } elseif ($last2Digits === 0) {
            return $number . '-ен';
        } elseif ($last2Digits > 10 && $last2Digits < 20) {
            return $number . '-ти';
        } elseif ($lastDigit === 1) {
            return $number . '-ви';
        } elseif ($lastDigit === 2) {
            return $number . '-ри';
        } elseif ($lastDigit === 7 || $lastDigit === 8) {
            return $number . '-ми';
        }

        return $number . '-ти';
    }
}
