<?php

namespace Wilkques\Moment\Locale\Locales;

use Wilkques\Moment\Locale\Locale;

class HyAm extends Locale
{
    public $abbr = 'hy-am';

    public static function config()
    {
        return array(
            'months' => array(
                'format' => array('հունվարի', 'փետրվարի', 'մարտի', 'ապրիլի', 'մայիսի', 'հունիսի', 'հուլիսի', 'օգոստոսի', 'սեպտեմբերի', 'հոկտեմբերի', 'նոյեմբերի', 'դեկտեմբերի'),
                'standalone' => array('հունվար', 'փետրվար', 'մարտ', 'ապրիլ', 'մայիս', 'հունիս', 'հուլիս', 'օգոստոս', 'սեպտեմբեր', 'հոկտեմբեր', 'նոյեմբեր', 'դեկտեմբեր'),
            ),
            'monthsShort' => array('հնվ', 'փտր', 'մրտ', 'ապր', 'մյս', 'հնս', 'հլս', 'օգս', 'սպտ', 'հկտ', 'նմբ', 'դկտ'),
            'weekdays' => array('կիրակի', 'երկուշաբթի', 'երեքշաբթի', 'չորեքշաբթի', 'հինգշաբթի', 'ուրբաթ', 'շաբաթ'),
            'weekdaysShort' => array('կրկ', 'երկ', 'երք', 'չրք', 'հնգ', 'ուրբ', 'շբթ'),
            'weekdaysMin' => array('կրկ', 'երկ', 'երք', 'չրք', 'հնգ', 'ուրբ', 'շբթ'),
            'longDateFormat' => array(
                'LT' => 'HH:mm', 'LTS' => 'HH:mm:ss', 'L' => 'DD.MM.YYYY',
                'LL' => 'D MMMM YYYY թ.', 'LLL' => 'D MMMM YYYY թ., HH:mm', 'LLLL' => 'dddd, D MMMM YYYY թ., HH:mm',
            ),
            'calendar' => array(
                'sameDay' => '[այսօր] LT', 'nextDay' => '[վաղը] LT', 'lastDay' => '[երեկ] LT',
                'nextWeek' => 'dddd [օրը ժամը] LT', 'lastWeek' => '[անցած] dddd [օրը ժամը] LT', 'sameElse' => 'L',
            ),
            'relativeTime' => array(
                'future' => '%s հետո', 'past' => '%s առաջ',
                's' => 'մի քանի վայրկյան', 'ss' => '%d վայրկյան', 'm' => 'րոպե', 'mm' => '%d րոպե',
                'h' => 'ժամ', 'hh' => '%d ժամ', 'd' => 'օր', 'dd' => '%d օր',
                'M' => 'ամիս', 'MM' => '%d ամիս', 'y' => 'տարի', 'yy' => '%d տարի',
            ),
            'meridiemParse' => '~գիշերվա|առավոտվա|ցերեկվա|երեկոյան~u',
            'dayOfMonthOrdinalParse' => '~\d{1,2}|\d{1,2}-(ին|րդ)~u',
            'week' => array('dow' => 1, 'doy' => 7),
        );
    }

    public function isPM($input)
    {
        return $input === 'ցերեկվա' || $input === 'երեկոյան';
    }

    public function meridiem($hour, $minute = 0, $isLower = false)
    {
        if ($hour < 4) {
            return 'գիշերվա';
        } elseif ($hour < 12) {
            return 'առավոտվա';
        } elseif ($hour < 17) {
            return 'ցերեկվա';
        }

        return 'երեկոյան';
    }

    public function ordinal($number, $token = '')
    {
        switch ($token) {
            case 'DDD':
            case 'w':
            case 'W':
            case 'DDDo':
                return $number . ($number === 1 ? '-ին' : '-րդ');
            default:
                return $number;
        }
    }
}
