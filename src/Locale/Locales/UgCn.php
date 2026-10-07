<?php

namespace Wilkques\Moment\Locale\Locales;

use Wilkques\Moment\Locale\Locale;

class UgCn extends Locale
{
    public $abbr = 'ug-cn';

    public static function config()
    {
        return array(
            'months' => explode('_', 'يانۋار_فېۋرال_مارت_ئاپرېل_ماي_ئىيۇن_ئىيۇل_ئاۋغۇست_سېنتەبىر_ئۆكتەبىر_نويابىر_دېكابىر'),
            'monthsShort' => explode('_', 'يانۋار_فېۋرال_مارت_ئاپرېل_ماي_ئىيۇن_ئىيۇل_ئاۋغۇست_سېنتەبىر_ئۆكتەبىر_نويابىر_دېكابىر'),
            'weekdays' => explode('_', 'يەكشەنبە_دۈشەنبە_سەيشەنبە_چارشەنبە_پەيشەنبە_جۈمە_شەنبە'),
            'weekdaysShort' => explode('_', 'يە_دۈ_سە_چا_پە_جۈ_شە'),
            'weekdaysMin' => explode('_', 'يە_دۈ_سە_چا_پە_جۈ_شە'),
            'longDateFormat' => array(
                'LT' => 'HH:mm',
                'LTS' => 'HH:mm:ss',
                'L' => 'YYYY-MM-DD',
                'LL' => 'YYYY-يىلىM-ئاينىڭD-كۈنى',
                'LLL' => 'YYYY-يىلىM-ئاينىڭD-كۈنى، HH:mm',
                'LLLL' => 'dddd، YYYY-يىلىM-ئاينىڭD-كۈنى، HH:mm',
            ),
            'meridiemParse' => '~يېرىم كېچە|سەھەر|چۈشتىن بۇرۇن|چۈش|چۈشتىن كېيىن|كەچ~u',
            'calendar' => array(
                'sameDay' => '[بۈگۈن سائەت] LT',
                'nextDay' => '[ئەتە سائەت] LT',
                'nextWeek' => '[كېلەركى] dddd [سائەت] LT',
                'lastDay' => '[تۆنۈگۈن] LT',
                'lastWeek' => '[ئالدىنقى] dddd [سائەت] LT',
                'sameElse' => 'L',
            ),
            'relativeTime' => array(
                'future' => '%s كېيىن',
                'past' => '%s بۇرۇن',
                's' => 'نەچچە سېكونت',
                'ss' => '%d سېكونت',
                'm' => 'بىر مىنۇت',
                'mm' => '%d مىنۇت',
                'h' => 'بىر سائەت',
                'hh' => '%d سائەت',
                'd' => 'بىر كۈن',
                'dd' => '%d كۈن',
                'M' => 'بىر ئاي',
                'MM' => '%d ئاي',
                'y' => 'بىر يىل',
                'yy' => '%d يىل',
            ),
            'dayOfMonthOrdinalParse' => '~\d{1,2}(-كۈنى|-ئاي|-ھەپتە)~u',
            'week' => array('dow' => 1, 'doy' => 7),
        );
    }

    public function meridiemHour($hour, $meridiem)
    {
        if ($hour === 12) {
            $hour = 0;
        }
        if ($meridiem === 'يېرىم كېچە' || $meridiem === 'سەھەر' || $meridiem === 'چۈشتىن بۇرۇن') {
            return $hour;
        } elseif ($meridiem === 'چۈشتىن كېيىن' || $meridiem === 'كەچ') {
            return $hour + 12;
        }

        return $hour >= 11 ? $hour : $hour + 12;
    }

    public function meridiem($hour, $minute, $isLower)
    {
        $hm = $hour * 100 + $minute;
        if ($hm < 600) {
            return 'يېرىم كېچە';
        } elseif ($hm < 900) {
            return 'سەھەر';
        } elseif ($hm < 1130) {
            return 'چۈشتىن بۇرۇن';
        } elseif ($hm < 1230) {
            return 'چۈش';
        } elseif ($hm < 1800) {
            return 'چۈشتىن كېيىن';
        }

        return 'كەچ';
    }

    public function ordinal($number, $token = '')
    {
        switch ($token) {
            case 'd':
            case 'D':
            case 'DDD':
                return $number . '-كۈنى';
            case 'w':
            case 'W':
                return $number . '-ھەپتە';
            default:
                return $number;
        }
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
