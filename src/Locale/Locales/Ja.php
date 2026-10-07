<?php

namespace Wilkques\Moment\Locale\Locales;

use Wilkques\Moment\Locale\Locale;

class Ja extends Locale
{
    public $abbr = 'ja';

    public static function config()
    {
        return array(
            'eras' => array(
                array('since' => '2019-05-01', 'offset' => 1, 'name' => '令和', 'narrow' => '㋿', 'abbr' => 'R'),
                array('since' => '1989-01-08', 'until' => '2019-04-30', 'offset' => 1, 'name' => '平成', 'narrow' => '㍻', 'abbr' => 'H'),
                array('since' => '1926-12-25', 'until' => '1989-01-07', 'offset' => 1, 'name' => '昭和', 'narrow' => '㍼', 'abbr' => 'S'),
                array('since' => '1912-07-30', 'until' => '1926-12-24', 'offset' => 1, 'name' => '大正', 'narrow' => '㍽', 'abbr' => 'T'),
                array('since' => '1873-01-01', 'until' => '1912-07-29', 'offset' => 6, 'name' => '明治', 'narrow' => '㍾', 'abbr' => 'M'),
                array('since' => '0001-01-01', 'until' => '1873-12-31', 'offset' => 1, 'name' => '西暦', 'narrow' => 'AD', 'abbr' => 'AD'),
                array('since' => '0000-12-31', 'until' => -INF, 'offset' => 1, 'name' => '紀元前', 'narrow' => 'BC', 'abbr' => 'BC'),
            ),
            'eraYearOrdinalRegex' => '~(元|\d+)年~u',
            'months' => explode('_', '1月_2月_3月_4月_5月_6月_7月_8月_9月_10月_11月_12月'),
            'monthsShort' => explode('_', '1月_2月_3月_4月_5月_6月_7月_8月_9月_10月_11月_12月'),
            'weekdays' => explode('_', '日曜日_月曜日_火曜日_水曜日_木曜日_金曜日_土曜日'),
            'weekdaysShort' => explode('_', '日_月_火_水_木_金_土'),
            'weekdaysMin' => explode('_', '日_月_火_水_木_金_土'),
            'longDateFormat' => array(
                'LT' => 'HH:mm', 'LTS' => 'HH:mm:ss', 'L' => 'YYYY/MM/DD', 'LL' => 'YYYY年M月D日',
                'LLL' => 'YYYY年M月D日 HH:mm', 'LLLL' => 'YYYY年M月D日 dddd HH:mm',
                'l' => 'YYYY/MM/DD', 'll' => 'YYYY年M月D日', 'lll' => 'YYYY年M月D日 HH:mm', 'llll' => 'YYYY年M月D日(ddd) HH:mm',
            ),
            'meridiemParse' => '~午前|午後~iu',
            'calendar' => array(
                'sameDay' => '[今日] LT', 'nextDay' => '[明日] LT', 'lastDay' => '[昨日] LT', 'sameElse' => 'L',
            ),
            'dayOfMonthOrdinalParse' => '~\d{1,2}日~u',
            'relativeTime' => array(
                'future' => '%s後', 'past' => '%s前', 's' => '数秒', 'ss' => '%d秒',
                'm' => '1分', 'mm' => '%d分', 'h' => '1時間', 'hh' => '%d時間',
                'd' => '1日', 'dd' => '%d日', 'M' => '1ヶ月', 'MM' => '%dヶ月', 'y' => '1年', 'yy' => '%d年',
            ),
        );
    }

    public function eraYearOrdinalParse($input, $match)
    {
        return $match[1] === '元' ? 1 : (int) ($match[1] ?: $input);
    }

    public function isPM($input)
    {
        return $input === '午後';
    }

    public function meridiem($hour, $minute, $isLower)
    {
        return $hour < 12 ? '午前' : '午後';
    }

    public function calendar($key, $mom = null, $now = null)
    {
        if ($key === 'nextWeek') {
            return $now->week() !== $mom->week() ? '[来週]dddd LT' : 'dddd LT';
        } elseif ($key === 'lastWeek') {
            return $mom->week() !== $now->week() ? '[先週]dddd LT' : 'dddd LT';
        }

        return parent::calendar($key, $mom, $now);
    }

    public function ordinal($number, $token = '')
    {
        switch ($token) {
            case 'y':
                return $number === 1 ? '元年' : $number . '年';
            case 'd':
            case 'D':
            case 'DDD':
                return $number . '日';
            default:
                return $number;
        }
    }
}
