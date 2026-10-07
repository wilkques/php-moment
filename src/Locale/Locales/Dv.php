<?php

namespace Wilkques\Moment\Locale\Locales;

use Wilkques\Moment\Locale\Locale;

class Dv extends Locale
{
    public $abbr = 'dv';

    public static function config()
    {
        $months = array('ޖެނުއަރީ', 'ފެބްރުއަރީ', 'މާރިޗު', 'އޭޕްރީލު', 'މޭ', 'ޖޫން', 'ޖުލައި', 'އޯގަސްޓު', 'ސެޕްޓެމްބަރު', 'އޮކްޓޯބަރު', 'ނޮވެމްބަރު', 'ޑިސެމްބަރު');
        $weekdays = array('އާދިއްތަ', 'ހޯމަ', 'އަންގާރަ', 'ބުދަ', 'ބުރާސްފަތި', 'ހުކުރު', 'ހޮނިހިރު');

        return array(
            'months' => $months,
            'monthsShort' => $months,
            'weekdays' => $weekdays,
            'weekdaysShort' => $weekdays,
            'weekdaysMin' => array('އާދި', 'ހޯމަ', 'އަން', 'ބުދަ', 'ބުރާ', 'ހުކު', 'ހޮނި'),
            'longDateFormat' => array(
                'LT' => 'HH:mm', 'LTS' => 'HH:mm:ss', 'L' => 'D/M/YYYY',
                'LL' => 'D MMMM YYYY', 'LLL' => 'D MMMM YYYY HH:mm', 'LLLL' => 'dddd D MMMM YYYY HH:mm',
            ),
            'meridiemParse' => '~މކ|މފ~u',
            'calendar' => array(
                'sameDay' => '[މިއަދު] LT', 'nextDay' => '[މާދަމާ] LT', 'nextWeek' => 'dddd LT',
                'lastDay' => '[އިއްޔެ] LT', 'lastWeek' => '[ފާއިތުވި] dddd LT', 'sameElse' => 'L',
            ),
            'relativeTime' => array(
                'future' => 'ތެރޭގައި %s', 'past' => 'ކުރިން %s',
                's' => 'ސިކުންތުކޮޅެއް', 'ss' => 'd% ސިކުންތު', 'm' => 'މިނިޓެއް', 'mm' => 'މިނިޓު %d',
                'h' => 'ގަޑިއިރެއް', 'hh' => 'ގަޑިއިރު %d', 'd' => 'ދުވަހެއް', 'dd' => 'ދުވަސް %d',
                'M' => 'މަހެއް', 'MM' => 'މަސް %d', 'y' => 'އަހަރެއް', 'yy' => 'އަހަރު %d',
            ),
            'week' => array('dow' => 7, 'doy' => 12),
        );
    }

    public function isPM($input)
    {
        return 'މފ' === $input;
    }

    public function meridiem($hour, $minute, $isLower)
    {
        return $hour < 12 ? 'މކ' : 'މފ';
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
