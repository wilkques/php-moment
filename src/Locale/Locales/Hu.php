<?php

namespace Wilkques\Moment\Locale\Locales;

use Wilkques\Moment\Locale\Locale;

class Hu extends Locale
{
    public $abbr = 'hu';

    public static function config()
    {
        return array(
            'months' => explode('_', 'január_február_március_április_május_június_július_augusztus_szeptember_október_november_december'),
            'monthsShort' => explode('_', 'jan._feb._márc._ápr._máj._jún._júl._aug._szept._okt._nov._dec.'),
            'monthsParseExact' => true,
            'weekdays' => explode('_', 'vasárnap_hétfő_kedd_szerda_csütörtök_péntek_szombat'),
            'weekdaysShort' => explode('_', 'vas_hét_kedd_sze_csüt_pén_szo'),
            'weekdaysMin' => explode('_', 'v_h_k_sze_cs_p_szo'),
            'longDateFormat' => array(
                'LT' => 'H:mm', 'LTS' => 'H:mm:ss', 'L' => 'YYYY.MM.DD.', 'LL' => 'YYYY. MMMM D.',
                'LLL' => 'YYYY. MMMM D. H:mm', 'LLLL' => 'YYYY. MMMM D., dddd H:mm',
            ),
            'meridiemParse' => '~de|du~iu',
            'calendar' => array(
                'sameDay' => '[ma] LT[-kor]', 'nextDay' => '[holnap] LT[-kor]',
                'lastDay' => '[tegnap] LT[-kor]', 'sameElse' => 'L',
            ),
            'relativeTime' => array('future' => '%s múlva', 'past' => '%s'),
            'dayOfMonthOrdinalParse' => '~\d{1,2}\.~u',
            'ordinal' => '%d.',
            'week' => array('dow' => 1, 'doy' => 4),
        );
    }

    public function isPM($input)
    {
        return strtolower(substr($input . '', 1, 1)) === 'u';
    }

    public function meridiem($hours, $minutes, $isLower)
    {
        if ($hours < 12) {
            return $isLower === true ? 'de' : 'DE';
        }

        return $isLower === true ? 'du' : 'DU';
    }

    protected function weekCal($mom, $isFuture)
    {
        $endings = explode(' ', 'vasárnap hétfőn kedden szerdán csütörtökön pénteken szombaton');

        return ($isFuture ? '' : '[múlt] ') . '[' . $endings[$mom->day()] . '] LT[-kor]';
    }

    public function calendar($key, $mom = null, $now = null)
    {
        if ($key === 'nextWeek') {
            return $this->weekCal($mom, true);
        } elseif ($key === 'lastWeek') {
            return $this->weekCal($mom, false);
        }

        return parent::calendar($key, $mom, $now);
    }

    public function relativeTime($number, $withoutSuffix, $key, $isFuture)
    {
        if ($key === 'w' || $key === 'ww') {
            return parent::relativeTime($number, $withoutSuffix, $key, $isFuture); // not set in the JS locale: moment's defaults
        }
        $x = $isFuture || $withoutSuffix;
        switch ($key) {
            case 's':
                return $x ? 'néhány másodperc' : 'néhány másodperce';
            case 'ss':
                // JS precedence quirk: (num + bool) ? ... : ...
                return ($number + ($x ? 1 : 0)) ? ' másodperc' : ' másodperce';
            case 'm':
                return 'egy' . ($x ? ' perc' : ' perce');
            case 'mm':
                return $number . ($x ? ' perc' : ' perce');
            case 'h':
                return 'egy' . ($x ? ' óra' : ' órája');
            case 'hh':
                return $number . ($x ? ' óra' : ' órája');
            case 'd':
                return 'egy' . ($x ? ' nap' : ' napja');
            case 'dd':
                return $number . ($x ? ' nap' : ' napja');
            case 'M':
                return 'egy' . ($x ? ' hónap' : ' hónapja');
            case 'MM':
                return $number . ($x ? ' hónap' : ' hónapja');
            case 'y':
                return 'egy' . ($x ? ' év' : ' éve');
            case 'yy':
                return $number . ($x ? ' év' : ' éve');
        }

        return '';
    }
}
