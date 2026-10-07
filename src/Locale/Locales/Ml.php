<?php

namespace Wilkques\Moment\Locale\Locales;

use Wilkques\Moment\Locale\Locale;

class Ml extends Locale
{
    public $abbr = 'ml';

    public static function config()
    {
        return array(
            'months' => explode('_', 'ജനുവരി_ഫെബ്രുവരി_മാർച്ച്_ഏപ്രിൽ_മേയ്_ജൂൺ_ജൂലൈ_ഓഗസ്റ്റ്_സെപ്റ്റംബർ_ഒക്ടോബർ_നവംബർ_ഡിസംബർ'),
            'monthsShort' => explode('_', 'ജനു._ഫെബ്രു._മാർ._ഏപ്രി._മേയ്_ജൂൺ_ജൂലൈ._ഓഗ._സെപ്റ്റ._ഒക്ടോ._നവം._ഡിസം.'),
            'monthsParseExact' => true,
            'weekdays' => explode('_', 'ഞായറാഴ്ച_തിങ്കളാഴ്ച_ചൊവ്വാഴ്ച_ബുധനാഴ്ച_വ്യാഴാഴ്ച_വെള്ളിയാഴ്ച_ശനിയാഴ്ച'),
            'weekdaysShort' => explode('_', 'ഞായർ_തിങ്കൾ_ചൊവ്വ_ബുധൻ_വ്യാഴം_വെള്ളി_ശനി'),
            'weekdaysMin' => explode('_', 'ഞാ_തി_ചൊ_ബു_വ്യാ_വെ_ശ'),
            'longDateFormat' => array(
                'LT' => 'A h:mm -നു',
                'LTS' => 'A h:mm:ss -നു',
                'L' => 'DD/MM/YYYY',
                'LL' => 'D MMMM YYYY',
                'LLL' => 'D MMMM YYYY, A h:mm -നു',
                'LLLL' => 'dddd, D MMMM YYYY, A h:mm -നു',
            ),
            'calendar' => array(
                'sameDay' => '[ഇന്ന്] LT',
                'nextDay' => '[നാളെ] LT',
                'nextWeek' => 'dddd, LT',
                'lastDay' => '[ഇന്നലെ] LT',
                'lastWeek' => '[കഴിഞ്ഞ] dddd, LT',
                'sameElse' => 'L',
            ),
            'relativeTime' => array(
                'future' => '%s കഴിഞ്ഞ്',
                'past' => '%s മുൻപ്',
                's' => 'അൽപ നിമിഷങ്ങൾ',
                'ss' => '%d സെക്കൻഡ്',
                'm' => 'ഒരു മിനിറ്റ്',
                'mm' => '%d മിനിറ്റ്',
                'h' => 'ഒരു മണിക്കൂർ',
                'hh' => '%d മണിക്കൂർ',
                'd' => 'ഒരു ദിവസം',
                'dd' => '%d ദിവസം',
                'M' => 'ഒരു മാസം',
                'MM' => '%d മാസം',
                'y' => 'ഒരു വർഷം',
                'yy' => '%d വർഷം',
            ),
            'meridiemParse' => '~രാത്രി|രാവിലെ|ഉച്ച കഴിഞ്ഞ്|വൈകുന്നേരം|രാത്രി~iu',
        );
    }

    public function meridiemHour($hour, $meridiem)
    {
        if ($hour === 12) {
            $hour = 0;
        }
        if (($meridiem === 'രാത്രി' && $hour >= 4) || $meridiem === 'ഉച്ച കഴിഞ്ഞ്' || $meridiem === 'വൈകുന്നേരം') {
            return $hour + 12;
        }

        return $hour;
    }

    public function meridiem($hour, $minute, $isLower)
    {
        if ($hour < 4) {
            return 'രാത്രി';
        } elseif ($hour < 12) {
            return 'രാവിലെ';
        } elseif ($hour < 17) {
            return 'ഉച്ച കഴിഞ്ഞ്';
        } elseif ($hour < 20) {
            return 'വൈകുന്നേരം';
        }

        return 'രാത്രി';
    }
}
