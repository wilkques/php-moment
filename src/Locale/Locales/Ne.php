<?php

namespace Wilkques\Moment\Locale\Locales;

use Wilkques\Moment\Locale\Locale;

class Ne extends Locale
{
    public $abbr = 'ne';

    public static function config()
    {
        return array(
            'months' => explode('_', 'जनवरी_फेब्रुवरी_मार्च_अप्रिल_मई_जुन_जुलाई_अगष्ट_सेप्टेम्बर_अक्टोबर_नोभेम्बर_डिसेम्बर'),
            'monthsShort' => explode('_', 'जन._फेब्रु._मार्च_अप्रि._मई_जुन_जुलाई._अग._सेप्ट._अक्टो._नोभे._डिसे.'),
            'monthsParseExact' => true,
            'weekdays' => explode('_', 'आइतबार_सोमबार_मङ्गलबार_बुधबार_बिहिबार_शुक्रबार_शनिबार'),
            'weekdaysShort' => explode('_', 'आइत._सोम._मङ्गल._बुध._बिहि._शुक्र._शनि.'),
            'weekdaysMin' => explode('_', 'आ._सो._मं._बु._बि._शु._श.'),
            'weekdaysParseExact' => true,
            'longDateFormat' => array(
                'LT' => 'Aको h:mm बजे', 'LTS' => 'Aको h:mm:ss बजे', 'L' => 'DD/MM/YYYY', 'LL' => 'D MMMM YYYY',
                'LLL' => 'D MMMM YYYY, Aको h:mm बजे', 'LLLL' => 'dddd, D MMMM YYYY, Aको h:mm बजे',
            ),
            'meridiemParse' => '~राति|बिहान|दिउँसो|साँझ~u',
            'calendar' => array(
                'sameDay' => '[आज] LT', 'nextDay' => '[भोलि] LT', 'nextWeek' => '[आउँदो] dddd[,] LT',
                'lastDay' => '[हिजो] LT', 'lastWeek' => '[गएको] dddd[,] LT', 'sameElse' => 'L',
            ),
            'relativeTime' => array(
                'future' => '%sमा', 'past' => '%s अगाडि', 's' => 'केही क्षण', 'ss' => '%d सेकेण्ड',
                'm' => 'एक मिनेट', 'mm' => '%d मिनेट', 'h' => 'एक घण्टा', 'hh' => '%d घण्टा',
                'd' => 'एक दिन', 'dd' => '%d दिन', 'M' => 'एक महिना', 'MM' => '%d महिना', 'y' => 'एक बर्ष', 'yy' => '%d बर्ष',
            ),
            'week' => array('dow' => 0, 'doy' => 6),
        );
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
        if ($meridiem === 'राति') {
            return $hour < 4 ? $hour : $hour + 12;
        } elseif ($meridiem === 'बिहान') {
            return $hour;
        } elseif ($meridiem === 'दिउँसो') {
            return $hour >= 10 ? $hour : $hour + 12;
        } elseif ($meridiem === 'साँझ') {
            return $hour + 12;
        }

        return null;
    }

    public function meridiem($hour, $minute, $isLower)
    {
        if ($hour < 3) {
            return 'राति';
        } elseif ($hour < 12) {
            return 'बिहान';
        } elseif ($hour < 16) {
            return 'दिउँसो';
        } elseif ($hour < 20) {
            return 'साँझ';
        }

        return 'राति';
    }
}
