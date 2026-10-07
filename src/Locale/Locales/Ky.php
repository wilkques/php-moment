<?php

namespace Wilkques\Moment\Locale\Locales;

use Wilkques\Moment\Locale\Locale;

class Ky extends Locale
{
    public $abbr = 'ky';

    private static $suffixes = array(
        0 => '-чү', 1 => '-чи', 2 => '-чи', 3 => '-чү', 4 => '-чү', 5 => '-чи', 6 => '-чы', 7 => '-чи', 8 => '-чи', 9 => '-чу',
        10 => '-чу', 20 => '-чы', 30 => '-чу', 40 => '-чы', 50 => '-чү', 60 => '-чы', 70 => '-чи', 80 => '-чи', 90 => '-чу', 100 => '-чү',
    );

    public static function config()
    {
        return array(
            'months' => array('январь', 'февраль', 'март', 'апрель', 'май', 'июнь', 'июль', 'август', 'сентябрь', 'октябрь', 'ноябрь', 'декабрь'),
            'monthsShort' => array('янв', 'фев', 'март', 'апр', 'май', 'июнь', 'июль', 'авг', 'сен', 'окт', 'ноя', 'дек'),
            'weekdays' => array('Жекшемби', 'Дүйшөмбү', 'Шейшемби', 'Шаршемби', 'Бейшемби', 'Жума', 'Ишемби'),
            'weekdaysShort' => array('Жек', 'Дүй', 'Шей', 'Шар', 'Бей', 'Жум', 'Ише'),
            'weekdaysMin' => array('Жк', 'Дй', 'Шй', 'Шр', 'Бй', 'Жм', 'Иш'),
            'longDateFormat' => array(
                'LT' => 'HH:mm', 'LTS' => 'HH:mm:ss', 'L' => 'DD.MM.YYYY', 'LL' => 'D MMMM YYYY',
                'LLL' => 'D MMMM YYYY HH:mm', 'LLLL' => 'dddd, D MMMM YYYY HH:mm',
            ),
            'calendar' => array(
                'sameDay' => '[Бүгүн саат] LT', 'nextDay' => '[Эртең саат] LT', 'nextWeek' => 'dddd [саат] LT',
                'lastDay' => '[Кечээ саат] LT', 'lastWeek' => '[Өткөн аптанын] dddd [күнү] [саат] LT', 'sameElse' => 'L',
            ),
            'relativeTime' => array(
                'future' => '%s ичинде', 'past' => '%s мурун', 's' => 'бирнече секунд', 'ss' => '%d секунд',
                'm' => 'бир мүнөт', 'mm' => '%d мүнөт', 'h' => 'бир саат', 'hh' => '%d саат',
                'd' => 'бир күн', 'dd' => '%d күн', 'M' => 'бир ай', 'MM' => '%d ай', 'y' => 'бир жыл', 'yy' => '%d жыл',
            ),
            'dayOfMonthOrdinalParse' => '~\d{1,2}-(чи|чы|чү|чу)~u',
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
