<?php

namespace Wilkques\Moment\Locale\Locales;

use Wilkques\Moment\Locale\Locale;

class Gl extends Locale
{
    public $abbr = 'gl';

    public static function config()
    {
        return array(
            'months' => explode('_', 'xaneiro_febreiro_marzo_abril_maio_xuño_xullo_agosto_setembro_outubro_novembro_decembro'),
            'monthsShort' => explode('_', 'xan._feb._mar._abr._mai._xuñ._xul._ago._set._out._nov._dec.'),
            'monthsParseExact' => true,
            'weekdays' => explode('_', 'domingo_luns_martes_mércores_xoves_venres_sábado'),
            'weekdaysShort' => explode('_', 'dom._lun._mar._mér._xov._ven._sáb.'),
            'weekdaysMin' => explode('_', 'do_lu_ma_mé_xo_ve_sá'),
            'weekdaysParseExact' => true,
            'longDateFormat' => array(
                'LT' => 'H:mm', 'LTS' => 'H:mm:ss', 'L' => 'DD/MM/YYYY', 'LL' => 'D [de] MMMM [de] YYYY',
                'LLL' => 'D [de] MMMM [de] YYYY H:mm', 'LLLL' => 'dddd, D [de] MMMM [de] YYYY H:mm',
            ),
            'relativeTime' => array(
                'past' => 'hai %s', 's' => 'uns segundos', 'ss' => '%d segundos',
                'm' => 'un minuto', 'mm' => '%d minutos', 'h' => 'unha hora', 'hh' => '%d horas',
                'd' => 'un día', 'dd' => '%d días', 'M' => 'un mes', 'MM' => '%d meses',
                'y' => 'un ano', 'yy' => '%d anos',
            ),
            'dayOfMonthOrdinalParse' => '~\d{1,2}º~u',
            'ordinal' => '%dº',
            'week' => array('dow' => 1, 'doy' => 4),
        );
    }

    public function calendar($key, $mom = null, $now = null)
    {
        $one = $mom ? $mom->hour() !== 1 : true;
        switch ($key) {
            case 'sameDay':
                return '[hoxe ' . ($one ? 'ás' : 'á') . '] LT';
            case 'nextDay':
                return '[mañá ' . ($one ? 'ás' : 'á') . '] LT';
            case 'nextWeek':
                return 'dddd [' . ($one ? 'ás' : 'a') . '] LT';
            case 'lastDay':
                return '[onte ' . ($one ? 'á' : 'a') . '] LT';
            case 'lastWeek':
                return '[o] dddd [pasado ' . ($one ? 'ás' : 'a') . '] LT';
        }

        return parent::calendar($key, $mom, $now);
    }

    public function pastFuture($diff, $output)
    {
        if ($diff > 0) {
            return strpos($output, 'un') === 0 ? 'n' . $output : 'en ' . $output;
        }

        return parent::pastFuture($diff, $output);
    }
}
