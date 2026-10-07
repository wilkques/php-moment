<?php

namespace Wilkques\Moment\Locale\Locales;

use Wilkques\Moment\Locale\Locale;

class Bm extends Locale
{
    public $abbr = 'bm';

    public static function config()
    {
        return array(
            'months' => array('Zanwuyekalo', 'Fewuruyekalo', 'Marisikalo', 'Awirilikalo', 'Mɛkalo', 'Zuwɛnkalo', 'Zuluyekalo', 'Utikalo', 'Sɛtanburukalo', 'ɔkutɔburukalo', 'Nowanburukalo', 'Desanburukalo'),
            'monthsShort' => array('Zan', 'Few', 'Mar', 'Awi', 'Mɛ', 'Zuw', 'Zul', 'Uti', 'Sɛt', 'ɔku', 'Now', 'Des'),
            'weekdays' => array('Kari', 'Ntɛnɛn', 'Tarata', 'Araba', 'Alamisa', 'Juma', 'Sibiri'),
            'weekdaysShort' => array('Kar', 'Ntɛ', 'Tar', 'Ara', 'Ala', 'Jum', 'Sib'),
            'weekdaysMin' => array('Ka', 'Nt', 'Ta', 'Ar', 'Al', 'Ju', 'Si'),
            'longDateFormat' => array(
                'LT' => 'HH:mm', 'LTS' => 'HH:mm:ss', 'L' => 'DD/MM/YYYY',
                'LL' => 'MMMM [tile] D [san] YYYY', 'LLL' => 'MMMM [tile] D [san] YYYY [lɛrɛ] HH:mm',
                'LLLL' => 'dddd MMMM [tile] D [san] YYYY [lɛrɛ] HH:mm',
            ),
            'calendar' => array(
                'sameDay' => '[Bi lɛrɛ] LT', 'nextDay' => '[Sini lɛrɛ] LT', 'nextWeek' => 'dddd [don lɛrɛ] LT',
                'lastDay' => '[Kunu lɛrɛ] LT', 'lastWeek' => 'dddd [tɛmɛnen lɛrɛ] LT', 'sameElse' => 'L',
            ),
            'relativeTime' => array(
                'future' => '%s kɔnɔ', 'past' => 'a bɛ %s bɔ', 's' => 'sanga dama dama', 'ss' => 'sekondi %d',
                'm' => 'miniti kelen', 'mm' => 'miniti %d', 'h' => 'lɛrɛ kelen', 'hh' => 'lɛrɛ %d',
                'd' => 'tile kelen', 'dd' => 'tile %d', 'M' => 'kalo kelen', 'MM' => 'kalo %d',
                'y' => 'san kelen', 'yy' => 'san %d',
            ),
            'week' => array('dow' => 1, 'doy' => 4),
        );
    }
}
