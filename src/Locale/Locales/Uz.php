<?php

namespace Wilkques\Moment\Locale\Locales;

use Wilkques\Moment\Locale\Locale;

class Uz extends Locale
{
    public $abbr = 'uz';

    public static function config()
    {
        return array(
            'months' => array('январ', 'феврал', 'март', 'апрел', 'май', 'июн', 'июл', 'август', 'сентябр', 'октябр', 'ноябр', 'декабр'),
            'monthsShort' => array('янв', 'фев', 'мар', 'апр', 'май', 'июн', 'июл', 'авг', 'сен', 'окт', 'ноя', 'дек'),
            'weekdays' => array('Якшанба', 'Душанба', 'Сешанба', 'Чоршанба', 'Пайшанба', 'Жума', 'Шанба'),
            'weekdaysShort' => array('Якш', 'Душ', 'Сеш', 'Чор', 'Пай', 'Жум', 'Шан'),
            'weekdaysMin' => array('Як', 'Ду', 'Се', 'Чо', 'Па', 'Жу', 'Ша'),
            'longDateFormat' => array(
                'LT' => 'HH:mm', 'LTS' => 'HH:mm:ss', 'L' => 'DD/MM/YYYY',
                'LL' => 'D MMMM YYYY', 'LLL' => 'D MMMM YYYY HH:mm', 'LLLL' => 'D MMMM YYYY, dddd HH:mm',
            ),
            'calendar' => array(
                'sameDay' => '[Бугун соат] LT [да]', 'nextDay' => '[Эртага] LT [да]', 'nextWeek' => 'dddd [куни соат] LT [да]',
                'lastDay' => '[Кеча соат] LT [да]', 'lastWeek' => '[Утган] dddd [куни соат] LT [да]', 'sameElse' => 'L',
            ),
            'relativeTime' => array(
                'future' => 'Якин %s ичида', 'past' => 'Бир неча %s олдин',
                's' => 'фурсат', 'ss' => '%d фурсат', 'm' => 'бир дакика', 'mm' => '%d дакика',
                'h' => 'бир соат', 'hh' => '%d соат', 'd' => 'бир кун', 'dd' => '%d кун',
                'M' => 'бир ой', 'MM' => '%d ой', 'y' => 'бир йил', 'yy' => '%d йил',
            ),
            'week' => array('dow' => 1, 'doy' => 7),
        );
    }
}
