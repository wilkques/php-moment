<?php

namespace Wilkques\Moment\Locale\Locales;

use Wilkques\Moment\Locale\Locale;

class Ko extends Locale
{
    public $abbr = 'ko';

    public static function config()
    {
        return array(
            'months' => array('1월', '2월', '3월', '4월', '5월', '6월', '7월', '8월', '9월', '10월', '11월', '12월'),
            'monthsShort' => array('1월', '2월', '3월', '4월', '5월', '6월', '7월', '8월', '9월', '10월', '11월', '12월'),
            'weekdays' => array('일요일', '월요일', '화요일', '수요일', '목요일', '금요일', '토요일'),
            'weekdaysShort' => array('일', '월', '화', '수', '목', '금', '토'),
            'weekdaysMin' => array('일', '월', '화', '수', '목', '금', '토'),
            'longDateFormat' => array(
                'LT' => 'A h:mm', 'LTS' => 'A h:mm:ss', 'L' => 'YYYY.MM.DD.', 'LL' => 'YYYY년 MMMM D일',
                'LLL' => 'YYYY년 MMMM D일 A h:mm', 'LLLL' => 'YYYY년 MMMM D일 dddd A h:mm',
                'l' => 'YYYY.MM.DD.', 'll' => 'YYYY년 MMMM D일', 'lll' => 'YYYY년 MMMM D일 A h:mm', 'llll' => 'YYYY년 MMMM D일 dddd A h:mm',
            ),
            'calendar' => array(
                'sameDay' => '오늘 LT', 'nextDay' => '내일 LT', 'nextWeek' => 'dddd LT',
                'lastDay' => '어제 LT', 'lastWeek' => '지난주 dddd LT', 'sameElse' => 'L',
            ),
            'relativeTime' => array(
                'future' => '%s 후', 'past' => '%s 전', 's' => '몇 초', 'ss' => '%d초',
                'm' => '1분', 'mm' => '%d분', 'h' => '한 시간', 'hh' => '%d시간',
                'd' => '하루', 'dd' => '%d일', 'M' => '한 달', 'MM' => '%d달', 'y' => '일 년', 'yy' => '%d년',
            ),
            'dayOfMonthOrdinalParse' => '~\d{1,2}(일|월|주)~u',
            'meridiemParse' => '~오전|오후~u',
        );
    }

    public function ordinal($number, $token = '')
    {
        switch ($token) {
            case 'd':
            case 'D':
            case 'DDD':
                return $number . '일';
            case 'M':
                return $number . '월';
            case 'w':
            case 'W':
                return $number . '주';
            default:
                return $number;
        }
    }

    public function isPM($token)
    {
        return $token === '오후';
    }

    public function meridiem($hour, $minute, $isUpper)
    {
        return $hour < 12 ? '오전' : '오후';
    }
}
