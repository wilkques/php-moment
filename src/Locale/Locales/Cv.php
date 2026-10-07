<?php

namespace Wilkques\Moment\Locale\Locales;

use Wilkques\Moment\Locale\Locale;

class Cv extends Locale
{
    public $abbr = 'cv';

    public static function config()
    {
        return array(
            'months' => array('кӑрлач', 'нарӑс', 'пуш', 'ака', 'май', 'ҫӗртме', 'утӑ', 'ҫурла', 'авӑн', 'юпа', 'чӳк', 'раштав'),
            'monthsShort' => array('кӑр', 'нар', 'пуш', 'ака', 'май', 'ҫӗр', 'утӑ', 'ҫур', 'авн', 'юпа', 'чӳк', 'раш'),
            'weekdays' => array('вырсарникун', 'тунтикун', 'ытларикун', 'юнкун', 'кӗҫнерникун', 'эрнекун', 'шӑматкун'),
            'weekdaysShort' => array('выр', 'тун', 'ытл', 'юн', 'кӗҫ', 'эрн', 'шӑм'),
            'weekdaysMin' => array('вр', 'тн', 'ыт', 'юн', 'кҫ', 'эр', 'шм'),
            'longDateFormat' => array(
                'LT' => 'HH:mm', 'LTS' => 'HH:mm:ss', 'L' => 'DD-MM-YYYY',
                'LL' => 'YYYY [ҫулхи] MMMM [уйӑхӗн] D[-мӗшӗ]',
                'LLL' => 'YYYY [ҫулхи] MMMM [уйӑхӗн] D[-мӗшӗ], HH:mm',
                'LLLL' => 'dddd, YYYY [ҫулхи] MMMM [уйӑхӗн] D[-мӗшӗ], HH:mm',
            ),
            'calendar' => array(
                'sameDay' => '[Паян] LT [сехетре]', 'nextDay' => '[Ыран] LT [сехетре]', 'lastDay' => '[Ӗнер] LT [сехетре]',
                'nextWeek' => '[Ҫитес] dddd LT [сехетре]', 'lastWeek' => '[Иртнӗ] dddd LT [сехетре]', 'sameElse' => 'L',
            ),
            'relativeTime' => array(
                'future' => '', 'past' => '%s каялла', 's' => 'пӗр-ик ҫеккунт', 'ss' => '%d ҫеккунт',
                'm' => 'пӗр минут', 'mm' => '%d минут', 'h' => 'пӗр сехет', 'hh' => '%d сехет',
                'd' => 'пӗр кун', 'dd' => '%d кун', 'M' => 'пӗр уйӑх', 'MM' => '%d уйӑх', 'y' => 'пӗр ҫул', 'yy' => '%d ҫул',
            ),
            'dayOfMonthOrdinalParse' => '~\d{1,2}-мӗш~u',
            'ordinal' => '%d-мӗш',
            'week' => array('dow' => 1, 'doy' => 7),
        );
    }

    public function pastFuture($diff, $output)
    {
        if ($diff > 0) {
            $affix = preg_match('~сехет$~iu', $output) ? 'рен' : (preg_match('~ҫул$~iu', $output) ? 'тан' : 'ран');

            return $output . $affix;
        }

        return parent::pastFuture($diff, $output);
    }
}
