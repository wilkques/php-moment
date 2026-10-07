<?php

namespace Wilkques\Moment\Locale\Locales;

use Wilkques\Moment\Locale\Locale;

class Tzl extends Locale
{
    public $abbr = 'tzl';

    public static function config()
    {
        return array(
            'months' => array('Januar', 'Fevraglh', 'Març', 'Avrïu', 'Mai', 'Gün', 'Julia', 'Guscht', 'Setemvar', 'Listopäts', 'Noemvar', 'Zecemvar'),
            'monthsShort' => array('Jan', 'Fev', 'Mar', 'Avr', 'Mai', 'Gün', 'Jul', 'Gus', 'Set', 'Lis', 'Noe', 'Zec'),
            'weekdays' => array('Súladi', 'Lúneçi', 'Maitzi', 'Márcuri', 'Xhúadi', 'Viénerçi', 'Sáturi'),
            'weekdaysShort' => array('Súl', 'Lún', 'Mai', 'Már', 'Xhú', 'Vié', 'Sát'),
            'weekdaysMin' => array('Sú', 'Lú', 'Ma', 'Má', 'Xh', 'Vi', 'Sá'),
            'longDateFormat' => array(
                'LT' => 'HH.mm', 'LTS' => 'HH.mm.ss', 'L' => 'DD.MM.YYYY', 'LL' => 'D. MMMM [dallas] YYYY',
                'LLL' => 'D. MMMM [dallas] YYYY HH.mm', 'LLLL' => 'dddd, [li] D. MMMM [dallas] YYYY HH.mm',
            ),
            'meridiemParse' => "~d'o|d'a~iu",
            'calendar' => array(
                'sameDay' => '[oxhi à] LT', 'nextDay' => '[demà à] LT', 'nextWeek' => 'dddd [à] LT',
                'lastDay' => '[ieiri à] LT', 'lastWeek' => '[sür el] dddd [lasteu à] LT', 'sameElse' => 'L',
            ),
            'relativeTime' => array('future' => 'osprei %s', 'past' => 'ja%s'),
            'dayOfMonthOrdinalParse' => '~\d{1,2}\.~u',
            'ordinal' => '%d.',
            'week' => array('dow' => 1, 'doy' => 4),
        );
    }

    public function isPM($input)
    {
        return "d'o" === strtolower($input);
    }

    public function meridiem($hours, $minutes, $isLower)
    {
        if ($hours > 11) {
            return $isLower ? "d'o" : "D'O";
        }

        return $isLower ? "d'a" : "D'A";
    }

    public function relativeTime($number, $withoutSuffix, $key, $isFuture)
    {
        if ($key === 'w' || $key === 'ww') {
            return parent::relativeTime($number, $withoutSuffix, $key, $isFuture); // not set in the JS locale: moment's defaults
        }
        $n = '' . $number;
        $format = array(
            's' => array('viensas secunds', "'iensas secunds"),
            'ss' => array($n . ' secunds', $n . ' secunds'),
            'm' => array("'n míut", "'iens míut"),
            'mm' => array($n . ' míuts', $n . ' míuts'),
            'h' => array("'n þora", "'iensa þora"),
            'hh' => array($n . ' þoras', $n . ' þoras'),
            'd' => array("'n ziua", "'iensa ziua"),
            'dd' => array($n . ' ziuas', $n . ' ziuas'),
            'M' => array("'n mes", "'iens mes"),
            'MM' => array($n . ' mesen', $n . ' mesen'),
            'y' => array("'n ar", "'iens ar"),
            'yy' => array($n . ' ars', $n . ' ars'),
        );

        return ($isFuture || $withoutSuffix) ? $format[$key][0] : $format[$key][1];
    }
}
