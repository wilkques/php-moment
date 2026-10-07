<?php

namespace Wilkques\Moment\Locale\Locales;

use Wilkques\Moment\Locale\Locale;

class Th extends Locale
{
    public $abbr = 'th';

    public static function config()
    {
        return array(
            'months' => array('มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน', 'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'),
            'monthsShort' => array('ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'),
            'monthsParseExact' => true,
            'weekdays' => array('อาทิตย์', 'จันทร์', 'อังคาร', 'พุธ', 'พฤหัสบดี', 'ศุกร์', 'เสาร์'),
            'weekdaysShort' => array('อาทิตย์', 'จันทร์', 'อังคาร', 'พุธ', 'พฤหัส', 'ศุกร์', 'เสาร์'),
            'weekdaysMin' => array('อา.', 'จ.', 'อ.', 'พ.', 'พฤ.', 'ศ.', 'ส.'),
            'weekdaysParseExact' => true,
            'longDateFormat' => array(
                'LT' => 'H:mm', 'LTS' => 'H:mm:ss', 'L' => 'DD/MM/YYYY',
                'LL' => 'D MMMM YYYY', 'LLL' => 'D MMMM YYYY เวลา H:mm', 'LLLL' => 'วันddddที่ D MMMM YYYY เวลา H:mm',
            ),
            'meridiemParse' => '~ก่อนเที่ยง|หลังเที่ยง~u',
            'calendar' => array(
                'sameDay' => '[วันนี้ เวลา] LT', 'nextDay' => '[พรุ่งนี้ เวลา] LT', 'nextWeek' => 'dddd[หน้า เวลา] LT',
                'lastDay' => '[เมื่อวานนี้ เวลา] LT', 'lastWeek' => '[วัน]dddd[ที่แล้ว เวลา] LT', 'sameElse' => 'L',
            ),
            'relativeTime' => array(
                'future' => 'อีก %s', 'past' => '%sที่แล้ว',
                's' => 'ไม่กี่วินาที', 'ss' => '%d วินาที', 'm' => '1 นาที', 'mm' => '%d นาที',
                'h' => '1 ชั่วโมง', 'hh' => '%d ชั่วโมง', 'd' => '1 วัน', 'dd' => '%d วัน',
                'w' => '1 สัปดาห์', 'ww' => '%d สัปดาห์',
                'M' => '1 เดือน', 'MM' => '%d เดือน', 'y' => '1 ปี', 'yy' => '%d ปี',
            ),
        );
    }

    public function isPM($input)
    {
        return $input === 'หลังเที่ยง';
    }

    public function meridiem($hour, $minute, $isLower)
    {
        return $hour < 12 ? 'ก่อนเที่ยง' : 'หลังเที่ยง';
    }
}
