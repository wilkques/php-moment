<?php

namespace Wilkques\Moment\Locale\Locales;

use Wilkques\Moment\Locale\Locale;

class Tg extends Locale
{
    public $abbr = 'tg';

    private static $suffixes = array(
        0 => '-ум', 1 => '-ум', 2 => '-юм', 3 => '-юм', 4 => '-ум', 5 => '-ум', 6 => '-ум', 7 => '-ум',
        8 => '-ум', 9 => '-ум', 10 => '-ум', 12 => '-ум', 13 => '-ум', 20 => '-ум', 30 => '-юм',
        40 => '-ум', 50 => '-ум', 60 => '-ум', 70 => '-ум', 80 => '-ум', 90 => '-ум', 100 => '-ум',
    );

    public static function config()
    {
        return array(
            'months' => array(
                'format' => explode('_', 'январи_феврали_марти_апрели_майи_июни_июли_августи_сентябри_октябри_ноябри_декабри'),
                'standalone' => explode('_', 'январ_феврал_март_апрел_май_июн_июл_август_сентябр_октябр_ноябр_декабр'),
            ),
            'monthsShort' => explode('_', 'янв_фев_мар_апр_май_июн_июл_авг_сен_окт_ноя_дек'),
            'weekdays' => explode('_', 'якшанбе_душанбе_сешанбе_чоршанбе_панҷшанбе_ҷумъа_шанбе'),
            'weekdaysShort' => explode('_', 'яшб_дшб_сшб_чшб_пшб_ҷум_шнб'),
            'weekdaysMin' => explode('_', 'яш_дш_сш_чш_пш_ҷм_шб'),
            'longDateFormat' => array(
                'LT' => 'HH:mm', 'LTS' => 'HH:mm:ss', 'L' => 'DD.MM.YYYY', 'LL' => 'D MMMM YYYY',
                'LLL' => 'D MMMM YYYY HH:mm', 'LLLL' => 'dddd, D MMMM YYYY HH:mm',
            ),
            'calendar' => array(
                'sameDay' => '[Имрӯз соати] LT', 'nextDay' => '[Фардо соати] LT', 'lastDay' => '[Дирӯз соати] LT',
                'nextWeek' => 'dddd[и] [ҳафтаи оянда соати] LT', 'lastWeek' => 'dddd[и] [ҳафтаи гузашта соати] LT',
                'sameElse' => 'L',
            ),
            'relativeTime' => array(
                'future' => 'баъди %s', 'past' => '%s пеш',
                's' => 'якчанд сония', 'm' => 'як дақиқа', 'mm' => '%d дақиқа',
                'h' => 'як соат', 'hh' => '%d соат', 'd' => 'як рӯз', 'dd' => '%d рӯз',
                'M' => 'як моҳ', 'MM' => '%d моҳ', 'y' => 'як сол', 'yy' => '%d сол',
            ),
            'meridiemParse' => '~шаб|субҳ|рӯз|бегоҳ~u',
            'dayOfMonthOrdinalParse' => '~\d{1,2}-(ум|юм)~u',
            'week' => array('dow' => 1, 'doy' => 7),
        );
    }

    public function meridiemHour($hour, $meridiem)
    {
        if ($hour === 12) {
            $hour = 0;
        }
        if ($meridiem === 'шаб') {
            return $hour < 4 ? $hour : $hour + 12;
        } elseif ($meridiem === 'субҳ') {
            return $hour;
        } elseif ($meridiem === 'рӯз') {
            return $hour >= 11 ? $hour : $hour + 12;
        } elseif ($meridiem === 'бегоҳ') {
            return $hour + 12;
        }

        return null;
    }

    public function meridiem($hour, $minute, $isLower)
    {
        if ($hour < 4) {
            return 'шаб';
        } elseif ($hour < 11) {
            return 'субҳ';
        } elseif ($hour < 16) {
            return 'рӯз';
        } elseif ($hour < 19) {
            return 'бегоҳ';
        }

        return 'шаб';
    }

    public function ordinal($number, $token = '')
    {
        $a = $number % 10;
        $s = self::$suffixes;
        if (isset($s[$number])) {
            $suffix = $s[$number];
        } elseif (isset($s[$a])) {
            $suffix = $s[$a];
        } elseif ($number >= 100) {
            $suffix = $s[100];
        } else {
            return 'NaN';
        }

        return $number . $suffix;
    }
}
