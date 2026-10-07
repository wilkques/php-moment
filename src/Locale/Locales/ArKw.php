<?php

namespace Wilkques\Moment\Locale\Locales;

use Wilkques\Moment\Locale\Locale;

class ArKw extends Locale
{
    public $abbr = 'ar-kw';

    public static function config()
    {
        $months = array('يناير', 'فبراير', 'مارس', 'أبريل', 'ماي', 'يونيو', 'يوليوز', 'غشت', 'شتنبر', 'أكتوبر', 'نونبر', 'دجنبر');

        return array(
            'months' => $months,
            'monthsShort' => $months,
            'weekdays' => array('الأحد', 'الإتنين', 'الثلاثاء', 'الأربعاء', 'الخميس', 'الجمعة', 'السبت'),
            'weekdaysShort' => array('احد', 'اتنين', 'ثلاثاء', 'اربعاء', 'خميس', 'جمعة', 'سبت'),
            'weekdaysMin' => array('ح', 'ن', 'ث', 'ر', 'خ', 'ج', 'س'),
            'weekdaysParseExact' => true,
            'longDateFormat' => array(
                'LT' => 'HH:mm', 'LTS' => 'HH:mm:ss', 'L' => 'DD/MM/YYYY',
                'LL' => 'D MMMM YYYY', 'LLL' => 'D MMMM YYYY HH:mm', 'LLLL' => 'dddd D MMMM YYYY HH:mm',
            ),
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
            'week' => array('dow' => 0, 'doy' => 12),
        );
    }
}
