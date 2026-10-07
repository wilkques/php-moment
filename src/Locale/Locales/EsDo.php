<?php

namespace Wilkques\Moment\Locale\Locales;

use Wilkques\Moment\Locale\Locale;

class EsDo extends Locale
{
    public $abbr = 'es-do';

    private static $monthsShortDot = array('ene.', 'feb.', 'mar.', 'abr.', 'may.', 'jun.', 'jul.', 'ago.', 'sep.', 'oct.', 'nov.', 'dic.');
    private static $monthsShortPlain = array('ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic');

    public static function config()
    {
        $monthsParse = array('~^ene~iu', '~^feb~iu', '~^mar~iu', '~^abr~iu', '~^may~iu', '~^jun~iu', '~^jul~iu', '~^ago~iu', '~^sep~iu', '~^oct~iu', '~^nov~iu', '~^dic~iu');
        $monthsRegex = '~^(enero|febrero|marzo|abril|mayo|junio|julio|agosto|septiembre|octubre|noviembre|diciembre|ene\.?|feb\.?|mar\.?|abr\.?|may\.?|jun\.?|jul\.?|ago\.?|sep\.?|oct\.?|nov\.?|dic\.?)~iu';

        return array(
            'months' => array('enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'),
            'monthsRegex' => $monthsRegex,
            'monthsShortRegex' => $monthsRegex,
            'monthsStrictRegex' => '~^(enero|febrero|marzo|abril|mayo|junio|julio|agosto|septiembre|octubre|noviembre|diciembre)~iu',
            'monthsShortStrictRegex' => '~^(ene\.?|feb\.?|mar\.?|abr\.?|may\.?|jun\.?|jul\.?|ago\.?|sep\.?|oct\.?|nov\.?|dic\.?)~iu',
            'monthsParse' => $monthsParse,
            'longMonthsParse' => $monthsParse,
            'shortMonthsParse' => $monthsParse,
            'weekdays' => array('domingo', 'lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado'),
            'weekdaysShort' => array('dom.', 'lun.', 'mar.', 'mié.', 'jue.', 'vie.', 'sáb.'),
            'weekdaysMin' => array('do', 'lu', 'ma', 'mi', 'ju', 'vi', 'sá'),
            'weekdaysParseExact' => true,
            'longDateFormat' => array(
                'LT' => 'h:mm A', 'LTS' => 'h:mm:ss A', 'L' => 'DD/MM/YYYY',
                'LL' => 'D [de] MMMM [de] YYYY', 'LLL' => 'D [de] MMMM [de] YYYY h:mm A',
                'LLLL' => 'dddd, D [de] MMMM [de] YYYY h:mm A',
            ),
            'relativeTime' => array(
                'future' => 'en %s', 'past' => 'hace %s',
                's' => 'unos segundos', 'ss' => '%d segundos', 'm' => 'un minuto', 'mm' => '%d minutos',
                'h' => 'una hora', 'hh' => '%d horas', 'd' => 'un día', 'dd' => '%d días',
                'w' => 'una semana', 'ww' => '%d semanas',
                'M' => 'un mes', 'MM' => '%d meses', 'y' => 'un año', 'yy' => '%d años',
            ),
            'dayOfMonthOrdinalParse' => '~\d{1,2}º~u',
            'ordinal' => '%dº',
            'week' => array('dow' => 1, 'doy' => 4),
        );
    }

    public function monthsShort($m = null, $format = '')
    {
        if ($m === null) {
            return self::$monthsShortDot;
        }
        $i = self::monthIndex($m);
        if (preg_match('~-MMM-~', (string) $format)) {
            return self::$monthsShortPlain[$i];
        }

        return self::$monthsShortDot[$i];
    }

    public function calendar($key, $mom = null, $now = null)
    {
        $a = ($mom !== null && $mom->hour() !== 1) ? 's' : '';
        switch ($key) {
            case 'sameDay':
                return '[hoy a la' . $a . '] LT';
            case 'nextDay':
                return '[mañana a la' . $a . '] LT';
            case 'nextWeek':
                return 'dddd [a la' . $a . '] LT';
            case 'lastDay':
                return '[ayer a la' . $a . '] LT';
            case 'lastWeek':
                return '[el] dddd [pasado a la' . $a . '] LT';
        }

        return parent::calendar($key, $mom, $now);
    }
}
