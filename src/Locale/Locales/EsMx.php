<?php

namespace Wilkques\Moment\Locale\Locales;

use Wilkques\Moment\Locale\Locale;

class EsMx extends Locale
{
    public $abbr = 'es-mx';

    private static $monthsShortDot = array('ene.', 'feb.', 'mar.', 'abr.', 'may.', 'jun.', 'jul.', 'ago.', 'sep.', 'oct.', 'nov.', 'dic.');
    private static $monthsShortNoDot = array('ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic');

    public static function config()
    {
        $monthsParse = array('~^ene~iu', '~^feb~iu', '~^mar~iu', '~^abr~iu', '~^may~iu', '~^jun~iu', '~^jul~iu', '~^ago~iu', '~^sep~iu', '~^oct~iu', '~^nov~iu', '~^dic~iu');
        $monthsRegex = '~^(enero|febrero|marzo|abril|mayo|junio|julio|agosto|septiembre|octubre|noviembre|diciembre|ene\.?|feb\.?|mar\.?|abr\.?|may\.?|jun\.?|jul\.?|ago\.?|sep\.?|oct\.?|nov\.?|dic\.?)~iu';

        return array(
            'months' => explode('_', 'enero_febrero_marzo_abril_mayo_junio_julio_agosto_septiembre_octubre_noviembre_diciembre'),
            'monthsRegex' => $monthsRegex,
            'monthsShortRegex' => $monthsRegex,
            'monthsStrictRegex' => '~^(enero|febrero|marzo|abril|mayo|junio|julio|agosto|septiembre|octubre|noviembre|diciembre)~iu',
            'monthsShortStrictRegex' => '~^(ene\.?|feb\.?|mar\.?|abr\.?|may\.?|jun\.?|jul\.?|ago\.?|sep\.?|oct\.?|nov\.?|dic\.?)~iu',
            'monthsParse' => $monthsParse,
            'longMonthsParse' => $monthsParse,
            'shortMonthsParse' => $monthsParse,
            'weekdays' => explode('_', 'domingo_lunes_martes_miércoles_jueves_viernes_sábado'),
            'weekdaysShort' => explode('_', 'dom._lun._mar._mié._jue._vie._sáb.'),
            'weekdaysMin' => explode('_', 'do_lu_ma_mi_ju_vi_sá'),
            'weekdaysParseExact' => true,
            'longDateFormat' => array(
                'LT' => 'H:mm', 'LTS' => 'H:mm:ss', 'L' => 'DD/MM/YYYY', 'LL' => 'D [de] MMMM [de] YYYY',
                'LLL' => 'D [de] MMMM [de] YYYY H:mm', 'LLLL' => 'dddd, D [de] MMMM [de] YYYY H:mm',
            ),
            'calendar' => array('sameElse' => 'L'),
            'relativeTime' => array(
                'future' => 'en %s', 'past' => 'hace %s', 's' => 'unos segundos', 'ss' => '%d segundos',
                'm' => 'un minuto', 'mm' => '%d minutos', 'h' => 'una hora', 'hh' => '%d horas',
                'd' => 'un día', 'dd' => '%d días', 'w' => 'una semana', 'ww' => '%d semanas',
                'M' => 'un mes', 'MM' => '%d meses', 'y' => 'un año', 'yy' => '%d años',
            ),
            'dayOfMonthOrdinalParse' => '~\d{1,2}º~u',
            'ordinal' => '%dº',
            'week' => array('dow' => 0, 'doy' => 4),
            'invalidDate' => 'Fecha inválida',
        );
    }

    public function monthsShort($m = null, $format = '')
    {
        if (!$m && $m !== 0) {
            return self::$monthsShortDot;
        } elseif (preg_match('~-MMM-~', (string) $format)) {
            return self::$monthsShortNoDot[self::monthIndex($m)];
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
