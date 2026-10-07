<?php

namespace Wilkques\Moment\Locale\Locales;

use Wilkques\Moment\Locale\Locale;

class ArTn extends Locale
{
    public $abbr = 'ar-tn';

    public static function config()
    {
        $months = array('جانفي', 'فيفري', 'مارس', 'أفريل', 'ماي', 'جوان', 'جويلية', 'أوت', 'سبتمبر', 'أكتوبر', 'نوفمبر', 'ديسمبر');

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
            'calendar' => array(
                'sameDay' => '[اليوم على الساعة] LT', 'nextDay' => '[غدا على الساعة] LT',
                'nextWeek' => 'dddd [على الساعة] LT', 'lastDay' => '[أمس على الساعة] LT',
                'lastWeek' => 'dddd [على الساعة] LT', 'sameElse' => 'L',
            ),
            'relativeTime' => array(
                'future' => 'في %s', 'past' => 'منذ %s', 's' => 'ثوان', 'ss' => '%d ثانية',
                'm' => 'دقيقة', 'mm' => '%d دقائق', 'h' => 'ساعة', 'hh' => '%d ساعات',
                'd' => 'يوم', 'dd' => '%d أيام', 'M' => 'شهر', 'MM' => '%d أشهر', 'y' => 'سنة', 'yy' => '%d سنوات',
            ),
            'week' => array('dow' => 1, 'doy' => 4),
        );
    }
}
