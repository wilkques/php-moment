<?php

namespace Wilkques\Moment\Locale\Locales;

use Wilkques\Moment\Locale\Locale;

class Tlh extends Locale
{
    public $abbr = 'tlh';

    private static $numbersNouns = array('pagh', 'wa’', 'cha’', 'wej', 'loS', 'vagh', 'jav', 'Soch', 'chorgh', 'Hut');

    public static function config()
    {
        return array(
            'months' => array('tera’ jar wa’', 'tera’ jar cha’', 'tera’ jar wej', 'tera’ jar loS', 'tera’ jar vagh', 'tera’ jar jav', 'tera’ jar Soch', 'tera’ jar chorgh', 'tera’ jar Hut', 'tera’ jar wa’maH', 'tera’ jar wa’maH wa’', 'tera’ jar wa’maH cha’'),
            'monthsShort' => array('jar wa’', 'jar cha’', 'jar wej', 'jar loS', 'jar vagh', 'jar jav', 'jar Soch', 'jar chorgh', 'jar Hut', 'jar wa’maH', 'jar wa’maH wa’', 'jar wa’maH cha’'),
            'monthsParseExact' => true,
            'weekdays' => array('lojmItjaj', 'DaSjaj', 'povjaj', 'ghItlhjaj', 'loghjaj', 'buqjaj', 'ghInjaj'),
            'weekdaysShort' => array('lojmItjaj', 'DaSjaj', 'povjaj', 'ghItlhjaj', 'loghjaj', 'buqjaj', 'ghInjaj'),
            'weekdaysMin' => array('lojmItjaj', 'DaSjaj', 'povjaj', 'ghItlhjaj', 'loghjaj', 'buqjaj', 'ghInjaj'),
            'longDateFormat' => array(
                'LT' => 'HH:mm', 'LTS' => 'HH:mm:ss', 'L' => 'DD.MM.YYYY', 'LL' => 'D MMMM YYYY',
                'LLL' => 'D MMMM YYYY HH:mm', 'LLLL' => 'dddd, D MMMM YYYY HH:mm',
            ),
            'calendar' => array(
                'sameDay' => '[DaHjaj] LT', 'nextDay' => '[wa’leS] LT', 'nextWeek' => 'LLL',
                'lastDay' => '[wa’Hu’] LT', 'lastWeek' => 'LLL', 'sameElse' => 'L',
            ),
            'relativeTime' => array(
                'future' => '', 'past' => '', 's' => 'puS lup', 'ss' => '', 'm' => 'wa’ tup', 'mm' => '',
                'h' => 'wa’ rep', 'hh' => '', 'd' => 'wa’ jaj', 'dd' => '', 'M' => 'wa’ jar', 'MM' => '',
                'y' => 'wa’ DIS', 'yy' => '',
            ),
            'dayOfMonthOrdinalParse' => '~\d{1,2}\.~u',
            'ordinal' => '%d.',
            'week' => array('dow' => 1, 'doy' => 4),
        );
    }

    protected static function numberAsNoun($number)
    {
        $hundred = (int) floor(($number % 1000) / 100);
        $ten = (int) floor(($number % 100) / 10);
        $one = $number % 10;
        $word = '';
        if ($hundred > 0) {
            $word .= self::$numbersNouns[$hundred] . 'vatlh';
        }
        if ($ten > 0) {
            $word .= ($word !== '' ? ' ' : '') . self::$numbersNouns[$ten] . 'maH';
        }
        if ($one > 0) {
            $word .= ($word !== '' ? ' ' : '') . self::$numbersNouns[$one];
        }

        return $word === '' ? 'pagh' : $word;
    }

    public function relativeTime($number, $withoutSuffix, $key, $isFuture)
    {
        $suffix = array('ss' => ' lup', 'mm' => ' tup', 'hh' => ' rep', 'dd' => ' jaj', 'MM' => ' jar', 'yy' => ' DIS');
        if (isset($suffix[$key])) {
            return self::numberAsNoun($number) . $suffix[$key];
        }

        return parent::relativeTime($number, $withoutSuffix, $key, $isFuture);
    }

    public function pastFuture($diff, $output)
    {
        $stem = substr($output, 0, -3);
        if ($diff > 0) {
            if (strpos($output, 'jaj') !== false) {
                return $stem . 'leS';
            } elseif (strpos($output, 'jar') !== false) {
                return $stem . 'waQ';
            } elseif (strpos($output, 'DIS') !== false) {
                return $stem . 'nem';
            }

            return $output . ' pIq';
        }
        if (strpos($output, 'jaj') !== false) {
            return $stem . 'Hu’';
        } elseif (strpos($output, 'jar') !== false) {
            return $stem . 'wen';
        } elseif (strpos($output, 'DIS') !== false) {
            return $stem . 'ben';
        }

        return $output . ' ret';
    }
}
