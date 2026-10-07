<?php

namespace Wilkques\Moment\Locale\Locales;

use Wilkques\Moment\Locale\Locale;

class Et extends Locale
{
    public $abbr = 'et';

    public static function config()
    {
        return array(
            'months' => array('jaanuar', 'veebruar', 'märts', 'aprill', 'mai', 'juuni', 'juuli', 'august', 'september', 'oktoober', 'november', 'detsember'),
            'monthsShort' => array('jaan', 'veebr', 'märts', 'apr', 'mai', 'juuni', 'juuli', 'aug', 'sept', 'okt', 'nov', 'dets'),
            'weekdays' => array('pühapäev', 'esmaspäev', 'teisipäev', 'kolmapäev', 'neljapäev', 'reede', 'laupäev'),
            'weekdaysShort' => array('P', 'E', 'T', 'K', 'N', 'R', 'L'),
            'weekdaysMin' => array('P', 'E', 'T', 'K', 'N', 'R', 'L'),
            'longDateFormat' => array(
                'LT' => 'H:mm', 'LTS' => 'H:mm:ss', 'L' => 'DD.MM.YYYY',
                'LL' => 'D. MMMM YYYY', 'LLL' => 'D. MMMM YYYY H:mm', 'LLLL' => 'dddd, D. MMMM YYYY H:mm',
            ),
            'calendar' => array(
                'sameDay' => '[Täna,] LT', 'nextDay' => '[Homme,] LT', 'nextWeek' => '[Järgmine] dddd LT',
                'lastDay' => '[Eile,] LT', 'lastWeek' => '[Eelmine] dddd LT', 'sameElse' => 'L',
            ),
            'relativeTime' => array(
                'future' => '%s pärast', 'past' => '%s tagasi', 'dd' => '%d päeva',
            ),
            'dayOfMonthOrdinalParse' => '~\d{1,2}\.~u',
            'ordinal' => '%d.',
            'week' => array('dow' => 1, 'doy' => 4),
        );
    }

    public function relativeTime($number, $withoutSuffix, $key, $isFuture)
    {
        $format = array(
            's' => array('mõne sekundi', 'mõni sekund', 'paar sekundit'),
            'ss' => array($number . 'sekundi', $number . 'sekundit'),
            'm' => array('ühe minuti', 'üks minut'),
            'mm' => array($number . ' minuti', $number . ' minutit'),
            'h' => array('ühe tunni', 'tund aega', 'üks tund'),
            'hh' => array($number . ' tunni', $number . ' tundi'),
            'd' => array('ühe päeva', 'üks päev'),
            'M' => array('kuu aja', 'kuu aega', 'üks kuu'),
            'MM' => array($number . ' kuu', $number . ' kuud'),
            'y' => array('ühe aasta', 'aasta', 'üks aasta'),
            'yy' => array($number . ' aasta', $number . ' aastat'),
        );
        if (!isset($format[$key])) {
            return parent::relativeTime($number, $withoutSuffix, $key, $isFuture);
        }
        $f = $format[$key];
        if ($withoutSuffix) {
            return isset($f[2]) && $f[2] ? $f[2] : $f[1];
        }

        return $isFuture ? $f[0] : $f[1];
    }
}
