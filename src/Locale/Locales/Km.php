<?php

namespace Wilkques\Moment\Locale\Locales;

use Wilkques\Moment\Locale\Locale;

class Km extends Locale
{
    public $abbr = 'km';

    public static function config()
    {
        return array(
            'months' => explode('_', 'មករា_កុម្ភៈ_មីនា_មេសា_ឧសភា_មិថុនា_កក្កដា_សីហា_កញ្ញា_តុលា_វិច្ឆិកា_ធ្នូ'),
            'monthsShort' => explode('_', 'មករា_កុម្ភៈ_មីនា_មេសា_ឧសភា_មិថុនា_កក្កដា_សីហា_កញ្ញា_តុលា_វិច្ឆិកា_ធ្នូ'),
            'weekdays' => explode('_', 'អាទិត្យ_ច័ន្ទ_អង្គារ_ពុធ_ព្រហស្បតិ៍_សុក្រ_សៅរ៍'),
            'weekdaysShort' => explode('_', 'អា_ច_អ_ព_ព្រ_សុ_ស'),
            'weekdaysMin' => explode('_', 'អា_ច_អ_ព_ព្រ_សុ_ស'),
            'weekdaysParseExact' => true,
            'longDateFormat' => array(
                'LT' => 'HH:mm',
                'LTS' => 'HH:mm:ss',
                'L' => 'DD/MM/YYYY',
                'LL' => 'D MMMM YYYY',
                'LLL' => 'D MMMM YYYY HH:mm',
                'LLLL' => 'dddd, D MMMM YYYY HH:mm',
            ),
            'meridiemParse' => '~ព្រឹក|ល្ងាច~u',
            'calendar' => array(
                'sameDay' => '[ថ្ងៃនេះ ម៉ោង] LT',
                'nextDay' => '[ស្អែក ម៉ោង] LT',
                'nextWeek' => 'dddd [ម៉ោង] LT',
                'lastDay' => '[ម្សិលមិញ ម៉ោង] LT',
                'lastWeek' => 'dddd [សប្តាហ៍មុន] [ម៉ោង] LT',
                'sameElse' => 'L',
            ),
            'relativeTime' => array(
                'future' => '%sទៀត',
                'past' => '%sមុន',
                's' => 'ប៉ុន្មានវិនាទី',
                'ss' => '%d វិនាទី',
                'm' => 'មួយនាទី',
                'mm' => '%d នាទី',
                'h' => 'មួយម៉ោង',
                'hh' => '%d ម៉ោង',
                'd' => 'មួយថ្ងៃ',
                'dd' => '%d ថ្ងៃ',
                'M' => 'មួយខែ',
                'MM' => '%d ខែ',
                'y' => 'មួយឆ្នាំ',
                'yy' => '%d ឆ្នាំ',
            ),
            'dayOfMonthOrdinalParse' => '~ទី\d{1,2}~u',
            'ordinal' => 'ទី%d',
            'week' => array('dow' => 1, 'doy' => 4),
        );
    }

    protected static $symbolMap = array(
        '1' => '១', '2' => '២', '3' => '៣', '4' => '៤', '5' => '៥',
        '6' => '៦', '7' => '៧', '8' => '៨', '9' => '៩', '0' => '០',
    );

    protected static $numberMap = array(
        '១' => '1', '២' => '2', '៣' => '3', '៤' => '4', '៥' => '5',
        '៦' => '6', '៧' => '7', '៨' => '8', '៩' => '9', '០' => '0',
    );

    public function isPM($input)
    {
        return $input === 'ល្ងាច';
    }

    public function meridiem($hour, $minute, $isLower)
    {
        return $hour < 12 ? 'ព្រឹក' : 'ល្ងាច';
    }

    public function preparse($string)
    {
        return strtr($string, self::$numberMap);
    }

    public function postformat($string)
    {
        return strtr($string, self::$symbolMap);
    }
}
