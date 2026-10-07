<?php

namespace Wilkques\Moment\Locale\Locales;

use Wilkques\Moment\Locale\Locale;

class GomDeva extends Locale
{
    public $abbr = 'gom-deva';

    public static function config()
    {
        return array(
            'calendar' => array(
                'sameDay' => '[आयज] LT',
                'nextDay' => '[फाल्यां] LT',
                'nextWeek' => '[फुडलो] dddd[,] LT',
                'lastDay' => '[काल] LT',
                'lastWeek' => '[फाटलो] dddd[,] LT',
                'sameElse' => 'L',
            ),
            'longDateFormat' => array(
                'LTS' => 'A h:mm:ss [वाजतां]',
                'LT' => 'A h:mm [वाजतां]',
                'L' => 'DD-MM-YYYY',
                'LL' => 'D MMMM YYYY',
                'LLL' => 'D MMMM YYYY A h:mm [वाजतां]',
                'LLLL' => 'dddd, MMMM Do, YYYY, A h:mm [वाजतां]',
                'llll' => 'ddd, D MMM YYYY, A h:mm [वाजतां]',
            ),
            'dayOfMonthOrdinalParse' => '~\\d{1,2}(वेर)~u',
            'relativeTime' => array(
                'future' => '%s',
                'past' => '%s आदीं',
                'w' => 'a week',
                'ww' => '%d weeks',
            ),
            'months' => array(
                'standalone' => array('जानेवारी', 'फेब्रुवारी', 'मार्च', 'एप्रील', 'मे', 'जून', 'जुलय', 'ऑगस्ट', 'सप्टेंबर', 'ऑक्टोबर', 'नोव्हेंबर', 'डिसेंबर'),
                'format' => array('जानेवारीच्या', 'फेब्रुवारीच्या', 'मार्चाच्या', 'एप्रीलाच्या', 'मेयाच्या', 'जूनाच्या', 'जुलयाच्या', 'ऑगस्टाच्या', 'सप्टेंबराच्या', 'ऑक्टोबराच्या', 'नोव्हेंबराच्या', 'डिसेंबराच्या'),
                'isFormat' => '~MMMM(\\s)+D[oD]?~u',
            ),
            'monthsShort' => array('जाने.', 'फेब्रु.', 'मार्च', 'एप्री.', 'मे', 'जून', 'जुल.', 'ऑग.', 'सप्टें.', 'ऑक्टो.', 'नोव्हें.', 'डिसें.'),
            'week' => array(
                'dow' => 0,
                'doy' => 3,
            ),
            'weekdays' => array('आयतार', 'सोमार', 'मंगळार', 'बुधवार', 'बिरेस्तार', 'सुक्रार', 'शेनवार'),
            'weekdaysMin' => array('आ', 'सो', 'मं', 'बु', 'ब्रे', 'सु', 'शे'),
            'weekdaysShort' => array('आयत.', 'सोम.', 'मंगळ.', 'बुध.', 'ब्रेस्त.', 'सुक्र.', 'शेन.'),
            'meridiemParse' => '~राती|सकाळीं|दनपारां|सांजे~u',
            'monthsParseExact' => true,
            'weekdaysParseExact' => true,
        );
    }

    public function relativeTime($number, $withoutSuffix, $key, $isFuture)
    {
        if ($key === 'w' || $key === 'ww') {
            return parent::relativeTime($number, $withoutSuffix, $key, $isFuture); // not set in the JS locale: moment's defaults
        }
        $format = array(
            's' => array('थोडया सॅकंडांनी', 'थोडे सॅकंड'),
            'ss' => array($number . ' सॅकंडांनी', $number . ' सॅकंड'),
            'm' => array('एका मिणटान', 'एक मिनूट'),
            'mm' => array($number . ' मिणटांनी', $number . ' मिणटां'),
            'h' => array('एका वरान', 'एक वर'),
            'hh' => array($number . ' वरांनी', $number . ' वरां'),
            'd' => array('एका दिसान', 'एक दीस'),
            'dd' => array($number . ' दिसांनी', $number . ' दीस'),
            'M' => array('एका म्हयन्यान', 'एक म्हयनो'),
            'MM' => array($number . ' म्हयन्यानी', $number . ' म्हयने'),
            'y' => array('एका वर्सान', 'एक वर्स'),
            'yy' => array($number . ' वर्सांनी', $number . ' वर्सां'),
        );

        return $format[$key][$isFuture ? 0 : 1];
    }

    public function ordinal($number, $token = '')
    {
        return $token === 'D' ? $number . 'वेर' : $number;
    }

    public function meridiemHour($hour, $meridiem)
    {
        if ($hour === 12) {
            $hour = 0;
        }
        if ($meridiem === 'राती') {
            return $hour < 4 ? $hour : $hour + 12;
        } elseif ($meridiem === 'सकाळीं') {
            return $hour;
        } elseif ($meridiem === 'दनपारां') {
            return $hour > 12 ? $hour : $hour + 12;
        } elseif ($meridiem === 'सांजे') {
            return $hour + 12;
        }

        return null;
    }

    public function meridiem($hour, $minute, $isLower)
    {
        if ($hour < 4) {
            return 'राती';
        } elseif ($hour < 12) {
            return 'सकाळीं';
        } elseif ($hour < 16) {
            return 'दनपारां';
        } elseif ($hour < 20) {
            return 'सांजे';
        }

        return 'राती';
    }
}
