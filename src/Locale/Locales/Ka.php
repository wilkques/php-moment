<?php

namespace Wilkques\Moment\Locale\Locales;

use Wilkques\Moment\Locale\Locale;

class Ka extends Locale
{
    public $abbr = 'ka';

    public static function config()
    {
        return array(
            'months' => explode('_', 'იანვარი_თებერვალი_მარტი_აპრილი_მაისი_ივნისი_ივლისი_აგვისტო_სექტემბერი_ოქტომბერი_ნოემბერი_დეკემბერი'),
            'monthsShort' => explode('_', 'იან_თებ_მარ_აპრ_მაი_ივნ_ივლ_აგვ_სექ_ოქტ_ნოე_დეკ'),
            'weekdays' => array(
                'standalone' => explode('_', 'კვირა_ორშაბათი_სამშაბათი_ოთხშაბათი_ხუთშაბათი_პარასკევი_შაბათი'),
                'format' => explode('_', 'კვირას_ორშაბათს_სამშაბათს_ოთხშაბათს_ხუთშაბათს_პარასკევს_შაბათს'),
                'isFormat' => '~(წინა|შემდეგ)~u',
            ),
            'weekdaysShort' => explode('_', 'კვი_ორშ_სამ_ოთხ_ხუთ_პარ_შაბ'),
            'weekdaysMin' => explode('_', 'კვ_ორ_სა_ოთ_ხუ_პა_შა'),
            'longDateFormat' => array(
                'LT' => 'HH:mm', 'LTS' => 'HH:mm:ss', 'L' => 'DD/MM/YYYY', 'LL' => 'D MMMM YYYY',
                'LLL' => 'D MMMM YYYY HH:mm', 'LLLL' => 'dddd, D MMMM YYYY HH:mm',
            ),
            'calendar' => array(
                'sameDay' => '[დღეს] LT[-ზე]', 'nextDay' => '[ხვალ] LT[-ზე]', 'lastDay' => '[გუშინ] LT[-ზე]',
                'nextWeek' => '[შემდეგ] dddd LT[-ზე]', 'lastWeek' => '[წინა] dddd LT-ზე', 'sameElse' => 'L',
            ),
            'relativeTime' => array(
                's' => 'რამდენიმე წამი', 'ss' => '%d წამი', 'm' => 'წუთი', 'mm' => '%d წუთი',
                'h' => 'საათი', 'hh' => '%d საათი', 'd' => 'დღე', 'dd' => '%d დღე',
                'M' => 'თვე', 'MM' => '%d თვე', 'y' => 'წელი', 'yy' => '%d წელი',
            ),
            'dayOfMonthOrdinalParse' => '~0|1-ლი|მე-\d{1,2}|\d{1,2}-ე~u',
            'week' => array('dow' => 1, 'doy' => 7),
        );
    }

    public function pastFuture($diff, $output)
    {
        if ($diff > 0) {
            return preg_replace_callback('~(წამ|წუთ|საათ|წელ|დღ|თვ)(ი|ე)~u', array(__CLASS__, 'futureCb'), $output, 1);
        }
        if (preg_match('~(წამი|წუთი|საათი|დღე|თვე)~u', $output)) {
            return preg_replace('~(ი|ე)$~u', 'ის წინ', $output, 1);
        }
        if (preg_match('~წელი~u', $output)) {
            return preg_replace('~წელი$~u', 'წლის წინ', $output, 1);
        }

        return $output;
    }

    public static function futureCb($m)
    {
        return $m[2] === 'ი' ? $m[1] . 'ში' : $m[1] . $m[2] . 'ში';
    }

    public function ordinal($number, $token = '')
    {
        if ($number === 0) {
            return $number;
        }
        if ($number === 1) {
            return $number . '-ლი';
        }
        if ($number < 20 || ($number <= 100 && $number % 20 === 0) || $number % 100 === 0) {
            return 'მე-' . $number;
        }

        return $number . '-ე';
    }

    /** Old PCRE lacks case folding for Mtavruli (U+1C90..U+1CBF): fold to Mkhedruli first. */
    public function monthsParse($monthName, $format = null, $strict = false)
    {
        return parent::monthsParse(self::foldMtavruli($monthName), $format, $strict);
    }

    public function weekdaysParse($weekdayName, $format = null, $strict = false)
    {
        return parent::weekdaysParse(self::foldMtavruli($weekdayName), $format, $strict);
    }

    protected static function foldMtavruli($s)
    {
        return preg_replace_callback('~[\x{1C90}-\x{1CBF}]~u', array(__CLASS__, 'foldCb'), $s);
    }

    public static function foldCb($m)
    {
        $cp = 0x10D0 + (self::ord8($m[0]) - 0x1C90);

        return self::chr8($cp);
    }

    private static function ord8($c)
    {
        $b = unpack('C*', $c);

        return (($b[1] & 0x0F) << 12) | (($b[2] & 0x3F) << 6) | ($b[3] & 0x3F);
    }

    private static function chr8($cp)
    {
        return chr(0xE0 | ($cp >> 12)) . chr(0x80 | (($cp >> 6) & 0x3F)) . chr(0x80 | ($cp & 0x3F));
    }
}
