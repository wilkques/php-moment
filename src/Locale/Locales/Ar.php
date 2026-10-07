<?php

namespace Wilkques\Moment\Locale\Locales;

use Wilkques\Moment\Locale\Locale;

class Ar extends Locale
{
    public $abbr = 'ar';

    private static $symbolMap = array(
        '1' => '١', '2' => '٢', '3' => '٣', '4' => '٤', '5' => '٥',
        '6' => '٦', '7' => '٧', '8' => '٨', '9' => '٩', '0' => '٠',
    );

    private static $numberMap = array(
        '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4', '٥' => '5',
        '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9', '٠' => '0',
    );

    private static $plurals = array(
        's' => array('أقل من ثانية', 'ثانية واحدة', array('ثانيتان', 'ثانيتين'), '%d ثوان', '%d ثانية', '%d ثانية'),
        'm' => array('أقل من دقيقة', 'دقيقة واحدة', array('دقيقتان', 'دقيقتين'), '%d دقائق', '%d دقيقة', '%d دقيقة'),
        'h' => array('أقل من ساعة', 'ساعة واحدة', array('ساعتان', 'ساعتين'), '%d ساعات', '%d ساعة', '%d ساعة'),
        'd' => array('أقل من يوم', 'يوم واحد', array('يومان', 'يومين'), '%d أيام', '%d يومًا', '%d يوم'),
        'M' => array('أقل من شهر', 'شهر واحد', array('شهران', 'شهرين'), '%d أشهر', '%d شهرا', '%d شهر'),
        'y' => array('أقل من عام', 'عام واحد', array('عامان', 'عامين'), '%d أعوام', '%d عامًا', '%d عام'),
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
                'LT' => 'HH:mm', 'LTS' => 'HH:mm:ss', 'L' => "D/\xE2\x80\x8FM/\xE2\x80\x8FYYYY",
                'LL' => 'D MMMM YYYY', 'LLL' => 'D MMMM YYYY HH:mm', 'LLLL' => 'dddd D MMMM YYYY HH:mm',
            ),
            'meridiemParse' => '~ص|م~u',
            'calendar' => array(
                'sameDay' => '[اليوم عند الساعة] LT',
                'nextDay' => '[غدًا عند الساعة] LT',
                'nextWeek' => 'dddd [عند الساعة] LT',
                'lastDay' => '[أمس عند الساعة] LT',
                'lastWeek' => 'dddd [عند الساعة] LT',
                'sameElse' => 'L',
            ),
            'relativeTime' => array(
                'future' => 'بعد %s', 'past' => 'منذ %s',
            ),
            'week' => array('dow' => 6, 'doy' => 12),
        );
    }

    protected static function pluralForm($n)
    {
        if ($n === 0) {
            return 0;
        }
        if ($n === 1) {
            return 1;
        }
        if ($n === 2) {
            return 2;
        }
        if ($n % 100 >= 3 && $n % 100 <= 10) {
            return 3;
        }

        return $n % 100 >= 11 ? 4 : 5;
    }

    public function relativeTime($number, $withoutSuffix, $key, $isFuture)
    {
        $u = substr($key, 0, 1);
        if (!isset(self::$plurals[$u])) {
            return parent::relativeTime($number, $withoutSuffix, $key, $isFuture); // w, ww: moment's defaults
        }
        $f = self::pluralForm($number);
        $str = self::$plurals[$u][$f];
        if ($f === 2) {
            $str = $str[$withoutSuffix ? 0 : 1];
        }

        return preg_replace('/%d/i', $number, $str, 1);
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
