<?php

namespace Wilkques\Moment\Locale\Locales;

use Wilkques\Moment\Locale\Locale;

class El extends Locale
{
    public $abbr = 'el';

    private static $nominative = array('Ιανουάριος', 'Φεβρουάριος', 'Μάρτιος', 'Απρίλιος', 'Μάιος', 'Ιούνιος', 'Ιούλιος', 'Αύγουστος', 'Σεπτέμβριος', 'Οκτώβριος', 'Νοέμβριος', 'Δεκέμβριος');
    private static $genitive = array('Ιανουαρίου', 'Φεβρουαρίου', 'Μαρτίου', 'Απριλίου', 'Μαΐου', 'Ιουνίου', 'Ιουλίου', 'Αυγούστου', 'Σεπτεμβρίου', 'Οκτωβρίου', 'Νοεμβρίου', 'Δεκεμβρίου');

    public static function config()
    {
        return array(
            'monthsShort' => explode('_', 'Ιαν_Φεβ_Μαρ_Απρ_Μαϊ_Ιουν_Ιουλ_Αυγ_Σεπ_Οκτ_Νοε_Δεκ'),
            'weekdays' => explode('_', 'Κυριακή_Δευτέρα_Τρίτη_Τετάρτη_Πέμπτη_Παρασκευή_Σάββατο'),
            'weekdaysShort' => explode('_', 'Κυρ_Δευ_Τρι_Τετ_Πεμ_Παρ_Σαβ'),
            'weekdaysMin' => explode('_', 'Κυ_Δε_Τρ_Τε_Πε_Πα_Σα'),
            'meridiemParse' => '~[ΠΜ]\.?Μ?\.?~iu',
            'longDateFormat' => array(
                'LT' => 'h:mm A', 'LTS' => 'h:mm:ss A', 'L' => 'DD/MM/YYYY', 'LL' => 'D MMMM YYYY',
                'LLL' => 'D MMMM YYYY h:mm A', 'LLLL' => 'dddd, D MMMM YYYY h:mm A',
            ),
            'relativeTime' => array(
                'future' => 'σε %s', 'past' => '%s πριν',
                's' => 'λίγα δευτερόλεπτα', 'ss' => '%d δευτερόλεπτα',
                'm' => 'ένα λεπτό', 'mm' => '%d λεπτά',
                'h' => 'μία ώρα', 'hh' => '%d ώρες',
                'd' => 'μία μέρα', 'dd' => '%d μέρες',
                'M' => 'ένας μήνας', 'MM' => '%d μήνες',
                'y' => 'ένας χρόνος', 'yy' => '%d χρόνια',
            ),
            'dayOfMonthOrdinalParse' => '~\d{1,2}η~u',
            'ordinal' => '%dη',
            'week' => array('dow' => 1, 'doy' => 4),
        );
    }

    public function months($m = null, $format = '')
    {
        if ($m === null) {
            return self::$nominative;
        }
        $i = self::monthIndex($m);
        if (is_string($format)) {
            $pos = strpos($format, 'MMMM');
            $before = $pos === false ? '' : substr($format, 0, $pos);
            if (strpos($before, 'D') !== false) {
                return self::$genitive[$i];
            }
        }

        return self::$nominative[$i];
    }

    public function meridiem($hours, $minutes, $isLower)
    {
        if ($hours > 11) {
            return $isLower ? 'μμ' : 'ΜΜ';
        }

        return $isLower ? 'πμ' : 'ΠΜ';
    }

    public function isPM($input)
    {
        return preg_match('~^μ~u', self::lower($input . '')) === 1;
    }

    public function calendar($key, $mom = null, $now = null)
    {
        switch ($key) {
            case 'sameDay':
                $output = '[Σήμερα {}] LT';
                break;
            case 'nextDay':
                $output = '[Αύριο {}] LT';
                break;
            case 'nextWeek':
                $output = 'dddd [{}] LT';
                break;
            case 'lastDay':
                $output = '[Χθες {}] LT';
                break;
            case 'lastWeek':
                $output = $mom && $mom->day() === 6
                    ? '[το προηγούμενο] dddd [{}] LT'
                    : '[την προηγούμενη] dddd [{}] LT';
                break;
            default:
                $output = 'L';
        }
        $hours = $mom ? $mom->hour() : null;

        return str_replace('{}', $hours !== null && $hours % 12 === 1 ? 'στη' : 'στις', $output);
    }
}
