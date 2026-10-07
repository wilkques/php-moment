<?php

namespace Wilkques\Moment\Locale\Locales;

use Wilkques\Moment\Locale\Locale;

class Mn extends Locale
{
    public $abbr = 'mn';

    public static function config()
    {
        return array(
            'months' => array('Нэгдүгээр сар', 'Хоёрдугаар сар', 'Гуравдугаар сар', 'Дөрөвдүгээр сар', 'Тавдугаар сар', 'Зургадугаар сар', 'Долдугаар сар', 'Наймдугаар сар', 'Есдүгээр сар', 'Аравдугаар сар', 'Арван нэгдүгээр сар', 'Арван хоёрдугаар сар'),
            'monthsShort' => array('1 сар', '2 сар', '3 сар', '4 сар', '5 сар', '6 сар', '7 сар', '8 сар', '9 сар', '10 сар', '11 сар', '12 сар'),
            'monthsParseExact' => true,
            'weekdays' => array('Ням', 'Даваа', 'Мягмар', 'Лхагва', 'Пүрэв', 'Баасан', 'Бямба'),
            'weekdaysShort' => array('Ням', 'Дав', 'Мяг', 'Лха', 'Пүр', 'Баа', 'Бям'),
            'weekdaysMin' => array('Ня', 'Да', 'Мя', 'Лх', 'Пү', 'Ба', 'Бя'),
            'weekdaysParseExact' => true,
            'longDateFormat' => array(
                'LT' => 'HH:mm', 'LTS' => 'HH:mm:ss', 'L' => 'YYYY-MM-DD', 'LL' => 'YYYY оны MMMMын D',
                'LLL' => 'YYYY оны MMMMын D HH:mm', 'LLLL' => 'dddd, YYYY оны MMMMын D HH:mm',
            ),
            'meridiemParse' => '~ҮӨ|ҮХ~iu',
            'calendar' => array(
                'sameDay' => '[Өнөөдөр] LT', 'nextDay' => '[Маргааш] LT', 'nextWeek' => '[Ирэх] dddd LT',
                'lastDay' => '[Өчигдөр] LT', 'lastWeek' => '[Өнгөрсөн] dddd LT', 'sameElse' => 'L',
            ),
            'relativeTime' => array(
                'future' => '%s дараа', 'past' => '%s өмнө',
                's' => '', 'ss' => '', 'm' => '', 'mm' => '', 'h' => '', 'hh' => '',
                'd' => '', 'dd' => '', 'M' => '', 'MM' => '', 'y' => '', 'yy' => '',
            ),
            'dayOfMonthOrdinalParse' => '~\d{1,2} өдөр~u',
        );
    }

    public function relativeTime($number, $withoutSuffix, $key, $isFuture)
    {
        if ($key === 'w' || $key === 'ww') {
            return parent::relativeTime($number, $withoutSuffix, $key, $isFuture); // not set in the JS locale: moment's defaults
        }
        switch ($key) {
            case 's':
                return $withoutSuffix ? 'хэдхэн секунд' : 'хэдхэн секундын';
            case 'ss':
                return $number . ($withoutSuffix ? ' секунд' : ' секундын');
            case 'm':
            case 'mm':
                return $number . ($withoutSuffix ? ' минут' : ' минутын');
            case 'h':
            case 'hh':
                return $number . ($withoutSuffix ? ' цаг' : ' цагийн');
            case 'd':
            case 'dd':
                return $number . ($withoutSuffix ? ' өдөр' : ' өдрийн');
            case 'M':
            case 'MM':
                return $number . ($withoutSuffix ? ' сар' : ' сарын');
            case 'y':
            case 'yy':
                return $number . ($withoutSuffix ? ' жил' : ' жилийн');
        }

        return (string) $number;
    }

    public function ordinal($number, $token = '')
    {
        switch ($token) {
            case 'd':
            case 'D':
            case 'DDD':
                return $number . ' өдөр';
            default:
                return $number;
        }
    }

    public function isPM($input)
    {
        return $input === 'ҮХ';
    }

    public function meridiem($hour, $minute, $isLower)
    {
        return $hour < 12 ? 'ҮӨ' : 'ҮХ';
    }
}
