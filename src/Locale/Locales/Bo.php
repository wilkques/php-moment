<?php

namespace Wilkques\Moment\Locale\Locales;

use Wilkques\Moment\Locale\Locale;

class Bo extends Locale
{
    public $abbr = 'bo';

    public static function config()
    {
        return array(
            'months' => explode('_', 'ཟླ་བ་དང་པོ_ཟླ་བ་གཉིས་པ_ཟླ་བ་གསུམ་པ_ཟླ་བ་བཞི་པ_ཟླ་བ་ལྔ་པ_ཟླ་བ་དྲུག་པ_ཟླ་བ་བདུན་པ_ཟླ་བ་བརྒྱད་པ_ཟླ་བ་དགུ་པ_ཟླ་བ་བཅུ་པ_ཟླ་བ་བཅུ་གཅིག་པ_ཟླ་བ་བཅུ་གཉིས་པ'),
            'monthsShort' => explode('_', 'ཟླ་1_ཟླ་2_ཟླ་3_ཟླ་4_ཟླ་5_ཟླ་6_ཟླ་7_ཟླ་8_ཟླ་9_ཟླ་10_ཟླ་11_ཟླ་12'),
            'monthsShortRegex' => '~^(ཟླ་\d{1,2})~u',
            'monthsParseExact' => true,
            'weekdays' => explode('_', 'གཟའ་ཉི་མ་_གཟའ་ཟླ་བ་_གཟའ་མིག་དམར་_གཟའ་ལྷག་པ་_གཟའ་ཕུར་བུ_གཟའ་པ་སངས་_གཟའ་སྤེན་པ་'),
            'weekdaysShort' => explode('_', 'ཉི་མ་_ཟླ་བ་_མིག་དམར་_ལྷག་པ་_ཕུར་བུ_པ་སངས་_སྤེན་པ་'),
            'weekdaysMin' => explode('_', 'ཉི_ཟླ_མིག_ལྷག_ཕུར_སངས_སྤེན'),
            'longDateFormat' => array(
                'LT' => 'A h:mm', 'LTS' => 'A h:mm:ss', 'L' => 'DD/MM/YYYY', 'LL' => 'D MMMM YYYY',
                'LLL' => 'D MMMM YYYY, A h:mm', 'LLLL' => 'dddd, D MMMM YYYY, A h:mm',
            ),
            'calendar' => array(
                'sameDay' => '[དི་རིང] LT', 'nextDay' => '[སང་ཉིན] LT', 'nextWeek' => '[བདུན་ཕྲག་རྗེས་མ], LT',
                'lastDay' => '[ཁ་སང] LT', 'lastWeek' => '[བདུན་ཕྲག་མཐའ་མ] dddd, LT', 'sameElse' => 'L',
            ),
            'relativeTime' => array(
                'future' => '%s ལ་', 'past' => '%s སྔན་ལ', 's' => 'ལམ་སང', 'ss' => '%d སྐར་ཆ།',
                'm' => 'སྐར་མ་གཅིག', 'mm' => '%d སྐར་མ', 'h' => 'ཆུ་ཚོད་གཅིག', 'hh' => '%d ཆུ་ཚོད',
                'd' => 'ཉིན་གཅིག', 'dd' => '%d ཉིན་', 'M' => 'ཟླ་བ་གཅིག', 'MM' => '%d ཟླ་བ', 'y' => 'ལོ་གཅིག', 'yy' => '%d ལོ',
            ),
            'meridiemParse' => '~མཚན་མོ|ཞོགས་ཀས|ཉིན་གུང|དགོང་དག|མཚན་མོ~u',
            'week' => array('dow' => 0, 'doy' => 6),
        );
    }

    public function preparse($string)
    {
        return strtr($string, array('༡' => '1', '༢' => '2', '༣' => '3', '༤' => '4', '༥' => '5', '༦' => '6', '༧' => '7', '༨' => '8', '༩' => '9', '༠' => '0'));
    }

    public function postformat($string)
    {
        return strtr($string, array('1' => '༡', '2' => '༢', '3' => '༣', '4' => '༤', '5' => '༥', '6' => '༦', '7' => '༧', '8' => '༨', '9' => '༩', '0' => '༠'));
    }

    public function meridiemHour($hour, $meridiem)
    {
        if ($hour === 12) {
            $hour = 0;
        }
        if (($meridiem === 'མཚན་མོ' && $hour >= 4) || ($meridiem === 'ཉིན་གུང' && $hour < 5) || $meridiem === 'དགོང་དག') {
            return $hour + 12;
        }

        return $hour;
    }

    public function meridiem($hour, $minute, $isLower)
    {
        if ($hour < 4) {
            return 'མཚན་མོ';
        } elseif ($hour < 10) {
            return 'ཞོགས་ཀས';
        } elseif ($hour < 17) {
            return 'ཉིན་གུང';
        } elseif ($hour < 20) {
            return 'དགོང་དག';
        }

        return 'མཚན་མོ';
    }
}
