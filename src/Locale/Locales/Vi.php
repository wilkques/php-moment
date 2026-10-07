<?php

namespace Wilkques\Moment\Locale\Locales;

use Wilkques\Moment\Locale\Locale;

class Vi extends Locale
{
    public $abbr = 'vi';

    public static function config()
    {
        return array(
            'months' => explode('_', 'tháng 1_tháng 2_tháng 3_tháng 4_tháng 5_tháng 6_tháng 7_tháng 8_tháng 9_tháng 10_tháng 11_tháng 12'),
            'monthsShort' => explode('_', 'Thg 01_Thg 02_Thg 03_Thg 04_Thg 05_Thg 06_Thg 07_Thg 08_Thg 09_Thg 10_Thg 11_Thg 12'),
            'monthsParseExact' => true,
            'weekdays' => explode('_', 'chủ nhật_thứ hai_thứ ba_thứ tư_thứ năm_thứ sáu_thứ bảy'),
            'weekdaysShort' => explode('_', 'CN_T2_T3_T4_T5_T6_T7'),
            'weekdaysMin' => explode('_', 'CN_T2_T3_T4_T5_T6_T7'),
            'weekdaysParseExact' => true,
            'meridiemParse' => '~sa|ch~iu',
            'longDateFormat' => array(
                'LT' => 'HH:mm', 'LTS' => 'HH:mm:ss', 'L' => 'DD/MM/YYYY', 'LL' => 'D MMMM [năm] YYYY',
                'LLL' => 'D MMMM [năm] YYYY HH:mm', 'LLLL' => 'dddd, D MMMM [năm] YYYY HH:mm',
                'l' => 'DD/M/YYYY', 'll' => 'D MMM YYYY', 'lll' => 'D MMM YYYY HH:mm', 'llll' => 'ddd, D MMM YYYY HH:mm',
            ),
            'calendar' => array(
                'sameDay' => '[Hôm nay lúc] LT', 'nextDay' => '[Ngày mai lúc] LT', 'nextWeek' => 'dddd [tuần tới lúc] LT',
                'lastDay' => '[Hôm qua lúc] LT', 'lastWeek' => 'dddd [tuần trước lúc] LT', 'sameElse' => 'L',
            ),
            'relativeTime' => array(
                'future' => '%s tới', 'past' => '%s trước', 's' => 'vài giây', 'ss' => '%d giây',
                'm' => 'một phút', 'mm' => '%d phút', 'h' => 'một giờ', 'hh' => '%d giờ',
                'd' => 'một ngày', 'dd' => '%d ngày', 'w' => 'một tuần', 'ww' => '%d tuần',
                'M' => 'một tháng', 'MM' => '%d tháng', 'y' => 'một năm', 'yy' => '%d năm',
            ),
            'dayOfMonthOrdinalParse' => '~\d{1,2}~u',
            'week' => array('dow' => 1, 'doy' => 4),
        );
    }

    public function isPM($input)
    {
        return (bool) preg_match('~^ch$~iu', $input);
    }

    public function meridiem($hours, $minutes, $isLower)
    {
        if ($hours < 12) {
            return $isLower ? 'sa' : 'SA';
        }

        return $isLower ? 'ch' : 'CH';
    }

    public function ordinal($number, $token = '')
    {
        return $number;
    }
}
