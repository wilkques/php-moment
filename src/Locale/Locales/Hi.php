<?php

namespace Wilkques\Moment\Locale\Locales;

use Wilkques\Moment\Locale\Locale;

class Hi extends Locale
{
    public $abbr = 'hi';

    public static function config()
    {
        $monthsParse = array(
            '~^जन~iu', '~^फ़र|फर~iu', '~^मार्च~iu', '~^अप्रै~iu', '~^मई~iu', '~^जून~iu',
            '~^जुल~iu', '~^अग~iu', '~^सितं|सित~iu', '~^अक्टू~iu', '~^नव|नवं~iu', '~^दिसं|दिस~iu',
        );
        $shortMonthsParse = array(
            '~^जन~iu', '~^फ़र~iu', '~^मार्च~iu', '~^अप्रै~iu', '~^मई~iu', '~^जून~iu',
            '~^जुल~iu', '~^अग~iu', '~^सित~iu', '~^अक्टू~iu', '~^नव~iu', '~^दिस~iu',
        );

        return array(
            'months' => array(
                'format' => explode('_', 'जनवरी_फ़रवरी_मार्च_अप्रैल_मई_जून_जुलाई_अगस्त_सितम्बर_अक्टूबर_नवम्बर_दिसम्बर'),
                'standalone' => explode('_', 'जनवरी_फरवरी_मार्च_अप्रैल_मई_जून_जुलाई_अगस्त_सितंबर_अक्टूबर_नवंबर_दिसंबर'),
            ),
            'monthsShort' => explode('_', 'जन._फ़र._मार्च_अप्रै._मई_जून_जुल._अग._सित._अक्टू._नव._दिस.'),
            'weekdays' => explode('_', 'रविवार_सोमवार_मंगलवार_बुधवार_गुरूवार_शुक्रवार_शनिवार'),
            'weekdaysShort' => explode('_', 'रवि_सोम_मंगल_बुध_गुरू_शुक्र_शनि'),
            'weekdaysMin' => explode('_', 'र_सो_मं_बु_गु_शु_श'),
            'longDateFormat' => array(
                'LT' => 'A h:mm बजे', 'LTS' => 'A h:mm:ss बजे', 'L' => 'DD/MM/YYYY', 'LL' => 'D MMMM YYYY',
                'LLL' => 'D MMMM YYYY, A h:mm बजे', 'LLLL' => 'dddd, D MMMM YYYY, A h:mm बजे',
            ),
            'monthsParse' => $monthsParse,
            'longMonthsParse' => $monthsParse,
            'shortMonthsParse' => $shortMonthsParse,
            'monthsRegex' => '~^(जनवरी|जन\.?|फ़रवरी|फरवरी|फ़र\.?|मार्च?|अप्रैल|अप्रै\.?|मई?|जून?|जुलाई|जुल\.?|अगस्त|अग\.?|सितम्बर|सितंबर|सित\.?|अक्टूबर|अक्टू\.?|नवम्बर|नवंबर|नव\.?|दिसम्बर|दिसंबर|दिस\.?)~iu',
            'monthsShortRegex' => '~^(जनवरी|जन\.?|फ़रवरी|फरवरी|फ़र\.?|मार्च?|अप्रैल|अप्रै\.?|मई?|जून?|जुलाई|जुल\.?|अगस्त|अग\.?|सितम्बर|सितंबर|सित\.?|अक्टूबर|अक्टू\.?|नवम्बर|नवंबर|नव\.?|दिसम्बर|दिसंबर|दिस\.?)~iu',
            'monthsStrictRegex' => '~^(जनवरी?|फ़रवरी|फरवरी?|मार्च?|अप्रैल?|मई?|जून?|जुलाई?|अगस्त?|सितम्बर|सितंबर|सित?\.?|अक्टूबर|अक्टू\.?|नवम्बर|नवंबर?|दिसम्बर|दिसंबर?)~iu',
            'monthsShortStrictRegex' => '~^(जन\.?|फ़र\.?|मार्च?|अप्रै\.?|मई?|जून?|जुल\.?|अग\.?|सित\.?|अक्टू\.?|नव\.?|दिस\.?)~iu',
            'calendar' => array(
                'sameDay' => '[आज] LT', 'nextDay' => '[कल] LT', 'nextWeek' => 'dddd, LT',
                'lastDay' => '[कल] LT', 'lastWeek' => '[पिछले] dddd, LT', 'sameElse' => 'L',
            ),
            'relativeTime' => array(
                'future' => '%s में', 'past' => '%s पहले', 's' => 'कुछ ही क्षण', 'ss' => '%d सेकंड',
                'm' => 'एक मिनट', 'mm' => '%d मिनट', 'h' => 'एक घंटा', 'hh' => '%d घंटे',
                'd' => 'एक दिन', 'dd' => '%d दिन', 'M' => 'एक महीने', 'MM' => '%d महीने',
                'y' => 'एक वर्ष', 'yy' => '%d वर्ष',
            ),
            'meridiemParse' => '~रात|सुबह|दोपहर|शाम~u',
            'week' => array('dow' => 0, 'doy' => 6),
        );
    }

    protected static function digitMap()
    {
        return array(
            '1' => '१', '2' => '२', '3' => '३', '4' => '४', '5' => '५',
            '6' => '६', '7' => '७', '8' => '८', '9' => '९', '0' => '०',
        );
    }

    public function preparse($string)
    {
        return strtr($string, array_flip(self::digitMap()));
    }

    public function postformat($string)
    {
        return strtr($string, self::digitMap());
    }

    public function meridiemHour($hour, $meridiem)
    {
        if ($hour === 12) {
            $hour = 0;
        }
        if ($meridiem === 'रात') {
            return $hour < 4 ? $hour : $hour + 12;
        } elseif ($meridiem === 'सुबह') {
            return $hour;
        } elseif ($meridiem === 'दोपहर') {
            return $hour >= 10 ? $hour : $hour + 12;
        } elseif ($meridiem === 'शाम') {
            return $hour + 12;
        }

        return null;
    }

    public function meridiem($hour, $minute, $isLower)
    {
        if ($hour < 4) {
            return 'रात';
        } elseif ($hour < 10) {
            return 'सुबह';
        } elseif ($hour < 17) {
            return 'दोपहर';
        } elseif ($hour < 20) {
            return 'शाम';
        }

        return 'रात';
    }
}
