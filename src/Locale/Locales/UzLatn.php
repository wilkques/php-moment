<?php

namespace Wilkques\Moment\Locale\Locales;

use Wilkques\Moment\Locale\Locale;

class UzLatn extends Locale
{
    public $abbr = 'uz-latn';

    public static function config()
    {
        return array(
            'months' => explode('_', 'Yanvar_Fevral_Mart_Aprel_May_Iyun_Iyul_Avgust_Sentabr_Oktabr_Noyabr_Dekabr'),
            'monthsShort' => explode('_', 'Yan_Fev_Mar_Apr_May_Iyun_Iyul_Avg_Sen_Okt_Noy_Dek'),
            'weekdays' => explode('_', 'Yakshanba_Dushanba_Seshanba_Chorshanba_Payshanba_Juma_Shanba'),
            'weekdaysShort' => explode('_', 'Yak_Dush_Sesh_Chor_Pay_Jum_Shan'),
            'weekdaysMin' => explode('_', 'Ya_Du_Se_Cho_Pa_Ju_Sha'),
            'longDateFormat' => array(
                'LT' => 'HH:mm', 'LTS' => 'HH:mm:ss', 'L' => 'DD/MM/YYYY', 'LL' => 'D MMMM YYYY',
                'LLL' => 'D MMMM YYYY HH:mm', 'LLLL' => 'D MMMM YYYY, dddd HH:mm',
            ),
            'calendar' => array(
                'sameDay' => '[Bugun soat] LT [da]', 'nextDay' => '[Ertaga] LT [da]',
                'nextWeek' => 'dddd [kuni soat] LT [da]', 'lastDay' => '[Kecha soat] LT [da]',
                'lastWeek' => "[O'tgan] dddd [kuni soat] LT [da]", 'sameElse' => 'L',
            ),
            'relativeTime' => array(
                'future' => 'Yaqin %s ichida', 'past' => 'Bir necha %s oldin', 's' => 'soniya', 'ss' => '%d soniya',
                'm' => 'bir daqiqa', 'mm' => '%d daqiqa', 'h' => 'bir soat', 'hh' => '%d soat',
                'd' => 'bir kun', 'dd' => '%d kun', 'M' => 'bir oy', 'MM' => '%d oy',
                'y' => 'bir yil', 'yy' => '%d yil',
            ),
            'week' => array('dow' => 1, 'doy' => 7),
        );
    }
}
