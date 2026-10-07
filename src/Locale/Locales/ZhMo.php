<?php

namespace Wilkques\Moment\Locale\Locales;

use Wilkques\Moment\Locale\Locale;

class ZhMo extends Locale
{
    public $abbr = 'zh-mo';

    public static function config()
    {
        return array(
            'months' => explode('_', '一月_二月_三月_四月_五月_六月_七月_八月_九月_十月_十一月_十二月'),
            'monthsShort' => explode('_', '1月_2月_3月_4月_5月_6月_7月_8月_9月_10月_11月_12月'),
            'weekdays' => explode('_', '星期日_星期一_星期二_星期三_星期四_星期五_星期六'),
            'weekdaysShort' => explode('_', '週日_週一_週二_週三_週四_週五_週六'),
            'weekdaysMin' => explode('_', '日_一_二_三_四_五_六'),
            'longDateFormat' => array(
                'LT' => 'HH:mm', 'LTS' => 'HH:mm:ss', 'L' => 'DD/MM/YYYY', 'LL' => 'YYYY年M月D日',
                'LLL' => 'YYYY年M月D日 HH:mm', 'LLLL' => 'YYYY年M月D日dddd HH:mm',
                'l' => 'D/M/YYYY', 'll' => 'YYYY年M月D日', 'lll' => 'YYYY年M月D日 HH:mm', 'llll' => 'YYYY年M月D日dddd HH:mm',
            ),
            'meridiemParse' => '~凌晨|早上|上午|中午|下午|晚上~u',
            'calendar' => array(
                'sameDay' => '[今天] LT', 'nextDay' => '[明天] LT', 'nextWeek' => '[下]dddd LT',
                'lastDay' => '[昨天] LT', 'lastWeek' => '[上]dddd LT', 'sameElse' => 'L',
            ),
            'dayOfMonthOrdinalParse' => '~\d{1,2}(日|月|週)~u',
            'relativeTime' => array(
                'future' => '%s內', 'past' => '%s前', 's' => '幾秒', 'ss' => '%d 秒',
                'm' => '1 分鐘', 'mm' => '%d 分鐘', 'h' => '1 小時', 'hh' => '%d 小時',
                'd' => '1 天', 'dd' => '%d 天', 'M' => '1 個月', 'MM' => '%d 個月', 'y' => '1 年', 'yy' => '%d 年',
            ),
        );
    }

    public function ordinal($number, $token = '')
    {
        switch ($token) {
            case 'd':
            case 'D':
            case 'DDD':
                return $number . '日';
            case 'M':
                return $number . '月';
            case 'w':
            case 'W':
                return $number . '週';
            default:
                return $number;
        }
    }

    public function meridiemHour($hour, $meridiem)
    {
        if ($hour === 12) {
            $hour = 0;
        }
        if ($meridiem === '凌晨' || $meridiem === '早上' || $meridiem === '上午') {
            return $hour;
        } elseif ($meridiem === '中午') {
            return $hour >= 11 ? $hour : $hour + 12;
        } elseif ($meridiem === '下午' || $meridiem === '晚上') {
            return $hour + 12;
        }

        return null;
    }

    public function meridiem($hour, $minute, $isLower)
    {
        $hm = $hour * 100 + $minute;
        if ($hm < 600) {
            return '凌晨';
        } elseif ($hm < 900) {
            return '早上';
        } elseif ($hm < 1130) {
            return '上午';
        } elseif ($hm < 1230) {
            return '中午';
        } elseif ($hm < 1800) {
            return '下午';
        }

        return '晚上';
    }
}
