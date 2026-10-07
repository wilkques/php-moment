<?php

namespace Wilkques\Moment\Locale\Locales;

use Wilkques\Moment\Locale\Locale;

class Sk extends Locale
{
    public $abbr = 'sk';

    public static function config()
    {
        return array(
            'months' => array('január', 'február', 'marec', 'apríl', 'máj', 'jún', 'júl', 'august', 'september', 'október', 'november', 'december'),
            'monthsShort' => array('jan', 'feb', 'mar', 'apr', 'máj', 'jún', 'júl', 'aug', 'sep', 'okt', 'nov', 'dec'),
            'weekdays' => array('nedeľa', 'pondelok', 'utorok', 'streda', 'štvrtok', 'piatok', 'sobota'),
            'weekdaysShort' => array('ne', 'po', 'ut', 'st', 'št', 'pi', 'so'),
            'weekdaysMin' => array('ne', 'po', 'ut', 'st', 'št', 'pi', 'so'),
            'longDateFormat' => array(
                'LT' => 'H:mm', 'LTS' => 'H:mm:ss', 'L' => 'DD.MM.YYYY',
                'LL' => 'D. MMMM YYYY', 'LLL' => 'D. MMMM YYYY H:mm', 'LLLL' => 'dddd D. MMMM YYYY H:mm',
            ),
            'calendar' => array(
                'sameDay' => '[dnes o] LT', 'nextDay' => '[zajtra o] LT', 'lastDay' => '[včera o] LT', 'sameElse' => 'L',
            ),
            'relativeTime' => array('future' => 'za %s', 'past' => 'pred %s'),
            'dayOfMonthOrdinalParse' => '~\d{1,2}\.~u',
            'ordinal' => '%d.',
            'week' => array('dow' => 1, 'doy' => 4),
        );
    }

    protected static function plural($n)
    {
        return $n > 1 && $n < 5;
    }

    public function relativeTime($number, $withoutSuffix, $key, $isFuture)
    {
        $result = $number . ' ';
        $short = $withoutSuffix || $isFuture;
        switch ($key) {
            case 's':
                return $short ? 'pár sekúnd' : 'pár sekundami';
            case 'ss':
                return $short ? $result . (self::plural($number) ? 'sekundy' : 'sekúnd') : $result . 'sekundami';
            case 'm':
                return $withoutSuffix ? 'minúta' : ($isFuture ? 'minútu' : 'minútou');
            case 'mm':
                return $short ? $result . (self::plural($number) ? 'minúty' : 'minút') : $result . 'minútami';
            case 'h':
                return $withoutSuffix ? 'hodina' : ($isFuture ? 'hodinu' : 'hodinou');
            case 'hh':
                return $short ? $result . (self::plural($number) ? 'hodiny' : 'hodín') : $result . 'hodinami';
            case 'd':
                return $short ? 'deň' : 'dňom';
            case 'dd':
                return $short ? $result . (self::plural($number) ? 'dni' : 'dní') : $result . 'dňami';
            case 'M':
                return $short ? 'mesiac' : 'mesiacom';
            case 'MM':
                return $short ? $result . (self::plural($number) ? 'mesiace' : 'mesiacov') : $result . 'mesiacmi';
            case 'y':
                return $short ? 'rok' : 'rokom';
            case 'yy':
                return $short ? $result . (self::plural($number) ? 'roky' : 'rokov') : $result . 'rokmi';
        }

        return parent::relativeTime($number, $withoutSuffix, $key, $isFuture);
    }

    public function calendar($key, $mom = null, $now = null)
    {
        if ($key === 'nextWeek') {
            switch ($mom->day()) {
                case 0:
                    return '[v nedeľu o] LT';
                case 1:
                case 2:
                    return '[v] dddd [o] LT';
                case 3:
                    return '[v stredu o] LT';
                case 4:
                    return '[vo štvrtok o] LT';
                case 5:
                    return '[v piatok o] LT';
                case 6:
                    return '[v sobotu o] LT';
            }
        } elseif ($key === 'lastWeek') {
            switch ($mom->day()) {
                case 0:
                    return '[minulú nedeľu o] LT';
                case 1:
                case 2:
                case 4:
                case 5:
                    return '[minulý] dddd [o] LT';
                case 3:
                    return '[minulú stredu o] LT';
                case 6:
                    return '[minulú sobotu o] LT';
            }
        }

        return parent::calendar($key, $mom, $now);
    }
}
