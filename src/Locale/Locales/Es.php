<?php

namespace Wilkques\Moment\Locale\Locales;

use Wilkques\Moment\Locale\Locale;

class Es extends Locale
{
    public $abbr = 'es';

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
                'LT' => 'H:mm', 'LTS' => 'H:mm:ss', 'L' => 'DD/MM/YYYY', 'LL' => 'D [de] MMMM [de] YYYY',
                'LLL' => 'D [de] MMMM [de] YYYY H:mm', 'LLLL' => 'dddd, D [de] MMMM [de] YYYY H:mm',
            ),
            'relativeTime' => array(
                'future' => 'en %s', 'past' => 'hace %s', 's' => 'unos segundos', 'ss' => '%d segundos',
                'm' => 'un minuto', 'mm' => '%d minutos', 'h' => 'una hora', 'hh' => '%d horas',
                'd' => 'un día', 'dd' => '%d días', 'w' => 'una semana', 'ww' => '%d semanas',
                'M' => 'un mes', 'MM' => '%d meses', 'y' => 'un año', 'yy' => '%d años',
            ),
            'dayOfMonthOrdinalParse' => '~\d{1,2}º~u',
            'ordinal' => '%dº',
            'week' => array('dow' => 1, 'doy' => 4),
            'invalidDate' => 'Fecha inválida',
        );
    }

    public function monthsShort($m = null, $format = '')
    {
        if ($m === null) {
            return self::$monthsShortDot;
        } elseif (preg_match('/-MMM-/', (string) $format)) {
            return self::$monthsShortPlain[self::monthIndex($m)];
        }

        return self::$monthsShortDot[self::monthIndex($m)];
    }

    public function calendar($key, $mom = null, $now = null)
    {
        $s = $mom->hour() !== 1 ? 's' : '';
        switch ($key) {
            case 'sameDay':
                return '[hoy a la' . $s . '] LT';
            case 'nextDay':
                return '[mañana a la' . $s . '] LT';
            case 'nextWeek':
                return 'dddd [a la' . $s . '] LT';
            case 'lastDay':
                return '[ayer a la' . $s . '] LT';
            case 'lastWeek':
                return '[el] dddd [pasado a la' . $s . '] LT';
        }

        return parent::calendar($key, $mom, $now);
    }
}
