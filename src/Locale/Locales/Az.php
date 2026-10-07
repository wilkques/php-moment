<?php

namespace Wilkques\Moment\Locale\Locales;

use Wilkques\Moment\Locale\Locale;

class Az extends Locale
{
    public $abbr = 'az';

    public static function config()
    {
        return array(
            'calendar' => array(
                'sameDay' => '[bugün saat] LT',
                'nextDay' => '[sabah saat] LT',
                'nextWeek' => '[gələn həftə] dddd [saat] LT',
                'lastDay' => '[dünən] LT',
                'lastWeek' => '[keçən həftə] dddd [saat] LT',
                'sameElse' => 'L',
            ),
            'longDateFormat' => array(
                'LTS' => 'HH:mm:ss',
                'LT' => 'HH:mm',
                'L' => 'DD.MM.YYYY',
                'LL' => 'D MMMM YYYY',
                'LLL' => 'D MMMM YYYY HH:mm',
                'LLLL' => 'dddd, D MMMM YYYY HH:mm',
            ),
            'dayOfMonthOrdinalParse' => '~\\d{1,2}-(ıncı|inci|nci|üncü|ncı|uncu)~u',
            'relativeTime' => array(
                'future' => '%s sonra',
                'past' => '%s əvvəl',
                's' => 'bir neçə saniyə',
                'ss' => '%d saniyə',
                'm' => 'bir dəqiqə',
                'mm' => '%d dəqiqə',
                'h' => 'bir saat',
                'hh' => '%d saat',
                'd' => 'bir gün',
                'dd' => '%d gün',
                'w' => 'a week',
                'ww' => '%d weeks',
                'M' => 'bir ay',
                'MM' => '%d ay',
                'y' => 'bir il',
                'yy' => '%d il',
            ),
            'months' => array('yanvar', 'fevral', 'mart', 'aprel', 'may', 'iyun', 'iyul', 'avqust', 'sentyabr', 'oktyabr', 'noyabr', 'dekabr'),
            'monthsShort' => array('yan', 'fev', 'mar', 'apr', 'may', 'iyn', 'iyl', 'avq', 'sen', 'okt', 'noy', 'dek'),
            'week' => array(
                'dow' => 1,
                'doy' => 7,
            ),
            'weekdays' => array('Bazar', 'Bazar ertəsi', 'Çərşənbə axşamı', 'Çərşənbə', 'Cümə axşamı', 'Cümə', 'Şənbə'),
            'weekdaysMin' => array('Bz', 'BE', 'ÇA', 'Çə', 'CA', 'Cü', 'Şə'),
            'weekdaysShort' => array('Baz', 'BzE', 'ÇAx', 'Çər', 'CAx', 'Cüm', 'Şən'),
            'meridiemParse' => '~gecə|səhər|gündüz|axşam~u',
            'weekdaysParseExact' => true,
        );
    }

    private static $suffixes = array(
        1 => '-inci', 5 => '-inci', 8 => '-inci', 70 => '-inci', 80 => '-inci',
        2 => '-nci', 7 => '-nci', 20 => '-nci', 50 => '-nci',
        3 => '-üncü', 4 => '-üncü', 100 => '-üncü',
        6 => '-ncı', 9 => '-uncu', 10 => '-uncu', 30 => '-uncu',
        60 => '-ıncı', 90 => '-ıncı',
    );

    public function isPM($input)
    {
        return (bool) preg_match('~^(gündüz|axşam)$~u', $input);
    }

    public function meridiem($hour, $minute, $isLower)
    {
        if ($hour < 4) {
            return 'gecə';
        } elseif ($hour < 12) {
            return 'səhər';
        } elseif ($hour < 17) {
            return 'gündüz';
        }

        return 'axşam';
    }

    public function ordinal($number, $token = '')
    {
        if ($number === 0) {
            return $number . '-ıncı';
        }
        $a = $number % 10;
        $b = ($number % 100) - $a;
        $c = $number >= 100 ? 100 : null;
        if (isset(self::$suffixes[$a])) {
            $s = self::$suffixes[$a];
        } elseif (isset(self::$suffixes[$b])) {
            $s = self::$suffixes[$b];
        } elseif ($c !== null && isset(self::$suffixes[$c])) {
            $s = self::$suffixes[$c];
        } else {
            return 'NaN';
        }

        return $number . $s;
    }
}
