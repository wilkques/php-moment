<?php

namespace Wilkques\Moment\Locale\Locales;

use Wilkques\Moment\Locale\Locale;

class Ku extends Locale
{
    public $abbr = 'ku';

    private static $symbolMap = array(
        '1' => '١', '2' => '٢', '3' => '٣', '4' => '٤', '5' => '٥',
        '6' => '٦', '7' => '٧', '8' => '٨', '9' => '٩', '0' => '٠',
    );

    private static $numberMap = array(
        '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4', '٥' => '5',
        '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9', '٠' => '0',
    );

    public static function config()
    {
        $months = array('کانونی دووەم', 'شوبات', 'ئازار', 'نیسان', 'ئایار', 'حوزەیران', 'تەمموز', 'ئاب', 'ئەیلوول', 'تشرینی یەكەم', 'تشرینی دووەم', 'كانونی یەکەم');

        return array(
            'months' => $months,
            'monthsShort' => $months,
            'weekdays' => array('یه‌كشه‌ممه‌', 'دووشه‌ممه‌', 'سێشه‌ممه‌', 'چوارشه‌ممه‌', 'پێنجشه‌ممه‌', 'هه‌ینی', 'شه‌ممه‌'),
            'weekdaysShort' => array('یه‌كشه‌م', 'دووشه‌م', 'سێشه‌م', 'چوارشه‌م', 'پێنجشه‌م', 'هه‌ینی', 'شه‌ممه‌'),
            'weekdaysMin' => array('ی', 'د', 'س', 'چ', 'پ', 'ه', 'ش'),
            'weekdaysParseExact' => true,
            'longDateFormat' => array(
                'LT' => 'HH:mm', 'LTS' => 'HH:mm:ss', 'L' => 'DD/MM/YYYY',
                'LL' => 'D MMMM YYYY', 'LLL' => 'D MMMM YYYY HH:mm', 'LLLL' => 'dddd, D MMMM YYYY HH:mm',
            ),
            'meridiemParse' => '~ئێواره‌|به‌یانی~u',
            'calendar' => array(
                'sameDay' => '[ئه‌مرۆ كاتژمێر] LT', 'nextDay' => '[به‌یانی كاتژمێر] LT', 'nextWeek' => 'dddd [كاتژمێر] LT',
                'lastDay' => '[دوێنێ كاتژمێر] LT', 'lastWeek' => 'dddd [كاتژمێر] LT', 'sameElse' => 'L',
            ),
            'relativeTime' => array(
                'future' => 'له‌ %s', 'past' => '%s',
                's' => 'چه‌ند چركه‌یه‌ك', 'ss' => 'چركه‌ %d', 'm' => 'یه‌ك خوله‌ك', 'mm' => '%d خوله‌ك',
                'h' => 'یه‌ك كاتژمێر', 'hh' => '%d كاتژمێر', 'd' => 'یه‌ك ڕۆژ', 'dd' => '%d ڕۆژ',
                'M' => 'یه‌ك مانگ', 'MM' => '%d مانگ', 'y' => 'یه‌ك ساڵ', 'yy' => '%d ساڵ',
            ),
            'week' => array('dow' => 6, 'doy' => 12),
        );
    }

    public function isPM($input)
    {
        return (bool) preg_match('~ئێواره‌~u', $input);
    }

    public function meridiem($hour, $minute, $isLower)
    {
        return $hour < 12 ? 'به‌یانی' : 'ئێواره‌';
    }

    public function preparse($string)
    {
        return str_replace('،', ',', strtr($string, self::$numberMap));
    }

    public function postformat($string)
    {
        return str_replace(',', '،', strtr($string, self::$symbolMap));
    }
}
