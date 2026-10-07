<?php

namespace Wilkques\Moment\Locale\Locales;

use Wilkques\Moment\Locale\Locale;

class Bg extends Locale
{
    public $abbr = 'bg';

    public static function config()
    {
        return array(
            'months' => explode('_', 'януари_февруари_март_април_май_юни_юли_август_септември_октомври_ноември_декември'),
            'monthsShort' => explode('_', 'яну_фев_мар_апр_май_юни_юли_авг_сеп_окт_ное_дек'),
            'weekdays' => explode('_', 'неделя_понеделник_вторник_сряда_четвъртък_петък_събота'),
            'weekdaysShort' => explode('_', 'нед_пон_вто_сря_чет_пет_съб'),
            'weekdaysMin' => explode('_', 'нд_пн_вт_ср_чт_пт_сб'),
            'longDateFormat' => array(
                'LT' => 'H:mm', 'LTS' => 'H:mm:ss', 'L' => 'D.MM.YYYY', 'LL' => 'D MMMM YYYY',
                'LLL' => 'D MMMM YYYY H:mm', 'LLLL' => 'dddd, D MMMM YYYY H:mm',
            ),
            'calendar' => array(
                'sameDay' => '[Днес в] LT', 'nextDay' => '[Утре в] LT', 'nextWeek' => 'dddd [в] LT',
                'lastDay' => '[Вчера в] LT', 'sameElse' => 'L',
            ),
            'relativeTime' => array(
                'future' => 'след %s', 'past' => 'преди %s', 's' => 'няколко секунди', 'ss' => '%d секунди',
                'm' => 'минута', 'mm' => '%d минути', 'h' => 'час', 'hh' => '%d часа',
                'd' => 'ден', 'dd' => '%d дена', 'w' => 'седмица', 'ww' => '%d седмици',
                'M' => 'месец', 'MM' => '%d месеца', 'y' => 'година', 'yy' => '%d години',
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
                    return '[Миналата] dddd [в] LT';
                default:
                    return '[Миналия] dddd [в] LT';
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
