<?php

namespace Wilkques\Moment\Locale\Locales;

use Wilkques\Moment\Locale\Locale;

class Is extends Locale
{
    public $abbr = 'is';

    protected static function plural($n)
    {
        if ($n % 100 === 11) {
            return true;
        } elseif ($n % 10 === 1) {
            return false;
        }

        return true;
    }

    public static function config()
    {
        return array(
            'months' => array('janúar', 'febrúar', 'mars', 'apríl', 'maí', 'júní', 'júlí', 'ágúst', 'september', 'október', 'nóvember', 'desember'),
            'monthsShort' => array('jan', 'feb', 'mar', 'apr', 'maí', 'jún', 'júl', 'ágú', 'sep', 'okt', 'nóv', 'des'),
            'weekdays' => array('sunnudagur', 'mánudagur', 'þriðjudagur', 'miðvikudagur', 'fimmtudagur', 'föstudagur', 'laugardagur'),
            'weekdaysShort' => array('sun', 'mán', 'þri', 'mið', 'fim', 'fös', 'lau'),
            'weekdaysMin' => array('Su', 'Má', 'Þr', 'Mi', 'Fi', 'Fö', 'La'),
            'longDateFormat' => array(
                'LT' => 'H:mm', 'LTS' => 'H:mm:ss', 'L' => 'DD.MM.YYYY', 'LL' => 'D. MMMM YYYY',
                'LLL' => 'D. MMMM YYYY [kl.] H:mm', 'LLLL' => 'dddd, D. MMMM YYYY [kl.] H:mm',
            ),
            'calendar' => array(
                'sameDay' => '[í dag kl.] LT', 'nextDay' => '[á morgun kl.] LT', 'nextWeek' => 'dddd [kl.] LT',
                'lastDay' => '[í gær kl.] LT', 'lastWeek' => '[síðasta] dddd [kl.] LT', 'sameElse' => 'L',
            ),
            'relativeTime' => array('future' => 'eftir %s', 'past' => 'fyrir %s síðan', 'h' => 'klukkustund'),
            'dayOfMonthOrdinalParse' => '~\d{1,2}\.~u',
            'ordinal' => '%d.',
            'week' => array('dow' => 1, 'doy' => 4),
        );
    }

    public function relativeTime($number, $withoutSuffix, $key, $isFuture)
    {
        $result = $number . ' ';
        $fs = $withoutSuffix || $isFuture;
        switch ($key) {
            case 's':
                return $fs ? 'nokkrar sekúndur' : 'nokkrum sekúndum';
            case 'ss':
                if (self::plural($number)) {
                    return $result . ($fs ? 'sekúndur' : 'sekúndum');
                }

                return $result . 'sekúnda';
            case 'm':
                return $withoutSuffix ? 'mínúta' : 'mínútu';
            case 'mm':
                if (self::plural($number)) {
                    return $result . ($fs ? 'mínútur' : 'mínútum');
                } elseif ($withoutSuffix) {
                    return $result . 'mínúta';
                }

                return $result . 'mínútu';
            case 'hh':
                if (self::plural($number)) {
                    return $result . ($fs ? 'klukkustundir' : 'klukkustundum');
                }

                return $result . 'klukkustund';
            case 'd':
                if ($withoutSuffix) {
                    return 'dagur';
                }

                return $isFuture ? 'dag' : 'degi';
            case 'dd':
                if (self::plural($number)) {
                    if ($withoutSuffix) {
                        return $result . 'dagar';
                    }

                    return $result . ($isFuture ? 'daga' : 'dögum');
                } elseif ($withoutSuffix) {
                    return $result . 'dagur';
                }

                return $result . ($isFuture ? 'dag' : 'degi');
            case 'M':
                if ($withoutSuffix) {
                    return 'mánuður';
                }

                return $isFuture ? 'mánuð' : 'mánuði';
            case 'MM':
                if (self::plural($number)) {
                    if ($withoutSuffix) {
                        return $result . 'mánuðir';
                    }

                    return $result . ($isFuture ? 'mánuði' : 'mánuðum');
                } elseif ($withoutSuffix) {
                    return $result . 'mánuður';
                }

                return $result . ($isFuture ? 'mánuð' : 'mánuði');
            case 'y':
                return $fs ? 'ár' : 'ári';
            case 'yy':
                if (self::plural($number)) {
                    return $result . ($fs ? 'ár' : 'árum');
                }

                return $result . ($fs ? 'ár' : 'ári');
        }

        return parent::relativeTime($number, $withoutSuffix, $key, $isFuture);
    }
}
