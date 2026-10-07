<?php

namespace Wilkques\Moment\Locale\Locales;

use Wilkques\Moment\Locale\Locale;

class Ss extends Locale
{
    public $abbr = 'ss';

    public static function config()
    {
        return array(
            'calendar' => array(
                'sameDay' => '[Namuhla nga] LT',
                'nextDay' => '[Kusasa nga] LT',
                'nextWeek' => 'dddd [nga] LT',
                'lastDay' => '[Itolo nga] LT',
                'lastWeek' => 'dddd [leliphelile] [nga] LT',
                'sameElse' => 'L',
            ),
            'longDateFormat' => array(
                'LTS' => 'h:mm:ss A',
                'LT' => 'h:mm A',
                'L' => 'DD/MM/YYYY',
                'LL' => 'D MMMM YYYY',
                'LLL' => 'D MMMM YYYY h:mm A',
                'LLLL' => 'dddd, D MMMM YYYY h:mm A',
            ),
            'ordinal' => '%d',
            'dayOfMonthOrdinalParse' => '~\\d{1,2}~u',
            'relativeTime' => array(
                'future' => 'nga %s',
                'past' => 'wenteka nga %s',
                's' => 'emizuzwana lomcane',
                'ss' => '%d mzuzwana',
                'm' => 'umzuzu',
                'mm' => '%d emizuzu',
                'h' => 'lihora',
                'hh' => '%d emahora',
                'd' => 'lilanga',
                'dd' => '%d emalanga',
                'w' => 'a week',
                'ww' => '%d weeks',
                'M' => 'inyanga',
                'MM' => '%d tinyanga',
                'y' => 'umnyaka',
                'yy' => '%d iminyaka',
            ),
            'months' => array('Bhimbidvwane', 'Indlovana', 'Indlov\'lenkhulu', 'Mabasa', 'Inkhwekhweti', 'Inhlaba', 'Kholwane', 'Ingci', 'Inyoni', 'Imphala', 'Lweti', 'Ingongoni'),
            'monthsShort' => array('Bhi', 'Ina', 'Inu', 'Mab', 'Ink', 'Inh', 'Kho', 'Igc', 'Iny', 'Imp', 'Lwe', 'Igo'),
            'week' => array(
                'dow' => 1,
                'doy' => 4,
            ),
            'weekdays' => array('Lisontfo', 'Umsombuluko', 'Lesibili', 'Lesitsatfu', 'Lesine', 'Lesihlanu', 'Umgcibelo'),
            'weekdaysMin' => array('Li', 'Us', 'Lb', 'Lt', 'Ls', 'Lh', 'Ug'),
            'weekdaysShort' => array('Lis', 'Umb', 'Lsb', 'Les', 'Lsi', 'Lsh', 'Umg'),
            'meridiemParse' => '~ekuseni|emini|entsambama|ebusuku~u',
            'weekdaysParseExact' => true,
        );
    }

    public function meridiem($hours, $minutes, $isLower)
    {
        if ($hours < 11) {
            return 'ekuseni';
        } elseif ($hours < 15) {
            return 'emini';
        } elseif ($hours < 19) {
            return 'entsambama';
        }

        return 'ebusuku';
    }

    public function meridiemHour($hour, $meridiem)
    {
        if ($hour === 12) {
            $hour = 0;
        }
        if ($meridiem === 'ekuseni') {
            return $hour;
        } elseif ($meridiem === 'emini') {
            return $hour >= 11 ? $hour : $hour + 12;
        } elseif ($meridiem === 'entsambama' || $meridiem === 'ebusuku') {
            if ($hour === 0) {
                return 0;
            }

            return $hour + 12;
        }

        return null;
    }
}
