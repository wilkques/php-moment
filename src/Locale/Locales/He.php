<?php

namespace Wilkques\Moment\Locale\Locales;

use Wilkques\Moment\Locale\Locale;

class He extends Locale
{
    public $abbr = 'he';

    public static function config()
    {
        return array(
            'months' => explode('_', 'ינואר_פברואר_מרץ_אפריל_מאי_יוני_יולי_אוגוסט_ספטמבר_אוקטובר_נובמבר_דצמבר'),
            'monthsShort' => explode('_', 'ינו׳_פבר׳_מרץ_אפר׳_מאי_יוני_יולי_אוג׳_ספט׳_אוק׳_נוב׳_דצמ׳'),
            'weekdays' => explode('_', 'ראשון_שני_שלישי_רביעי_חמישי_שישי_שבת'),
            'weekdaysShort' => explode('_', 'א׳_ב׳_ג׳_ד׳_ה׳_ו׳_ש׳'),
            'weekdaysMin' => explode('_', 'א_ב_ג_ד_ה_ו_ש'),
            'longDateFormat' => array(
                'LT' => 'HH:mm', 'LTS' => 'HH:mm:ss', 'L' => 'DD/MM/YYYY', 'LL' => 'D [ב]MMMM YYYY',
                'LLL' => 'D [ב]MMMM YYYY HH:mm', 'LLLL' => 'dddd, D [ב]MMMM YYYY HH:mm',
                'l' => 'D/M/YYYY', 'll' => 'D MMM YYYY', 'lll' => 'D MMM YYYY HH:mm', 'llll' => 'ddd, D MMM YYYY HH:mm',
            ),
            'calendar' => array(
                'sameDay' => '[היום ב־]LT', 'nextDay' => '[מחר ב־]LT', 'nextWeek' => 'dddd [בשעה] LT',
                'lastDay' => '[אתמול ב־]LT', 'lastWeek' => '[ביום] dddd [האחרון בשעה] LT', 'sameElse' => 'L',
            ),
            'relativeTime' => array(
                'future' => 'בעוד %s', 'past' => 'לפני %s', 's' => 'מספר שניות', 'ss' => '%d שניות',
                'm' => 'דקה', 'mm' => '%d דקות', 'h' => 'שעה', 'd' => 'יום', 'M' => 'חודש', 'y' => 'שנה',
            ),
            'meridiemParse' => '~אחה"צ|לפנה"צ|אחרי הצהריים|לפני הצהריים|לפנות בוקר|בבוקר|בערב~iu',
        );
    }

    public function relativeTime($number, $withoutSuffix, $key, $isFuture)
    {
        switch ($key) {
            case 'hh':
                return $number === 2 ? 'שעתיים' : $number . ' שעות';
            case 'dd':
                return $number === 2 ? 'יומיים' : $number . ' ימים';
            case 'MM':
                return $number === 2 ? 'חודשיים' : $number . ' חודשים';
            case 'yy':
                if ($number === 2) {
                    return 'שנתיים';
                } elseif ($number % 10 === 0 && $number !== 10) {
                    return $number . ' שנה';
                }

                return $number . ' שנים';
        }

        return parent::relativeTime($number, $withoutSuffix, $key, $isFuture);
    }

    public function isPM($input)
    {
        return (bool) preg_match('~^(אחה"צ|אחרי הצהריים|בערב)$~u', $input);
    }

    public function meridiem($hour, $minute, $isLower)
    {
        if ($hour < 5) {
            return 'לפנות בוקר';
        } elseif ($hour < 10) {
            return 'בבוקר';
        } elseif ($hour < 12) {
            return $isLower ? 'לפנה"צ' : 'לפני הצהריים';
        } elseif ($hour < 18) {
            return $isLower ? 'אחה"צ' : 'אחרי הצהריים';
        }

        return 'בערב';
    }
}
