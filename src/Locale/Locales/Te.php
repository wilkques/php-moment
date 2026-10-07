<?php

namespace Wilkques\Moment\Locale\Locales;

use Wilkques\Moment\Locale\Locale;

class Te extends Locale
{
    public $abbr = 'te';

    public static function config()
    {
        return array(
            'calendar' => array(
                'sameDay' => '[నేడు] LT',
                'nextDay' => '[రేపు] LT',
                'nextWeek' => 'dddd, LT',
                'lastDay' => '[నిన్న] LT',
                'lastWeek' => '[గత] dddd, LT',
                'sameElse' => 'L',
            ),
            'longDateFormat' => array(
                'LTS' => 'A h:mm:ss',
                'LT' => 'A h:mm',
                'L' => 'DD/MM/YYYY',
                'LL' => 'D MMMM YYYY',
                'LLL' => 'D MMMM YYYY, A h:mm',
                'LLLL' => 'dddd, D MMMM YYYY, A h:mm',
            ),
            'ordinal' => '%dవ',
            'dayOfMonthOrdinalParse' => '~\\d{1,2}వ~u',
            'relativeTime' => array(
                'future' => '%s లో',
                'past' => '%s క్రితం',
                's' => 'కొన్ని క్షణాలు',
                'ss' => '%d సెకన్లు',
                'm' => 'ఒక నిమిషం',
                'mm' => '%d నిమిషాలు',
                'h' => 'ఒక గంట',
                'hh' => '%d గంటలు',
                'd' => 'ఒక రోజు',
                'dd' => '%d రోజులు',
                'w' => 'a week',
                'ww' => '%d weeks',
                'M' => 'ఒక నెల',
                'MM' => '%d నెలలు',
                'y' => 'ఒక సంవత్సరం',
                'yy' => '%d సంవత్సరాలు',
            ),
            'months' => array('జనవరి', 'ఫిబ్రవరి', 'మార్చి', 'ఏప్రిల్', 'మే', 'జూన్', 'జులై', 'ఆగస్టు', 'సెప్టెంబర్', 'అక్టోబర్', 'నవంబర్', 'డిసెంబర్'),
            'monthsShort' => array('జన.', 'ఫిబ్ర.', 'మార్చి', 'ఏప్రి.', 'మే', 'జూన్', 'జులై', 'ఆగ.', 'సెప్.', 'అక్టో.', 'నవ.', 'డిసె.'),
            'week' => array(
                'dow' => 0,
                'doy' => 6,
            ),
            'weekdays' => array('ఆదివారం', 'సోమవారం', 'మంగళవారం', 'బుధవారం', 'గురువారం', 'శుక్రవారం', 'శనివారం'),
            'weekdaysMin' => array('ఆ', 'సో', 'మం', 'బు', 'గు', 'శు', 'శ'),
            'weekdaysShort' => array('ఆది', 'సోమ', 'మంగళ', 'బుధ', 'గురు', 'శుక్ర', 'శని'),
            'meridiemParse' => '~రాత్రి|ఉదయం|మధ్యాహ్నం|సాయంత్రం~u',
            'monthsParseExact' => true,
        );
    }

    public function meridiemHour($hour, $meridiem)
    {
        if ($hour === 12) {
            $hour = 0;
        }
        if ($meridiem === 'రాత్రి') {
            return $hour < 4 ? $hour : $hour + 12;
        } elseif ($meridiem === 'ఉదయం') {
            return $hour;
        } elseif ($meridiem === 'మధ్యాహ్నం') {
            return $hour >= 10 ? $hour : $hour + 12;
        } elseif ($meridiem === 'సాయంత్రం') {
            return $hour + 12;
        }

        return null;
    }

    public function meridiem($hour, $minute, $isLower)
    {
        if ($hour < 4) {
            return 'రాత్రి';
        } elseif ($hour < 10) {
            return 'ఉదయం';
        } elseif ($hour < 17) {
            return 'మధ్యాహ్నం';
        } elseif ($hour < 20) {
            return 'సాయంత్రం';
        }

        return 'రాత్రి';
    }
}
