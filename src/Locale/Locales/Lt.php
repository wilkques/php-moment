<?php

namespace Wilkques\Moment\Locale\Locales;

use Wilkques\Moment\Locale\Locale;

class Lt extends Locale
{
    public $abbr = 'lt';

    private static $units = array(
        'ss' => 'sekundė_sekundžių_sekundes',
        'm' => 'minutė_minutės_minutę',
        'mm' => 'minutės_minučių_minutes',
        'h' => 'valanda_valandos_valandą',
        'hh' => 'valandos_valandų_valandas',
        'd' => 'diena_dienos_dieną',
        'dd' => 'dienos_dienų_dienas',
        'M' => 'mėnuo_mėnesio_mėnesį',
        'MM' => 'mėnesiai_mėnesių_mėnesius',
        'y' => 'metai_metų_metus',
        'yy' => 'metai_metų_metus',
    );

    protected static function forms($key)
    {
        if (!isset(self::$units[$key])) {
            // like the TypeError JavaScript raises for the key 's' (translate() of 'ss' with exactly 1 second)
            throw new \UnexpectedValueException("Cannot read properties of undefined (reading 'split')");
        }

        return explode('_', self::$units[$key]);
    }

    protected static function special($number)
    {
        return $number % 10 === 0 || ($number > 10 && $number < 20);
    }

    protected static function translateSingular($withoutSuffix, $key, $isFuture)
    {
        $f = self::forms($key);

        return $withoutSuffix ? $f[0] : ($isFuture ? $f[1] : $f[2]);
    }

    public static function config()
    {
        return array(
            'months' => array(
                'format' => array('sausio', 'vasario', 'kovo', 'balandžio', 'gegužės', 'birželio', 'liepos', 'rugpjūčio', 'rugsėjo', 'spalio', 'lapkričio', 'gruodžio'),
                'standalone' => array('sausis', 'vasaris', 'kovas', 'balandis', 'gegužė', 'birželis', 'liepa', 'rugpjūtis', 'rugsėjis', 'spalis', 'lapkritis', 'gruodis'),
                'isFormat' => '~D[oD]?(\[[^\[\]]*\]|\s)+MMMM?|MMMM?(\[[^\[\]]*\]|\s)+D[oD]?~u',
            ),
            'monthsShort' => array('sau', 'vas', 'kov', 'bal', 'geg', 'bir', 'lie', 'rgp', 'rgs', 'spa', 'lap', 'grd'),
            'weekdays' => array(
                'format' => array('sekmadienį', 'pirmadienį', 'antradienį', 'trečiadienį', 'ketvirtadienį', 'penktadienį', 'šeštadienį'),
                'standalone' => array('sekmadienis', 'pirmadienis', 'antradienis', 'trečiadienis', 'ketvirtadienis', 'penktadienis', 'šeštadienis'),
                'isFormat' => '~dddd HH:mm~u',
            ),
            'weekdaysShort' => array('Sek', 'Pir', 'Ant', 'Tre', 'Ket', 'Pen', 'Šeš'),
            'weekdaysMin' => array('S', 'P', 'A', 'T', 'K', 'Pn', 'Š'),
            'weekdaysParseExact' => true,
            'longDateFormat' => array(
                'LT' => 'HH:mm', 'LTS' => 'HH:mm:ss', 'L' => 'YYYY-MM-DD', 'LL' => 'YYYY [m.] MMMM D [d.]',
                'LLL' => 'YYYY [m.] MMMM D [d.], HH:mm [val.]', 'LLLL' => 'YYYY [m.] MMMM D [d.], dddd, HH:mm [val.]',
                'l' => 'YYYY-MM-DD', 'll' => 'YYYY [m.] MMMM D [d.]', 'lll' => 'YYYY [m.] MMMM D [d.], HH:mm [val.]',
                'llll' => 'YYYY [m.] MMMM D [d.], ddd, HH:mm [val.]',
            ),
            'calendar' => array(
                'sameDay' => '[Šiandien] LT', 'nextDay' => '[Rytoj] LT', 'nextWeek' => 'dddd LT',
                'lastDay' => '[Vakar] LT', 'lastWeek' => '[Praėjusį] dddd LT', 'sameElse' => 'L',
            ),
            'relativeTime' => array('future' => 'po %s', 'past' => 'prieš %s'),
            'dayOfMonthOrdinalParse' => '~\d{1,2}-oji~u',
            'week' => array('dow' => 1, 'doy' => 4),
        );
    }

    public function relativeTime($number, $withoutSuffix, $key, $isFuture)
    {
        if ($key === 'w' || $key === 'ww') {
            return parent::relativeTime($number, $withoutSuffix, $key, $isFuture); // not set in the JS locale: moment's defaults
        }
        switch ($key) {
            case 's':
                if ($withoutSuffix) {
                    return 'kelios sekundės';
                }

                return $isFuture ? 'kelių sekundžių' : 'kelias sekundes';
            case 'm':
            case 'h':
            case 'd':
            case 'M':
            case 'y':
                return self::translateSingular($withoutSuffix, $key, $isFuture);
        }

        // ss mm hh dd MM yy
        $result = $number . ' ';
        if ($number === 1) {
            return $result . self::translateSingular($withoutSuffix, substr($key, 0, 1), $isFuture);
        }
        $f = self::forms($key);
        if ($withoutSuffix) {
            return $result . (self::special($number) ? $f[1] : $f[0]);
        }
        if ($isFuture) {
            return $result . $f[1];
        }

        return $result . (self::special($number) ? $f[1] : $f[2]);
    }

    public function ordinal($number, $token = '')
    {
        return $number . '-oji';
    }
}
