<?php

namespace Wilkques\Moment\Locale\Locales;

use Wilkques\Moment\Locale\Locale;

class Kk extends Locale
{
    public $abbr = 'kk';

    private static $suffixes = array(
        0 => '-ші', 1 => '-ші', 2 => '-ші', 3 => '-ші', 4 => '-ші', 5 => '-ші',
        6 => '-шы', 7 => '-ші', 8 => '-ші', 9 => '-шы', 10 => '-шы', 20 => '-шы',
        30 => '-шы', 40 => '-шы', 50 => '-ші', 60 => '-шы', 70 => '-ші', 80 => '-ші',
        90 => '-шы', 100 => '-ші',
    );

    public static function config()
    {
        return array(
            'months' => array('қаңтар', 'ақпан', 'наурыз', 'сәуір', 'мамыр', 'маусым', 'шілде', 'тамыз', 'қыркүйек', 'қазан', 'қараша', 'желтоқсан'),
            'monthsShort' => array('қаң', 'ақп', 'нау', 'сәу', 'мам', 'мау', 'шіл', 'там', 'қыр', 'қаз', 'қар', 'жел'),
            'weekdays' => array('жексенбі', 'дүйсенбі', 'сейсенбі', 'сәрсенбі', 'бейсенбі', 'жұма', 'сенбі'),
            'weekdaysShort' => array('жек', 'дүй', 'сей', 'сәр', 'бей', 'жұм', 'сен'),
            'weekdaysMin' => array('жк', 'дй', 'сй', 'ср', 'бй', 'жм', 'сн'),
            'longDateFormat' => array(
                'LT' => 'HH:mm', 'LTS' => 'HH:mm:ss', 'L' => 'DD.MM.YYYY',
                'LL' => 'D MMMM YYYY', 'LLL' => 'D MMMM YYYY HH:mm', 'LLLL' => 'dddd, D MMMM YYYY HH:mm',
            ),
            'calendar' => array(
                'sameDay' => '[Бүгін сағат] LT', 'nextDay' => '[Ертең сағат] LT', 'nextWeek' => 'dddd [сағат] LT',
                'lastDay' => '[Кеше сағат] LT', 'lastWeek' => '[Өткен аптаның] dddd [сағат] LT', 'sameElse' => 'L',
            ),
            'relativeTime' => array(
                'future' => '%s ішінде', 'past' => '%s бұрын',
                's' => 'бірнеше секунд', 'ss' => '%d секунд', 'm' => 'бір минут', 'mm' => '%d минут',
                'h' => 'бір сағат', 'hh' => '%d сағат', 'd' => 'бір күн', 'dd' => '%d күн',
                'M' => 'бір ай', 'MM' => '%d ай', 'y' => 'бір жыл', 'yy' => '%d жыл',
            ),
            'dayOfMonthOrdinalParse' => '~\d{1,2}-(ші|шы)~u',
            'week' => array('dow' => 1, 'doy' => 7),
        );
    }

    public function ordinal($number, $token = '')
    {
        $a = $number % 10;
        $b = $number >= 100 ? 100 : null;
        if (isset(self::$suffixes[$number])) {
            $s = self::$suffixes[$number];
        } elseif (isset(self::$suffixes[$a])) {
            $s = self::$suffixes[$a];
        } elseif ($b !== null && isset(self::$suffixes[$b])) {
            $s = self::$suffixes[$b];
        } else {
            $s = 'undefined';
        }

        return $number . $s;
    }
}
