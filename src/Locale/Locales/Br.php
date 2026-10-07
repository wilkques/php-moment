<?php

namespace Wilkques\Moment\Locale\Locales;

use Wilkques\Moment\Locale\Locale;

class Br extends Locale
{
    public $abbr = 'br';

    public static function config()
    {
        $monthsParse = array(
            '~^gen~iu', "~^c[ʼ']hwe~iu", '~^meu~iu', '~^ebr~iu', '~^mae~iu', '~^(mez|eve)~iu',
            '~^gou~iu', '~^eos~iu', '~^gwe~iu', '~^her~iu', '~^du~iu', '~^ker~iu',
        );
        $monthsRegex = "~^(genver|c[ʼ']hwevrer|meurzh|ebrel|mae|mezheven|gouere|eost|gwengolo|here|du|kerzu|gen|c[ʼ']hwe|meu|ebr|mae|eve|gou|eos|gwe|her|du|ker)~iu";
        $minWeekdaysParse = array('~^Su~iu', '~^Lu~iu', '~^Me([^r]|$)~iu', '~^Mer~iu', '~^Ya~iu', '~^Gw~iu', '~^Sa~iu');

        return array(
            'months' => explode('_', 'Genver_Cʼhwevrer_Meurzh_Ebrel_Mae_Mezheven_Gouere_Eost_Gwengolo_Here_Du_Kerzu'),
            'monthsShort' => explode('_', 'Gen_Cʼhwe_Meu_Ebr_Mae_Eve_Gou_Eos_Gwe_Her_Du_Ker'),
            'weekdays' => explode('_', 'Sul_Lun_Meurzh_Mercʼher_Yaou_Gwener_Sadorn'),
            'weekdaysShort' => explode('_', 'Sul_Lun_Meu_Mer_Yao_Gwe_Sad'),
            'weekdaysMin' => explode('_', 'Su_Lu_Me_Mer_Ya_Gw_Sa'),
            'weekdaysParse' => $minWeekdaysParse,
            'fullWeekdaysParse' => array('~^sul~iu', '~^lun~iu', '~^meurzh~iu', "~^merc[ʼ']her~iu", '~^yaou~iu', '~^gwener~iu', '~^sadorn~iu'),
            'shortWeekdaysParse' => array('~^Sul~iu', '~^Lun~iu', '~^Meu~iu', '~^Mer~iu', '~^Yao~iu', '~^Gwe~iu', '~^Sad~iu'),
            'minWeekdaysParse' => $minWeekdaysParse,
            'monthsRegex' => $monthsRegex,
            'monthsShortRegex' => $monthsRegex,
            'monthsStrictRegex' => "~^(genver|c[ʼ']hwevrer|meurzh|ebrel|mae|mezheven|gouere|eost|gwengolo|here|du|kerzu)~iu",
            'monthsShortStrictRegex' => "~^(gen|c[ʼ']hwe|meu|ebr|mae|eve|gou|eos|gwe|her|du|ker)~iu",
            'monthsParse' => $monthsParse,
            'longMonthsParse' => $monthsParse,
            'shortMonthsParse' => $monthsParse,
            'longDateFormat' => array(
                'LT' => 'HH:mm',
                'LTS' => 'HH:mm:ss',
                'L' => 'DD/MM/YYYY',
                'LL' => 'D [a viz] MMMM YYYY',
                'LLL' => 'D [a viz] MMMM YYYY HH:mm',
                'LLLL' => 'dddd, D [a viz] MMMM YYYY HH:mm',
            ),
            'calendar' => array(
                'sameDay' => '[Hiziv da] LT',
                'nextDay' => '[Warcʼhoazh da] LT',
                'nextWeek' => 'dddd [da] LT',
                'lastDay' => '[Decʼh da] LT',
                'lastWeek' => 'dddd [paset da] LT',
                'sameElse' => 'L',
            ),
            'relativeTime' => array(
                'future' => 'a-benn %s',
                'past' => '%s ʼzo',
                's' => 'un nebeud segondennoù',
                'ss' => '%d eilenn',
                'm' => 'ur vunutenn',
                'h' => 'un eur',
                'hh' => '%d eur',
                'd' => 'un devezh',
                'M' => 'ur miz',
                'y' => 'ur bloaz',
            ),
            'dayOfMonthOrdinalParse' => '~\d{1,2}(añ|vet)~u',
            'week' => array('dow' => 1, 'doy' => 4),
            'meridiemParse' => '~a.m.|g.m.~u',
        );
    }

    public function relativeTime($number, $withoutSuffix, $key, $isFuture)
    {
        switch ($key) {
            case 'mm':
            case 'dd':
            case 'MM':
                $format = array('mm' => 'munutenn', 'MM' => 'miz', 'dd' => 'devezh');

                return $number . ' ' . $this->mutation($format[$key], $number);
            case 'yy':
                switch ($this->lastNumber($number)) {
                    case 1:
                    case 3:
                    case 4:
                    case 5:
                    case 9:
                        return $number . ' bloaz';
                    default:
                        return $number . ' vloaz';
                }
        }

        return parent::relativeTime($number, $withoutSuffix, $key, $isFuture);
    }

    protected function lastNumber($number)
    {
        if ($number > 9) {
            return $this->lastNumber($number % 10);
        }

        return $number;
    }

    protected function mutation($text, $number)
    {
        if ($number === 2) {
            $table = array('m' => 'v', 'b' => 'v', 'd' => 'z');
            $c = substr($text, 0, 1);
            if (isset($table[$c])) {
                return $table[$c] . substr($text, 1);
            }
        }

        return $text;
    }

    public function ordinal($number, $token = '')
    {
        return $number . ($number === 1 ? 'añ' : 'vet');
    }

    public function isPM($token)
    {
        return $token === 'g.m.';
    }

    public function meridiem($hour, $minute, $isLower)
    {
        return $hour < 12 ? 'a.m.' : 'g.m.';
    }
}
