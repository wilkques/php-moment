<?php

namespace Wilkques\Moment\Locale\Locales;

use Wilkques\Moment\Locale\Locale;

class EsUs extends Locale
{
    public $abbr = 'es-us';

    public static function config()
    {
        $monthsParse = array(
            '~^ene~iu', '~^feb~iu', '~^mar~iu', '~^abr~iu', '~^may~iu', '~^jun~iu',
            '~^jul~iu', '~^ago~iu', '~^sep~iu', '~^oct~iu', '~^nov~iu', '~^dic~iu',
        );
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
                'LT' => 'h:mm A', 'LTS' => 'h:mm:ss A', 'L' => 'MM/DD/YYYY', 'LL' => 'D [de] MMMM [de] YYYY',
                'LLL' => 'D [de] MMMM [de] YYYY h:mm A', 'LLLL' => 'dddd, D [de] MMMM [de] YYYY h:mm A',
            ),
            'relativeTime' => array(
                'future' => 'en %s', 'past' => 'hace %s', 's' => 'unos segundos', 'ss' => '%d segundos',
                'm' => 'un minuto', 'mm' => '%d minutos', 'h' => 'una hora', 'hh' => '%d horas',
                'd' => 'un día', 'dd' => '%d días', 'w' => 'una semana', 'ww' => '%d semanas',
                'M' => 'un mes', 'MM' => '%d meses', 'y' => 'un año', 'yy' => '%d años',
            ),
            'dayOfMonthOrdinalParse' => '~\d{1,2}º~u',
            'ordinal' => '%dº',
            'week' => array('dow' => 0, 'doy' => 6),
        );
    }

    public function monthsShort($m = null, $format = '')
    {
        $dot = explode('_', 'ene._feb._mar._abr._may._jun._jul._ago._sep._oct._nov._dic.');
        if ($m === null) {
            return $dot;
        } elseif (preg_match('~-MMM-~u', (string) $format)) {
            $plain = explode('_', 'ene_feb_mar_abr_may_jun_jul_ago_sep_oct_nov_dic');

            return $plain[self::monthIndex($m)];
        }

        return $dot[self::monthIndex($m)];
    }

    public function calendar($key, $mom = null, $now = null)
    {
        $a = $mom->hour() !== 1 ? 's' : '';
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
