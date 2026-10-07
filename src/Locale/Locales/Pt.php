<?php

namespace Wilkques\Moment\Locale\Locales;

use Wilkques\Moment\Locale\Locale;

class Pt extends Locale
{
    public $abbr = 'pt';

    public static function config()
    {
        return array(
            'months' => array('janeiro', 'fevereiro', 'março', 'abril', 'maio', 'junho', 'julho', 'agosto', 'setembro', 'outubro', 'novembro', 'dezembro'),
            'monthsShort' => array('jan', 'fev', 'mar', 'abr', 'mai', 'jun', 'jul', 'ago', 'set', 'out', 'nov', 'dez'),
            'weekdays' => array('Domingo', 'Segunda-feira', 'Terça-feira', 'Quarta-feira', 'Quinta-feira', 'Sexta-feira', 'Sábado'),
            'weekdaysShort' => array('Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'),
            'weekdaysMin' => array('Do', '2ª', '3ª', '4ª', '5ª', '6ª', 'Sá'),
            'weekdaysParseExact' => true,
            'longDateFormat' => array(
                'LT' => 'HH:mm', 'LTS' => 'HH:mm:ss', 'L' => 'DD/MM/YYYY',
                'LL' => 'D [de] MMMM [de] YYYY', 'LLL' => 'D [de] MMMM [de] YYYY HH:mm',
                'LLLL' => 'dddd, D [de] MMMM [de] YYYY HH:mm',
            ),
            'calendar' => array(
                'sameDay' => '[Hoje às] LT', 'nextDay' => '[Amanhã às] LT', 'nextWeek' => 'dddd [às] LT',
                'lastDay' => '[Ontem às] LT', 'sameElse' => 'L',
            ),
            'relativeTime' => array(
                'future' => 'em %s', 'past' => 'há %s',
                's' => 'segundos', 'ss' => '%d segundos', 'm' => 'um minuto', 'mm' => '%d minutos',
                'h' => 'uma hora', 'hh' => '%d horas', 'd' => 'um dia', 'dd' => '%d dias',
                'w' => 'uma semana', 'ww' => '%d semanas',
                'M' => 'um mês', 'MM' => '%d meses', 'y' => 'um ano', 'yy' => '%d anos',
            ),
            'dayOfMonthOrdinalParse' => '~\d{1,2}º~u',
            'ordinal' => '%dº',
            'week' => array('dow' => 1, 'doy' => 4),
        );
    }

    public function calendar($key, $mom = null, $now = null)
    {
        if ($key === 'lastWeek') {
            $d = $mom->day();

            return ($d === 0 || $d === 6) ? '[Último] dddd [às] LT' : '[Última] dddd [às] LT';
        }

        return parent::calendar($key, $mom, $now);
    }
}
