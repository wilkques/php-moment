<?php

namespace Wilkques\Moment\Locale\Locales;

use Wilkques\Moment\Locale\Locale;

class Ru extends Locale
{
    public $abbr = 'ru';

    protected static function plural($word, $num)
    {
        $forms = explode('_', $word);

        return $num % 10 === 1 && $num % 100 !== 11
            ? $forms[0]
            : ($num % 10 >= 2 && $num % 10 <= 4 && ($num % 100 < 10 || $num % 100 >= 20)
                ? $forms[1]
                : $forms[2]);
    }

    public static function config()
    {
        $monthsParse = array(
            '~^янв~iu', '~^фев~iu', '~^мар~iu', '~^апр~iu', '~^ма[йя]~iu', '~^июн~iu',
            '~^июл~iu', '~^авг~iu', '~^сен~iu', '~^окт~iu', '~^ноя~iu', '~^дек~iu',
        );
        $all = '~^(январ[ья]|янв\.?|феврал[ья]|февр?\.?|марта?|мар\.?|апрел[ья]|апр\.?|ма[йя]|июн[ья]|июн\.?|июл[ья]|июл\.?|августа?|авг\.?|сентябр[ья]|сент?\.?|октябр[ья]|окт\.?|ноябр[ья]|нояб?\.?|декабр[ья]|дек\.?)~iu';

        return array(
            'months' => array(
                'format' => array('января', 'февраля', 'марта', 'апреля', 'мая', 'июня', 'июля', 'августа', 'сентября', 'октября', 'ноября', 'декабря'),
                'standalone' => array('январь', 'февраль', 'март', 'апрель', 'май', 'июнь', 'июль', 'август', 'сентябрь', 'октябрь', 'ноябрь', 'декабрь'),
            ),
            'monthsShort' => array(
                'format' => array('янв.', 'февр.', 'мар.', 'апр.', 'мая', 'июня', 'июля', 'авг.', 'сент.', 'окт.', 'нояб.', 'дек.'),
                'standalone' => array('янв.', 'февр.', 'март', 'апр.', 'май', 'июнь', 'июль', 'авг.', 'сент.', 'окт.', 'нояб.', 'дек.'),
            ),
            'weekdays' => array(
                'standalone' => array('воскресенье', 'понедельник', 'вторник', 'среда', 'четверг', 'пятница', 'суббота'),
                'format' => array('воскресенье', 'понедельник', 'вторник', 'среду', 'четверг', 'пятницу', 'субботу'),
                'isFormat' => '~\[ ?[Вв] ?(?:прошлую|следующую|эту)? ?\] ?dddd~u',
            ),
            'weekdaysShort' => array('вс', 'пн', 'вт', 'ср', 'чт', 'пт', 'сб'),
            'weekdaysMin' => array('вс', 'пн', 'вт', 'ср', 'чт', 'пт', 'сб'),
            'monthsParse' => $monthsParse,
            'longMonthsParse' => $monthsParse,
            'shortMonthsParse' => $monthsParse,
            'monthsRegex' => $all,
            'monthsShortRegex' => $all,
            'monthsStrictRegex' => '~^(январ[яь]|феврал[яь]|марта?|апрел[яь]|ма[яй]|июн[яь]|июл[яь]|августа?|сентябр[яь]|октябр[яь]|ноябр[яь]|декабр[яь])~iu',
            'monthsShortStrictRegex' => '~^(янв\.|февр?\.|мар[т.]|апр\.|ма[яй]|июн[ья.]|июл[ья.]|авг\.|сент?\.|окт\.|нояб?\.|дек\.)~iu',
            'longDateFormat' => array(
                'LT' => 'H:mm', 'LTS' => 'H:mm:ss', 'L' => 'DD.MM.YYYY', 'LL' => 'D MMMM YYYY г.',
                'LLL' => 'D MMMM YYYY г., H:mm', 'LLLL' => 'dddd, D MMMM YYYY г., H:mm',
            ),
            'calendar' => array(
                'sameDay' => '[Сегодня, в] LT', 'nextDay' => '[Завтра, в] LT', 'lastDay' => '[Вчера, в] LT', 'sameElse' => 'L',
            ),
            'relativeTime' => array(
                'future' => 'через %s', 'past' => '%s назад', 's' => 'несколько секунд',
                'h' => 'час', 'd' => 'день', 'w' => 'неделя', 'M' => 'месяц', 'y' => 'год',
            ),
            'meridiemParse' => '~ночи|утра|дня|вечера~iu',
            'dayOfMonthOrdinalParse' => '~\d{1,2}-(й|го|я)~u',
            'week' => array('dow' => 1, 'doy' => 4),
        );
    }

    public function calendar($key, $mom = null, $now = null)
    {
        if ($key === 'nextWeek' || $key === 'lastWeek') {
            $next = $key === 'nextWeek';
            $day = $mom->day();
            if ($now->week() !== $mom->week()) {
                $w = $next ? 'следующ' : 'прошл';
                switch ($day) {
                    case 0:
                        return '[В ' . ($next ? 'следующее' : 'прошлое') . '] dddd, [в] LT';
                    case 1:
                    case 2:
                    case 4:
                        return '[В ' . ($next ? 'следующий' : 'прошлый') . '] dddd, [в] LT';
                    default:
                        return '[В ' . $w . 'ую] dddd, [в] LT';
                }
            }

            return $day === 2 ? '[Во] dddd, [в] LT' : '[В] dddd, [в] LT';
        }

        return parent::calendar($key, $mom, $now);
    }

    public function relativeTime($number, $withoutSuffix, $key, $isFuture)
    {
        $format = array(
            'ss' => $withoutSuffix ? 'секунда_секунды_секунд' : 'секунду_секунды_секунд',
            'mm' => $withoutSuffix ? 'минута_минуты_минут' : 'минуту_минуты_минут',
            'hh' => 'час_часа_часов',
            'dd' => 'день_дня_дней',
            'ww' => 'неделя_недели_недель',
            'MM' => 'месяц_месяца_месяцев',
            'yy' => 'год_года_лет',
        );
        if ($key === 'm') {
            return $withoutSuffix ? 'минута' : 'минуту';
        }
        if (isset($format[$key])) {
            return $number . ' ' . self::plural($format[$key], (int) $number);
        }

        return parent::relativeTime($number, $withoutSuffix, $key, $isFuture);
    }

    public function isPM($input)
    {
        return (bool) preg_match('~^(дня|вечера)$~u', $input);
    }

    public function meridiem($hour, $minute, $isLower)
    {
        if ($hour < 4) {
            return 'ночи';
        } elseif ($hour < 12) {
            return 'утра';
        } elseif ($hour < 17) {
            return 'дня';
        }

        return 'вечера';
    }

    public function ordinal($number, $token = '')
    {
        switch ($token) {
            case 'M':
            case 'd':
            case 'DDD':
                return $number . '-й';
            case 'D':
                return $number . '-го';
            case 'w':
            case 'W':
                return $number . '-я';
            default:
                return $number;
        }
    }
}
