<?php

namespace Wilkques\Moment\Locale\Locales;

use Wilkques\Moment\Locale\Locale;

class ZhCn extends Locale
{
    public $abbr = 'zh-cn';

    public static function config()
    {
        return array(
            'months' => array('一月', '二月', '三月', '四月', '五月', '六月', '七月', '八月', '九月', '十月', '十一月', '十二月'),
            'monthsShort' => array('1月', '2月', '3月', '4月', '5月', '6月', '7月', '8月', '9月', '10月', '11月', '12月'),
            'weekdays' => array('星期日', '星期一', '星期二', '星期三', '星期四', '星期五', '星期六'),
            'weekdaysShort' => array('周日', '周一', '周二', '周三', '周四', '周五', '周六'),
            'weekdaysMin' => array('日', '一', '二', '三', '四', '五', '六'),
            'longDateFormat' => array(
                'LT' => 'HH:mm', 'LTS' => 'HH:mm:ss', 'L' => 'YYYY/MM/DD', 'LL' => 'YYYY年M月D日',
                'LLL' => 'YYYY年M月D日Ah点mm分', 'LLLL' => 'YYYY年M月D日ddddAh点mm分',
                'l' => 'YYYY/M/D', 'll' => 'YYYY年M月D日', 'lll' => 'YYYY年M月D日 HH:mm', 'llll' => 'YYYY年M月D日dddd HH:mm',
            ),
            'meridiemParse' => '~凌晨|早上|上午|中午|下午|晚上~u',
            'calendar' => array(
                'sameDay' => '[今天]LT', 'nextDay' => '[明天]LT', 'lastDay' => '[昨天]LT', 'sameElse' => 'L',
            ),
            'dayOfMonthOrdinalParse' => '~\d{1,2}(日|月|周)~u',
            'relativeTime' => array(
                'future' => '%s后', 'past' => '%s前', 's' => '几秒', 'ss' => '%d 秒',
                'm' => '1 分钟', 'mm' => '%d 分钟', 'h' => '1 小时', 'hh' => '%d 小时',
                'd' => '1 天', 'dd' => '%d 天', 'w' => '1 周', 'ww' => '%d 周',
                'M' => '1 个月', 'MM' => '%d 个月', 'y' => '1 年', 'yy' => '%d 年',
            ),
            'week' => array('dow' => 1, 'doy' => 4),
        );
    }

    public function calendar($key, $mom = null, $now = null)
    {
        if ($key === 'nextWeek') {
            return $now->week() !== $mom->week() ? '[下]dddLT' : '[本]dddLT';
        }
        if ($key === 'lastWeek') {
            return $mom->week() !== $now->week() ? '[上]dddLT' : '[本]dddLT';
        }

        return parent::calendar($key, $mom, $now);
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
                return $number . '周';
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
        } elseif ($meridiem === '下午' || $meridiem === '晚上') {
            return $hour + 12;
        }

        return $hour >= 11 ? $hour : $hour + 12;
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
