<?php

namespace Wilkques\Moment\Locale\Locales;

use Wilkques\Moment\Locale\Locale;

class Cy extends Locale
{
    public $abbr = 'cy';

    public static function config()
    {
        return array(
            'calendar' => array(
                'sameDay' => '[Heddiw am] LT',
                'nextDay' => '[Yfory am] LT',
                'nextWeek' => 'dddd [am] LT',
                'lastDay' => '[Ddoe am] LT',
                'lastWeek' => 'dddd [diwethaf am] LT',
                'sameElse' => 'L',
            ),
            'longDateFormat' => array(
                'LTS' => 'HH:mm:ss',
                'LT' => 'HH:mm',
                'L' => 'DD/MM/YYYY',
                'LL' => 'D MMMM YYYY',
                'LLL' => 'D MMMM YYYY HH:mm',
                'LLLL' => 'dddd, D MMMM YYYY HH:mm',
            ),
            'dayOfMonthOrdinalParse' => '~\\d{1,2}(fed|ain|af|il|ydd|ed|eg)~u',
            'relativeTime' => array(
                'future' => 'mewn %s',
                'past' => '%s yn ôl',
                's' => 'ychydig eiliadau',
                'ss' => '%d eiliad',
                'm' => 'munud',
                'mm' => '%d munud',
                'h' => 'awr',
                'hh' => '%d awr',
                'd' => 'diwrnod',
                'dd' => '%d diwrnod',
                'w' => 'a week',
                'ww' => '%d weeks',
                'M' => 'mis',
                'MM' => '%d mis',
                'y' => 'blwyddyn',
                'yy' => '%d flynedd',
            ),
            'months' => array('Ionawr', 'Chwefror', 'Mawrth', 'Ebrill', 'Mai', 'Mehefin', 'Gorffennaf', 'Awst', 'Medi', 'Hydref', 'Tachwedd', 'Rhagfyr'),
            'monthsShort' => array('Ion', 'Chwe', 'Maw', 'Ebr', 'Mai', 'Meh', 'Gor', 'Aws', 'Med', 'Hyd', 'Tach', 'Rhag'),
            'week' => array(
                'dow' => 1,
                'doy' => 4,
            ),
            'weekdays' => array('Dydd Sul', 'Dydd Llun', 'Dydd Mawrth', 'Dydd Mercher', 'Dydd Iau', 'Dydd Gwener', 'Dydd Sadwrn'),
            'weekdaysMin' => array('Su', 'Ll', 'Ma', 'Me', 'Ia', 'Gw', 'Sa'),
            'weekdaysShort' => array('Sul', 'Llun', 'Maw', 'Mer', 'Iau', 'Gwe', 'Sad'),
            'weekdaysParseExact' => true,
        );
    }

    public function ordinal($number, $token = '')
    {
        $output = '';
        $lookup = array('', 'af', 'il', 'ydd', 'ydd', 'ed', 'ed', 'ed', 'fed', 'fed', 'fed',
            'eg', 'fed', 'eg', 'eg', 'fed', 'eg', 'eg', 'fed', 'eg', 'fed');
        if ($number > 20) {
            if ($number === 40 || $number === 50 || $number === 60 || $number === 80 || $number === 100) {
                $output = 'fed';
            } else {
                $output = 'ain';
            }
        } elseif ($number > 0) {
            $output = $lookup[$number];
        }

        return $number . $output;
    }
}
