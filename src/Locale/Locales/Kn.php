<?php

namespace Wilkques\Moment\Locale\Locales;

use Wilkques\Moment\Locale\Locale;

class Kn extends Locale
{
    public $abbr = 'kn';

    private static $symbolMap = array(
        '1' => '೧', '2' => '೨', '3' => '೩', '4' => '೪', '5' => '೫',
        '6' => '೬', '7' => '೭', '8' => '೮', '9' => '೯', '0' => '೦',
    );

    private static $numberMap = array(
        '೧' => '1', '೨' => '2', '೩' => '3', '೪' => '4', '೫' => '5',
        '೬' => '6', '೭' => '7', '೮' => '8', '೯' => '9', '೦' => '0',
    );

    public static function config()
    {
        return array(
            'months' => array('ಜನವರಿ', 'ಫೆಬ್ರವರಿ', 'ಮಾರ್ಚ್', 'ಏಪ್ರಿಲ್', 'ಮೇ', 'ಜೂನ್', 'ಜುಲೈ', 'ಆಗಸ್ಟ್', 'ಸೆಪ್ಟೆಂಬರ್', 'ಅಕ್ಟೋಬರ್', 'ನವೆಂಬರ್', 'ಡಿಸೆಂಬರ್'),
            'monthsShort' => array('ಜನ', 'ಫೆಬ್ರ', 'ಮಾರ್ಚ್', 'ಏಪ್ರಿಲ್', 'ಮೇ', 'ಜೂನ್', 'ಜುಲೈ', 'ಆಗಸ್ಟ್', 'ಸೆಪ್ಟೆಂ', 'ಅಕ್ಟೋ', 'ನವೆಂ', 'ಡಿಸೆಂ'),
            'monthsParseExact' => true,
            'weekdays' => array('ಭಾನುವಾರ', 'ಸೋಮವಾರ', 'ಮಂಗಳವಾರ', 'ಬುಧವಾರ', 'ಗುರುವಾರ', 'ಶುಕ್ರವಾರ', 'ಶನಿವಾರ'),
            'weekdaysShort' => array('ಭಾನು', 'ಸೋಮ', 'ಮಂಗಳ', 'ಬುಧ', 'ಗುರು', 'ಶುಕ್ರ', 'ಶನಿ'),
            'weekdaysMin' => array('ಭಾ', 'ಸೋ', 'ಮಂ', 'ಬು', 'ಗು', 'ಶು', 'ಶ'),
            'longDateFormat' => array(
                'LT' => 'A h:mm', 'LTS' => 'A h:mm:ss', 'L' => 'DD/MM/YYYY',
                'LL' => 'D MMMM YYYY', 'LLL' => 'D MMMM YYYY, A h:mm', 'LLLL' => 'dddd, D MMMM YYYY, A h:mm',
            ),
            'calendar' => array(
                'sameDay' => '[ಇಂದು] LT', 'nextDay' => '[ನಾಳೆ] LT', 'nextWeek' => 'dddd, LT',
                'lastDay' => '[ನಿನ್ನೆ] LT', 'lastWeek' => '[ಕೊನೆಯ] dddd, LT', 'sameElse' => 'L',
            ),
            'relativeTime' => array(
                'future' => '%s ನಂತರ', 'past' => '%s ಹಿಂದೆ',
                's' => 'ಕೆಲವು ಕ್ಷಣಗಳು', 'ss' => '%d ಸೆಕೆಂಡುಗಳು', 'm' => 'ಒಂದು ನಿಮಿಷ', 'mm' => '%d ನಿಮಿಷ',
                'h' => 'ಒಂದು ಗಂಟೆ', 'hh' => '%d ಗಂಟೆ', 'd' => 'ಒಂದು ದಿನ', 'dd' => '%d ದಿನ',
                'M' => 'ಒಂದು ತಿಂಗಳು', 'MM' => '%d ತಿಂಗಳು', 'y' => 'ಒಂದು ವರ್ಷ', 'yy' => '%d ವರ್ಷ',
            ),
            'meridiemParse' => '~ರಾತ್ರಿ|ಬೆಳಿಗ್ಗೆ|ಮಧ್ಯಾಹ್ನ|ಸಂಜೆ~u',
            'dayOfMonthOrdinalParse' => '~\d{1,2}(ನೇ)~u',
            'week' => array('dow' => 0, 'doy' => 6),
        );
    }

    public function preparse($string)
    {
        return strtr($string, self::$numberMap);
    }

    public function postformat($string)
    {
        return strtr($string, self::$symbolMap);
    }

    public function meridiemHour($hour, $meridiem)
    {
        if ($hour === 12) {
            $hour = 0;
        }
        if ($meridiem === 'ರಾತ್ರಿ') {
            return $hour < 4 ? $hour : $hour + 12;
        } elseif ($meridiem === 'ಬೆಳಿಗ್ಗೆ') {
            return $hour;
        } elseif ($meridiem === 'ಮಧ್ಯಾಹ್ನ') {
            return $hour >= 10 ? $hour : $hour + 12;
        } elseif ($meridiem === 'ಸಂಜೆ') {
            return $hour + 12;
        }

        return null;
    }

    public function meridiem($hour, $minute, $isLower)
    {
        if ($hour < 4) {
            return 'ರಾತ್ರಿ';
        } elseif ($hour < 10) {
            return 'ಬೆಳಿಗ್ಗೆ';
        } elseif ($hour < 17) {
            return 'ಮಧ್ಯಾಹ್ನ';
        } elseif ($hour < 20) {
            return 'ಸಂಜೆ';
        }

        return 'ರಾತ್ರಿ';
    }

    public function ordinal($number, $token = '')
    {
        return $number . 'ನೇ';
    }
}
