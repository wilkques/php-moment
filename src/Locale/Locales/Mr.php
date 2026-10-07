<?php

namespace Wilkques\Moment\Locale\Locales;

use Wilkques\Moment\Locale\Locale;

class Mr extends Locale
{
    public $abbr = 'mr';

    public static function config()
    {
        return array(
            'calendar' => array(
                'sameDay' => '[आज] LT',
                'nextDay' => '[उद्या] LT',
                'nextWeek' => 'dddd, LT',
                'lastDay' => '[काल] LT',
                'lastWeek' => '[मागील] dddd, LT',
                'sameElse' => 'L',
            ),
            'longDateFormat' => array(
                'LTS' => 'A h:mm:ss वाजता',
                'LT' => 'A h:mm वाजता',
                'L' => 'DD/MM/YYYY',
                'LL' => 'D MMMM YYYY',
                'LLL' => 'D MMMM YYYY, A h:mm वाजता',
                'LLLL' => 'dddd, D MMMM YYYY, A h:mm वाजता',
            ),
            'ordinal' => '%d',
            'dayOfMonthOrdinalParse' => '~\\d{1,2}~u',
            'relativeTime' => array(
                'future' => '%sमध्ये',
                'past' => '%sपूर्वी',
                'w' => 'a week',
                'ww' => '%d weeks',
            ),
            'months' => array('जानेवारी', 'फेब्रुवारी', 'मार्च', 'एप्रिल', 'मे', 'जून', 'जुलै', 'ऑगस्ट', 'सप्टेंबर', 'ऑक्टोबर', 'नोव्हेंबर', 'डिसेंबर'),
            'monthsShort' => array('जाने.', 'फेब्रु.', 'मार्च.', 'एप्रि.', 'मे.', 'जून.', 'जुलै.', 'ऑग.', 'सप्टें.', 'ऑक्टो.', 'नोव्हें.', 'डिसें.'),
            'week' => array(
                'dow' => 0,
                'doy' => 6,
            ),
            'weekdays' => array('रविवार', 'सोमवार', 'मंगळवार', 'बुधवार', 'गुरूवार', 'शुक्रवार', 'शनिवार'),
            'weekdaysMin' => array('र', 'सो', 'मं', 'बु', 'गु', 'शु', 'श'),
            'weekdaysShort' => array('रवि', 'सोम', 'मंगळ', 'बुध', 'गुरू', 'शुक्र', 'शनि'),
            'meridiemParse' => '~पहाटे|सकाळी|दुपारी|सायंकाळी|रात्री~u',
            'monthsParseExact' => true,
        );
    }

    public function relativeTime($number, $withoutSuffix, $key, $isFuture)
    {
        if ($key === 'w' || $key === 'ww') {
            return parent::relativeTime($number, $withoutSuffix, $key, $isFuture); // not set in the JS locale: moment's defaults
        }
        $with = array('s' => 'काही सेकंद', 'ss' => '%d सेकंद', 'm' => 'एक मिनिट', 'mm' => '%d मिनिटे',
            'h' => 'एक तास', 'hh' => '%d तास', 'd' => 'एक दिवस', 'dd' => '%d दिवस',
            'M' => 'एक महिना', 'MM' => '%d महिने', 'y' => 'एक वर्ष', 'yy' => '%d वर्षे');
        $without = array('s' => 'काही सेकंदां', 'ss' => '%d सेकंदां', 'm' => 'एका मिनिटा', 'mm' => '%d मिनिटां',
            'h' => 'एका तासा', 'hh' => '%d तासां', 'd' => 'एका दिवसा', 'dd' => '%d दिवसां',
            'M' => 'एका महिन्या', 'MM' => '%d महिन्यां', 'y' => 'एका वर्षा', 'yy' => '%d वर्षां');
        $list = $withoutSuffix ? $with : $without;

        return str_replace('%d', $number, isset($list[$key]) ? $list[$key] : '');
    }

    public function preparse($string)
    {
        return strtr($string, array('१' => '1', '२' => '2', '३' => '3', '४' => '4', '५' => '5', '६' => '6', '७' => '7', '८' => '8', '९' => '9', '०' => '0'));
    }

    public function postformat($string)
    {
        return strtr($string, array('1' => '१', '2' => '२', '3' => '३', '4' => '४', '5' => '५', '6' => '६', '7' => '७', '8' => '८', '9' => '९', '0' => '०'));
    }

    public function meridiemHour($hour, $meridiem)
    {
        if ($hour === 12) {
            $hour = 0;
        }
        if ($meridiem === 'पहाटे' || $meridiem === 'सकाळी') {
            return $hour;
        } elseif ($meridiem === 'दुपारी' || $meridiem === 'सायंकाळी' || $meridiem === 'रात्री') {
            return $hour >= 12 ? $hour : $hour + 12;
        }

        return null;
    }

    public function meridiem($hour, $minute, $isLower)
    {
        if ($hour >= 0 && $hour < 6) {
            return 'पहाटे';
        } elseif ($hour < 12) {
            return 'सकाळी';
        } elseif ($hour < 17) {
            return 'दुपारी';
        } elseif ($hour < 20) {
            return 'सायंकाळी';
        }

        return 'रात्री';
    }
}
