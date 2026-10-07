<?php

namespace Wilkques\Moment\Locale\Locales;

use Wilkques\Moment\Locale\Locale;

class KuKmr extends Locale
{
    public $abbr = 'ku-kmr';

    public static function config()
    {
        return array(
            'months' => array('Rêbendan', 'Sibat', 'Adar', 'Nîsan', 'Gulan', 'Hezîran', 'Tîrmeh', 'Tebax', 'Îlon', 'Cotmeh', 'Mijdar', 'Berfanbar'),
            'monthsShort' => array('Rêb', 'Sib', 'Ada', 'Nîs', 'Gul', 'Hez', 'Tîr', 'Teb', 'Îlo', 'Cot', 'Mij', 'Ber'),
            'monthsParseExact' => true,
            'weekdays' => array('Yekşem', 'Duşem', 'Sêşem', 'Çarşem', 'Pêncşem', 'În', 'Şemî'),
            'weekdaysShort' => array('Yek', 'Du', 'Sê', 'Çar', 'Pên', 'În', 'Şem'),
            'weekdaysMin' => array('Ye', 'Du', 'Sê', 'Ça', 'Pê', 'În', 'Şe'),
            'meridiemParse' => '~bn|BN|pn|PN~u',
            'longDateFormat' => array(
                'LT' => 'HH:mm', 'LTS' => 'HH:mm:ss', 'L' => 'DD.MM.YYYY', 'LL' => 'Do MMMM[a] YYYY[an]',
                'LLL' => 'Do MMMM[a] YYYY[an] HH:mm', 'LLLL' => 'dddd, Do MMMM[a] YYYY[an] HH:mm',
                'll' => 'Do MMM[.] YYYY[an]', 'lll' => 'Do MMM[.] YYYY[an] HH:mm', 'llll' => 'ddd[.], Do MMM[.] YYYY[an] HH:mm',
            ),
            'calendar' => array(
                'sameDay' => '[Îro di saet] LT [de]', 'nextDay' => '[Sibê di saet] LT [de]',
                'nextWeek' => 'dddd [di saet] LT [de]', 'lastDay' => '[Duh di saet] LT [de]',
                'lastWeek' => 'dddd[a borî di saet] LT [de]', 'sameElse' => 'L',
            ),
            'relativeTime' => array('future' => 'di %s de', 'past' => 'berî %s'),
            'dayOfMonthOrdinalParse' => '~\d{1,2}(?:yê|ê|\.)~u',
            'week' => array('dow' => 1, 'doy' => 4),
        );
    }

    public function meridiem($hours, $minutes, $isLower)
    {
        if ($hours < 12) {
            return $isLower ? 'bn' : 'BN';
        }

        return $isLower ? 'pn' : 'PN';
    }

    public function relativeTime($number, $withoutSuffix, $key, $isFuture)
    {
        $format = array(
            's' => array('çend sanîye', 'çend sanîyeyan'),
            'ss' => array($number . ' sanîye', $number . ' sanîyeyan'),
            'm' => array('deqîqeyek', 'deqîqeyekê'),
            'mm' => array($number . ' deqîqe', $number . ' deqîqeyan'),
            'h' => array('saetek', 'saetekê'),
            'hh' => array($number . ' saet', $number . ' saetan'),
            'd' => array('rojek', 'rojekê'),
            'dd' => array($number . ' roj', $number . ' rojan'),
            'w' => array('hefteyek', 'hefteyekê'),
            'ww' => array($number . ' hefte', $number . ' hefteyan'),
            'M' => array('mehek', 'mehekê'),
            'MM' => array($number . ' meh', $number . ' mehan'),
            'y' => array('salek', 'salekê'),
            'yy' => array($number . ' sal', $number . ' salan'),
        );

        return $format[$key][$withoutSuffix ? 0 : 1];
    }

    protected function ezafeNumSuffix($num)
    {
        $num = '' . $num;
        $l = substr($num, -1);
        $ll = strlen($num) > 1 ? substr($num, -2) : '';
        if (!($ll === '12' || $ll === '13') && ($l === '2' || $l === '3' || $ll === '50' || $l === '70' || $l === '80')) {
            return 'yê';
        }

        return 'ê';
    }

    public function ordinal($number, $token = '')
    {
        $p = strtolower($token);
        if (strpos($p, 'w') !== false || strpos($p, 'm') !== false) {
            return $number . '.';
        }

        return $number . $this->ezafeNumSuffix($number);
    }
}
