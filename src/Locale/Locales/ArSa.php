<?php

namespace Wilkques\Moment\Locale\Locales;

use Wilkques\Moment\Locale\Locale;

class ArSa extends Locale
{
    public $abbr = 'ar-sa';

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
        $months = array('يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو', 'يوليو', 'أغسطس', 'سبتمبر', 'أكتوبر', 'نوفمبر', 'ديسمبر');

        return array(
            'months' => $months,
            'monthsShort' => $months,
            'weekdays' => array('الأحد', 'الإثنين', 'الثلاثاء', 'الأربعاء', 'الخميس', 'الجمعة', 'السبت'),
            'weekdaysShort' => array('أحد', 'إثنين', 'ثلاثاء', 'أربعاء', 'خميس', 'جمعة', 'سبت'),
            'weekdaysMin' => array('ح', 'ن', 'ث', 'ر', 'خ', 'ج', 'س'),
            'weekdaysParseExact' => true,
            'longDateFormat' => array(
                'LT' => 'HH:mm', 'LTS' => 'HH:mm:ss', 'L' => 'DD/MM/YYYY',
                'LL' => 'D MMMM YYYY', 'LLL' => 'D MMMM YYYY HH:mm', 'LLLL' => 'dddd D MMMM YYYY HH:mm',
            ),
            'meridiemParse' => '~ص|م~u',
            'calendar' => array(
                'sameDay' => '[اليوم على الساعة] LT',
                'nextDay' => '[غدا على الساعة] LT',
                'nextWeek' => 'dddd [على الساعة] LT',
                'lastDay' => '[أمس على الساعة] LT',
                'lastWeek' => 'dddd [على الساعة] LT',
                'sameElse' => 'L',
            ),
            'relativeTime' => array(
                'future' => 'في %s', 'past' => 'منذ %s',
                's' => 'ثوان', 'ss' => '%d ثانية', 'm' => 'دقيقة', 'mm' => '%d دقائق',
                'h' => 'ساعة', 'hh' => '%d ساعات', 'd' => 'يوم', 'dd' => '%d أيام',
                'M' => 'شهر', 'MM' => '%d أشهر', 'y' => 'سنة', 'yy' => '%d سنوات',
            ),
            'week' => array('dow' => 0, 'doy' => 6),
        );
    }

    public function isPM($input)
    {
        return 'م' === $input;
    }

    public function meridiem($hour, $minute, $isLower)
    {
        return $hour < 12 ? 'ص' : 'م';
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
