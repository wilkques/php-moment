<?php

namespace Wilkques\Moment\Locale\Locales;

use Wilkques\Moment\Locale\Locale;

class Sd extends Locale
{
    public $abbr = 'sd';

    public static function config()
    {
        $months = array('جنوري', 'فيبروري', 'مارچ', 'اپريل', 'مئي', 'جون', 'جولاءِ', 'آگسٽ', 'سيپٽمبر', 'آڪٽوبر', 'نومبر', 'ڊسمبر');
        $days = array('آچر', 'سومر', 'اڱارو', 'اربع', 'خميس', 'جمع', 'ڇنڇر');

        return array(
            'months' => $months,
            'monthsShort' => $months,
            'weekdays' => $days,
            'weekdaysShort' => $days,
            'weekdaysMin' => $days,
            'longDateFormat' => array(
                'LT' => 'HH:mm', 'LTS' => 'HH:mm:ss', 'L' => 'DD/MM/YYYY', 'LL' => 'D MMMM YYYY',
                'LLL' => 'D MMMM YYYY HH:mm', 'LLLL' => 'dddd، D MMMM YYYY HH:mm',
            ),
            'meridiemParse' => '~صبح|شام~u',
            'calendar' => array(
                'sameDay' => '[اڄ] LT', 'nextDay' => '[سڀاڻي] LT', 'nextWeek' => 'dddd [اڳين هفتي تي] LT',
                'lastDay' => '[ڪالهه] LT', 'lastWeek' => '[گزريل هفتي] dddd [تي] LT', 'sameElse' => 'L',
            ),
            'relativeTime' => array(
                'future' => '%s پوء', 'past' => '%s اڳ',
                's' => 'چند سيڪنڊ', 'ss' => '%d سيڪنڊ',
                'm' => 'هڪ منٽ', 'mm' => '%d منٽ',
                'h' => 'هڪ ڪلاڪ', 'hh' => '%d ڪلاڪ',
                'd' => 'هڪ ڏينهن', 'dd' => '%d ڏينهن',
                'M' => 'هڪ مهينو', 'MM' => '%d مهينا',
                'y' => 'هڪ سال', 'yy' => '%d سال',
            ),
            'week' => array('dow' => 1, 'doy' => 4),
        );
    }

    public function isPM($input)
    {
        return 'شام' === $input;
    }

    public function meridiem($hour, $minute, $isLower)
    {
        return $hour < 12 ? 'صبح' : 'شام';
    }

    public function preparse($string)
    {
        return str_replace('،', ',', $string);
    }

    public function postformat($string)
    {
        return str_replace(',', '،', $string);
    }
}
