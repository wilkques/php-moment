<?php

namespace Wilkques\Moment\Locale\Locales;

use Wilkques\Moment\Locale\Locale;

class Lb extends Locale
{
    public $abbr = 'lb';

    private static $words = array(
        'm' => array('eng Minutt', 'enger Minutt'),
        'h' => array('eng Stonn', 'enger Stonn'),
        'd' => array('een Dag', 'engem Dag'),
        'M' => array('ee Mount', 'engem Mount'),
        'y' => array('ee Joer', 'engem Joer'),
    );

    public static function config()
    {
        return array(
            'months' => array('Januar', 'Februar', 'Mäerz', 'Abrëll', 'Mee', 'Juni', 'Juli', 'August', 'September', 'Oktober', 'November', 'Dezember'),
            'monthsShort' => array('Jan.', 'Febr.', 'Mrz.', 'Abr.', 'Mee', 'Jun.', 'Jul.', 'Aug.', 'Sept.', 'Okt.', 'Nov.', 'Dez.'),
            'monthsParseExact' => true,
            'weekdays' => array('Sonndeg', 'Méindeg', 'Dënschdeg', 'Mëttwoch', 'Donneschdeg', 'Freideg', 'Samschdeg'),
            'weekdaysShort' => array('So.', 'Mé.', 'Dë.', 'Më.', 'Do.', 'Fr.', 'Sa.'),
            'weekdaysMin' => array('So', 'Mé', 'Dë', 'Më', 'Do', 'Fr', 'Sa'),
            'weekdaysParseExact' => true,
            'longDateFormat' => array(
                'LT' => 'H:mm [Auer]', 'LTS' => 'H:mm:ss [Auer]', 'L' => 'DD.MM.YYYY', 'LL' => 'D. MMMM YYYY',
                'LLL' => 'D. MMMM YYYY H:mm [Auer]', 'LLLL' => 'dddd, D. MMMM YYYY H:mm [Auer]',
            ),
            'calendar' => array(
                'sameDay' => '[Haut um] LT', 'sameElse' => 'L', 'nextDay' => '[Muer um] LT',
                'nextWeek' => 'dddd [um] LT', 'lastDay' => '[Gëschter um] LT', 'lastWeek' => '',
            ),
            'relativeTime' => array(
                'future' => '', 'past' => '', 's' => 'e puer Sekonnen', 'ss' => '%d Sekonnen',
                'm' => '', 'mm' => '%d Minutten', 'h' => '', 'hh' => '%d Stonnen',
                'd' => '', 'dd' => '%d Deeg', 'M' => '', 'MM' => '%d Méint', 'y' => '', 'yy' => '%d Joer',
            ),
            'dayOfMonthOrdinalParse' => '~\d{1,2}\.~u',
            'ordinal' => '%d.',
            'week' => array('dow' => 1, 'doy' => 4),
        );
    }

    public function calendar($key, $mom = null, $now = null)
    {
        if ($key === 'lastWeek') {
            $d = $mom->day();

            return ($d === 2 || $d === 4) ? '[Leschten] dddd [um] LT' : '[Leschte] dddd [um] LT';
        }

        return parent::calendar($key, $mom, $now);
    }

    public function relativeTime($number, $withoutSuffix, $key, $isFuture)
    {
        if (isset(self::$words[$key])) {
            return self::$words[$key][$withoutSuffix ? 0 : 1];
        }

        return parent::relativeTime($number, $withoutSuffix, $key, $isFuture);
    }

    public function pastFuture($diff, $output)
    {
        $pos = strpos($output, ' ');
        $number = $pos === false ? '' : substr($output, 0, $pos);
        $applies = self::eifelerRegelAppliesToNumber($number);
        if ($diff > 0) {
            return ($applies ? 'a ' : 'an ') . $output;
        }

        return ($applies ? 'viru ' : 'virun ') . $output;
    }

    protected static function eifelerRegelAppliesToNumber($number)
    {
        if (is_string($number)) {
            if (!preg_match('/^\s*([+-]?\d+)/', $number, $mm)) {
                return false;
            }
            $number = (float) $mm[1];
        } else {
            $number = (float) (int) $number; // parseInt truncation
        }
        if ($number < 0) {
            return true;
        } elseif ($number < 10) {
            return $number >= 4 && $number <= 7;
        } elseif ($number < 100) {
            $lastDigit = fmod($number, 10);
            $firstDigit = $number / 10;
            if ($lastDigit == 0) {
                return self::eifelerRegelAppliesToNumber($firstDigit);
            }

            return self::eifelerRegelAppliesToNumber($lastDigit);
        } elseif ($number < 10000) {
            while ($number >= 10) {
                $number = $number / 10;
            }

            return self::eifelerRegelAppliesToNumber($number);
        }

        return self::eifelerRegelAppliesToNumber($number / 1000);
    }
}
