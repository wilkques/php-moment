<?php

namespace Wilkques\Moment\Locale\Locales;

use Wilkques\Moment\Locale\Locale;

class Uk extends Locale
{
    public $abbr = 'uk';

    public static function config()
    {
        return array(
            'months' => array(
                'format' => explode('_', 'січня_лютого_березня_квітня_травня_червня_липня_серпня_вересня_жовтня_листопада_грудня'),
                'standalone' => explode('_', 'січень_лютий_березень_квітень_травень_червень_липень_серпень_вересень_жовтень_листопад_грудень'),
            ),
            'monthsShort' => explode('_', 'січ_лют_бер_квіт_трав_черв_лип_серп_вер_жовт_лист_груд'),
            'weekdaysShort' => explode('_', 'нд_пн_вт_ср_чт_пт_сб'),
            'weekdaysMin' => explode('_', 'нд_пн_вт_ср_чт_пт_сб'),
            'longDateFormat' => array(
                'LT' => 'HH:mm', 'LTS' => 'HH:mm:ss', 'L' => 'DD.MM.YYYY', 'LL' => 'D MMMM YYYY р.',
                'LLL' => 'D MMMM YYYY р., HH:mm', 'LLLL' => 'dddd, D MMMM YYYY р., HH:mm',
            ),
            'calendar' => array('sameElse' => 'L'),
            'relativeTime' => array(
                'future' => 'за %s', 'past' => '%s тому', 's' => 'декілька секунд',
                'h' => 'годину', 'd' => 'день', 'M' => 'місяць', 'y' => 'рік',
            ),
            'meridiemParse' => '~ночі|ранку|дня|вечора~u',
            'dayOfMonthOrdinalParse' => '~\d{1,2}-(й|го)~u',
            'week' => array('dow' => 1, 'doy' => 7),
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
            'ss' => $withoutSuffix ? 'секунда_секунди_секунд' : 'секунду_секунди_секунд',
            'mm' => $withoutSuffix ? 'хвилина_хвилини_хвилин' : 'хвилину_хвилини_хвилин',
            'hh' => $withoutSuffix ? 'година_години_годин' : 'годину_години_годин',
            'dd' => 'день_дні_днів',
            'MM' => 'місяць_місяці_місяців',
            'yy' => 'рік_роки_років',
        );
        if ($key === 'm') {
            return $withoutSuffix ? 'хвилина' : 'хвилину';
        } elseif (isset($format[$key])) {
            return $number . ' ' . self::plural($format[$key], (int) $number);
        }

        return parent::relativeTime($number, $withoutSuffix, $key, $isFuture);
    }

    public function weekdays($m = null, $format = '')
    {
        $nominative = explode('_', 'неділя_понеділок_вівторок_середа_четвер_п’ятниця_субота');
        if ($m === true) {
            return array_merge(array_slice($nominative, 1, 6), array_slice($nominative, 0, 1));
        }
        if ($m === null || $m === false) {
            return $nominative;
        }

        if (preg_match('~(\[[ВвУу]\]) ?dddd~u', (string) $format)) {
            $list = explode('_', 'неділю_понеділок_вівторок_середу_четвер_п’ятницю_суботу');
        } elseif (preg_match('~\[?(?:минулої|наступної)? ?\] ?dddd~u', (string) $format)) {
            $list = explode('_', 'неділі_понеділка_вівторка_середи_четверга_п’ятниці_суботи');
        } else {
            $list = $nominative;
        }

        return $list[self::dayIndex($m)];
    }

    protected function hoursCal($str, $mom)
    {
        return $str . 'о' . ($mom->hour() === 11 ? 'б' : '') . '] LT';
    }

    public function calendar($key, $mom = null, $now = null)
    {
        switch ($key) {
            case 'sameDay':
                return $this->hoursCal('[Сьогодні ', $mom);
            case 'nextDay':
                return $this->hoursCal('[Завтра ', $mom);
            case 'lastDay':
                return $this->hoursCal('[Вчора ', $mom);
            case 'nextWeek':
                return $this->hoursCal('[У] dddd [', $mom);
            case 'lastWeek':
                switch ($mom->day()) {
                    case 0:
                    case 3:
                    case 5:
                    case 6:
                        return $this->hoursCal('[Минулої] dddd [', $mom);
                    default:
                        return $this->hoursCal('[Минулого] dddd [', $mom);
                }
        }

        return parent::calendar($key, $mom, $now);
    }

    public function isPM($input)
    {
        return (bool) preg_match('~^(дня|вечора)$~uD', $input);
    }

    public function meridiem($hour, $minute, $isLower)
    {
        if ($hour < 4) {
            return 'ночі';
        } elseif ($hour < 12) {
            return 'ранку';
        } elseif ($hour < 17) {
            return 'дня';
        }

        return 'вечора';
    }

    public function ordinal($number, $token = '')
    {
        switch ($token) {
            case 'M':
            case 'd':
            case 'DDD':
            case 'w':
            case 'W':
                return $number . '-й';
            case 'D':
                return $number . '-го';
            default:
                return $number;
        }
    }
}
