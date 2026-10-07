<?php

namespace Wilkques\Moment\Locale\Locales;

use Wilkques\Moment\Locale\Locale;

class PtBr extends Locale
{
    public $abbr = 'pt-br';

    public static function config()
    {
        return array(
            'months' => explode('_', 'janeiro_fevereiro_março_abril_maio_junho_julho_agosto_setembro_outubro_novembro_dezembro'),
            'monthsShort' => explode('_', 'jan_fev_mar_abr_mai_jun_jul_ago_set_out_nov_dez'),
            'weekdays' => explode('_', 'domingo_segunda-feira_terça-feira_quarta-feira_quinta-feira_sexta-feira_sábado'),
            'weekdaysShort' => explode('_', 'dom_seg_ter_qua_qui_sex_sáb'),
            'weekdaysMin' => explode('_', 'do_2ª_3ª_4ª_5ª_6ª_sá'),
            'weekdaysParseExact' => true,
            'longDateFormat' => array(
                'LT' => 'HH:mm', 'LTS' => 'HH:mm:ss', 'L' => 'DD/MM/YYYY', 'LL' => 'D [de] MMMM [de] YYYY',
                'LLL' => 'D [de] MMMM [de] YYYY [às] HH:mm', 'LLLL' => 'dddd, D [de] MMMM [de] YYYY [às] HH:mm',
            ),
            'calendar' => array(
                'sameDay' => '[Hoje às] LT', 'nextDay' => '[Amanhã às] LT', 'nextWeek' => 'dddd [às] LT',
                'lastDay' => '[Ontem às] LT', 'sameElse' => 'L',
            ),
            'relativeTime' => array(
                'future' => 'em %s', 'past' => 'há %s',
                's' => 'poucos segundos', 'ss' => '%d segundos',
                'm' => 'um minuto', 'mm' => '%d minutos',
                'h' => 'uma hora', 'hh' => '%d horas',
                'd' => 'um dia', 'dd' => '%d dias',
                'M' => 'um mês', 'MM' => '%d meses',
                'y' => 'um ano', 'yy' => '%d anos',
            ),
            'dayOfMonthOrdinalParse' => '~\d{1,2}º~u',
            'ordinal' => '%dº',
            'invalidDate' => 'Data inválida',
        );
    }

    public function calendar($key, $mom = null, $now = null)
    {
        if ($key === 'lastWeek') {
            $d = $mom->day();

            return $d === 0 || $d === 6 ? '[Último] dddd [às] LT' : '[Última] dddd [às] LT';
        }

        return parent::calendar($key, $mom, $now);
    }
}
