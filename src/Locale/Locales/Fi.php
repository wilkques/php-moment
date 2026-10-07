<?php

namespace Wilkques\Moment\Locale\Locales;

use Wilkques\Moment\Locale\Locale;

class Fi extends Locale
{
    public $abbr = 'fi';

    public static function config()
    {
        return array(
            'months' => explode('_', 'tammikuu_helmikuu_maaliskuu_huhtikuu_toukokuu_kesäkuu_heinäkuu_elokuu_syyskuu_lokakuu_marraskuu_joulukuu'),
            'monthsShort' => explode('_', 'tammi_helmi_maalis_huhti_touko_kesä_heinä_elo_syys_loka_marras_joulu'),
            'weekdays' => explode('_', 'sunnuntai_maanantai_tiistai_keskiviikko_torstai_perjantai_lauantai'),
            'weekdaysShort' => explode('_', 'su_ma_ti_ke_to_pe_la'),
            'weekdaysMin' => explode('_', 'su_ma_ti_ke_to_pe_la'),
            'longDateFormat' => array(
                'LT' => 'HH.mm',
                'LTS' => 'HH.mm.ss',
                'L' => 'DD.MM.YYYY',
                'LL' => 'Do MMMM[ta] YYYY',
                'LLL' => 'Do MMMM[ta] YYYY, [klo] HH.mm',
                'LLLL' => 'dddd, Do MMMM[ta] YYYY, [klo] HH.mm',
                'l' => 'D.M.YYYY',
                'll' => 'Do MMM YYYY',
                'lll' => 'Do MMM YYYY, [klo] HH.mm',
                'llll' => 'ddd, Do MMM YYYY, [klo] HH.mm',
            ),
            'calendar' => array(
                'sameDay' => '[tänään] [klo] LT',
                'nextDay' => '[huomenna] [klo] LT',
                'nextWeek' => 'dddd [klo] LT',
                'lastDay' => '[eilen] [klo] LT',
                'lastWeek' => '[viime] dddd[na] [klo] LT',
                'sameElse' => 'L',
            ),
            'relativeTime' => array('future' => '%s päästä', 'past' => '%s sitten'),
            'dayOfMonthOrdinalParse' => '~\d{1,2}\.~u',
            'ordinal' => '%d.',
            'week' => array('dow' => 1, 'doy' => 4),
        );
    }

    protected static $numbersPast = array('nolla', 'yksi', 'kaksi', 'kolme', 'neljä', 'viisi', 'kuusi', 'seitsemän', 'kahdeksan', 'yhdeksän');
    protected static $numbersFuture = array('nolla', 'yhden', 'kahden', 'kolmen', 'neljän', 'viiden', 'kuuden', 'seitsemän', 'kahdeksan', 'yhdeksän');

    public function relativeTime($number, $withoutSuffix, $key, $isFuture)
    {
        if ($key === 'w' || $key === 'ww') {
            return parent::relativeTime($number, $withoutSuffix, $key, $isFuture); // not set in the JS locale: moment's defaults
        }
        $result = '';
        switch ($key) {
            case 's':
                return $isFuture ? 'muutaman sekunnin' : 'muutama sekunti';
            case 'ss':
                $result = $isFuture ? 'sekunnin' : 'sekuntia';
                break;
            case 'm':
                return $isFuture ? 'minuutin' : 'minuutti';
            case 'mm':
                $result = $isFuture ? 'minuutin' : 'minuuttia';
                break;
            case 'h':
                return $isFuture ? 'tunnin' : 'tunti';
            case 'hh':
                $result = $isFuture ? 'tunnin' : 'tuntia';
                break;
            case 'd':
                return $isFuture ? 'päivän' : 'päivä';
            case 'dd':
                $result = $isFuture ? 'päivän' : 'päivää';
                break;
            case 'M':
                return $isFuture ? 'kuukauden' : 'kuukausi';
            case 'MM':
                $result = $isFuture ? 'kuukauden' : 'kuukautta';
                break;
            case 'y':
                return $isFuture ? 'vuoden' : 'vuosi';
            case 'yy':
                $result = $isFuture ? 'vuoden' : 'vuotta';
                break;
        }

        return $this->verbalNumber($number, $isFuture) . ' ' . $result;
    }

    protected function verbalNumber($number, $isFuture)
    {
        if ($number < 10) {
            return $isFuture ? self::$numbersFuture[$number] : self::$numbersPast[$number];
        }

        return $number;
    }
}
