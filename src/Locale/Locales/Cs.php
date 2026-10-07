<?php

namespace Wilkques\Moment\Locale\Locales;

use Wilkques\Moment\Locale\Locale;

class Cs extends Locale
{
    public $abbr = 'cs';

    public static function config()
    {
        $monthsParse = array(
            '~^led~iu', '~^úno~iu', '~^bře~iu', '~^dub~iu', '~^kvě~iu', '~^(čvn|červen$|června)~iu',
            '~^(čvc|červenec|července)~iu', '~^srp~iu', '~^zář~iu', '~^říj~iu', '~^lis~iu', '~^pro~iu',
        );
        $monthsRegex = '~^(leden|únor|březen|duben|květen|červenec|července|červen|června|srpen|září|říjen|listopad|prosinec|led|úno|bře|dub|kvě|čvn|čvc|srp|zář|říj|lis|pro)~iu';

        return array(
            'months' => array(
                'standalone' => explode('_', 'leden_únor_březen_duben_květen_červen_červenec_srpen_září_říjen_listopad_prosinec'),
                'format' => explode('_', 'ledna_února_března_dubna_května_června_července_srpna_září_října_listopadu_prosince'),
                'isFormat' => '~DD?[o.]?(\[[^\[\]]*\]|\s)+MMMM~u',
            ),
            'monthsShort' => explode('_', 'led_úno_bře_dub_kvě_čvn_čvc_srp_zář_říj_lis_pro'),
            'monthsRegex' => $monthsRegex,
            'monthsShortRegex' => $monthsRegex,
            'monthsStrictRegex' => '~^(leden|ledna|února|únor|březen|března|duben|dubna|květen|května|červenec|července|červen|června|srpen|srpna|září|říjen|října|listopadu|listopad|prosinec|prosince)~iu',
            'monthsShortStrictRegex' => '~^(led|úno|bře|dub|kvě|čvn|čvc|srp|zář|říj|lis|pro)~iu',
            'monthsParse' => $monthsParse,
            'longMonthsParse' => $monthsParse,
            'shortMonthsParse' => $monthsParse,
            'weekdays' => explode('_', 'neděle_pondělí_úterý_středa_čtvrtek_pátek_sobota'),
            'weekdaysShort' => explode('_', 'ne_po_út_st_čt_pá_so'),
            'weekdaysMin' => explode('_', 'ne_po_út_st_čt_pá_so'),
            'longDateFormat' => array(
                'LT' => 'H:mm', 'LTS' => 'H:mm:ss', 'L' => 'DD.MM.YYYY', 'LL' => 'D. MMMM YYYY',
                'LLL' => 'D. MMMM YYYY H:mm', 'LLLL' => 'dddd D. MMMM YYYY H:mm', 'l' => 'D. M. YYYY',
            ),
            'calendar' => array(
                'sameDay' => '[dnes v] LT', 'nextDay' => '[zítra v] LT', 'lastDay' => '[včera v] LT', 'sameElse' => 'L',
            ),
            'relativeTime' => array('future' => 'za %s', 'past' => 'před %s'),
            'dayOfMonthOrdinalParse' => '~\d{1,2}\.~u',
            'ordinal' => '%d.',
            'week' => array('dow' => 1, 'doy' => 4),
        );
    }

    public function calendar($key, $mom = null, $now = null)
    {
        if ($key === 'nextWeek') {
            switch ($mom->day()) {
                case 0:
                    return '[v neděli v] LT';
                case 1:
                case 2:
                    return '[v] dddd [v] LT';
                case 3:
                    return '[ve středu v] LT';
                case 4:
                    return '[ve čtvrtek v] LT';
                case 5:
                    return '[v pátek v] LT';
                case 6:
                    return '[v sobotu v] LT';
            }
        } elseif ($key === 'lastWeek') {
            switch ($mom->day()) {
                case 0:
                    return '[minulou neděli v] LT';
                case 1:
                case 2:
                    return '[minulé] dddd [v] LT';
                case 3:
                    return '[minulou středu v] LT';
                case 4:
                case 5:
                    return '[minulý] dddd [v] LT';
                case 6:
                    return '[minulou sobotu v] LT';
            }
        }

        return parent::calendar($key, $mom, $now);
    }

    protected static function plural($n)
    {
        return $n > 1 && $n < 5 && (int) ($n / 10) !== 1;
    }

    public function relativeTime($number, $withoutSuffix, $key, $isFuture)
    {
        if ($key === 'w' || $key === 'ww') {
            return parent::relativeTime($number, $withoutSuffix, $key, $isFuture); // not set in the JS locale: moment's defaults
        }
        if ($key === 'w' || $key === 'ww') {
            return null;
        }
        $result = $number . ' ';
        $sf = $withoutSuffix || $isFuture;
        switch ($key) {
            case 's':
                return $sf ? 'pár sekund' : 'pár sekundami';
            case 'ss':
                return $sf ? $result . (self::plural($number) ? 'sekundy' : 'sekund') : $result . 'sekundami';
            case 'm':
                return $withoutSuffix ? 'minuta' : ($isFuture ? 'minutu' : 'minutou');
            case 'mm':
                return $sf ? $result . (self::plural($number) ? 'minuty' : 'minut') : $result . 'minutami';
            case 'h':
                return $withoutSuffix ? 'hodina' : ($isFuture ? 'hodinu' : 'hodinou');
            case 'hh':
                return $sf ? $result . (self::plural($number) ? 'hodiny' : 'hodin') : $result . 'hodinami';
            case 'd':
                return $sf ? 'den' : 'dnem';
            case 'dd':
                return $sf ? $result . (self::plural($number) ? 'dny' : 'dní') : $result . 'dny';
            case 'M':
                return $sf ? 'měsíc' : 'měsícem';
            case 'MM':
                return $sf ? $result . (self::plural($number) ? 'měsíce' : 'měsíců') : $result . 'měsíci';
            case 'y':
                return $sf ? 'rok' : 'rokem';
            case 'yy':
                return $sf ? $result . (self::plural($number) ? 'roky' : 'let') : $result . 'lety';
        }

        return null;
    }
}
