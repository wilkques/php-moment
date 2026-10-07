<?php

namespace Wilkques\Moment\Locale\Locales;

use Wilkques\Moment\Locale\Locale;

class My extends Locale
{
    public $abbr = 'my';

    public static function config()
    {
        return array(
            'months' => explode('_', 'ဇန်နဝါရီ_ဖေဖော်ဝါရီ_မတ်_ဧပြီ_မေ_ဇွန်_ဇူလိုင်_သြဂုတ်_စက်တင်ဘာ_အောက်တိုဘာ_နိုဝင်ဘာ_ဒီဇင်ဘာ'),
            'monthsShort' => explode('_', 'ဇန်_ဖေ_မတ်_ပြီ_မေ_ဇွန်_လိုင်_သြ_စက်_အောက်_နို_ဒီ'),
            'weekdays' => explode('_', 'တနင်္ဂနွေ_တနင်္လာ_အင်္ဂါ_ဗုဒ္ဓဟူး_ကြာသပတေး_သောကြာ_စနေ'),
            'weekdaysShort' => explode('_', 'နွေ_လာ_ဂါ_ဟူး_ကြာ_သော_နေ'),
            'weekdaysMin' => explode('_', 'နွေ_လာ_ဂါ_ဟူး_ကြာ_သော_နေ'),
            'longDateFormat' => array(
                'LT' => 'HH:mm',
                'LTS' => 'HH:mm:ss',
                'L' => 'DD/MM/YYYY',
                'LL' => 'D MMMM YYYY',
                'LLL' => 'D MMMM YYYY HH:mm',
                'LLLL' => 'dddd D MMMM YYYY HH:mm',
            ),
            'calendar' => array(
                'sameDay' => '[ယနေ.] LT [မှာ]',
                'nextDay' => '[မနက်ဖြန်] LT [မှာ]',
                'nextWeek' => 'dddd LT [မှာ]',
                'lastDay' => '[မနေ.က] LT [မှာ]',
                'lastWeek' => '[ပြီးခဲ့သော] dddd LT [မှာ]',
                'sameElse' => 'L',
            ),
            'relativeTime' => array(
                'future' => 'လာမည့် %s မှာ',
                'past' => 'လွန်ခဲ့သော %s က',
                's' => 'စက္ကန်.အနည်းငယ်',
                'ss' => '%d စက္ကန့်',
                'm' => 'တစ်မိနစ်',
                'mm' => '%d မိနစ်',
                'h' => 'တစ်နာရီ',
                'hh' => '%d နာရီ',
                'd' => 'တစ်ရက်',
                'dd' => '%d ရက်',
                'M' => 'တစ်လ',
                'MM' => '%d လ',
                'y' => 'တစ်နှစ်',
                'yy' => '%d နှစ်',
            ),
            'week' => array('dow' => 1, 'doy' => 4),
        );
    }

    protected static $symbolMap = array(
        '1' => '၁', '2' => '၂', '3' => '၃', '4' => '၄', '5' => '၅',
        '6' => '၆', '7' => '၇', '8' => '၈', '9' => '၉', '0' => '၀',
    );

    protected static $numberMap = array(
        '၁' => '1', '၂' => '2', '၃' => '3', '၄' => '4', '၅' => '5',
        '၆' => '6', '၇' => '7', '၈' => '8', '၉' => '9', '၀' => '0',
    );

    public function preparse($string)
    {
        return strtr($string, self::$numberMap);
    }

    public function postformat($string)
    {
        return strtr($string, self::$symbolMap);
    }
}
