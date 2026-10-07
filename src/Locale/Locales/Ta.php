<?php

namespace Wilkques\Moment\Locale\Locales;

use Wilkques\Moment\Locale\Locale;

class Ta extends Locale
{
    public $abbr = 'ta';

    public static function config()
    {
        return array(
            'months' => explode('_', 'ஜனவரி_பிப்ரவரி_மார்ச்_ஏப்ரல்_மே_ஜூன்_ஜூலை_ஆகஸ்ட்_செப்டெம்பர்_அக்டோபர்_நவம்பர்_டிசம்பர்'),
            'monthsShort' => explode('_', 'ஜனவரி_பிப்ரவரி_மார்ச்_ஏப்ரல்_மே_ஜூன்_ஜூலை_ஆகஸ்ட்_செப்டெம்பர்_அக்டோபர்_நவம்பர்_டிசம்பர்'),
            'weekdays' => explode('_', 'ஞாயிற்றுக்கிழமை_திங்கட்கிழமை_செவ்வாய்கிழமை_புதன்கிழமை_வியாழக்கிழமை_வெள்ளிக்கிழமை_சனிக்கிழமை'),
            'weekdaysShort' => explode('_', 'ஞாயிறு_திங்கள்_செவ்வாய்_புதன்_வியாழன்_வெள்ளி_சனி'),
            'weekdaysMin' => explode('_', 'ஞா_தி_செ_பு_வி_வெ_ச'),
            'longDateFormat' => array(
                'LT' => 'HH:mm',
                'LTS' => 'HH:mm:ss',
                'L' => 'DD/MM/YYYY',
                'LL' => 'D MMMM YYYY',
                'LLL' => 'D MMMM YYYY, HH:mm',
                'LLLL' => 'dddd, D MMMM YYYY, HH:mm',
            ),
            'calendar' => array(
                'sameDay' => '[இன்று] LT',
                'nextDay' => '[நாளை] LT',
                'nextWeek' => 'dddd, LT',
                'lastDay' => '[நேற்று] LT',
                'lastWeek' => '[கடந்த வாரம்] dddd, LT',
                'sameElse' => 'L',
            ),
            'relativeTime' => array(
                'future' => '%s இல்',
                'past' => '%s முன்',
                's' => 'ஒரு சில விநாடிகள்',
                'ss' => '%d விநாடிகள்',
                'm' => 'ஒரு நிமிடம்',
                'mm' => '%d நிமிடங்கள்',
                'h' => 'ஒரு மணி நேரம்',
                'hh' => '%d மணி நேரம்',
                'd' => 'ஒரு நாள்',
                'dd' => '%d நாட்கள்',
                'M' => 'ஒரு மாதம்',
                'MM' => '%d மாதங்கள்',
                'y' => 'ஒரு வருடம்',
                'yy' => '%d ஆண்டுகள்',
            ),
            'dayOfMonthOrdinalParse' => '~\d{1,2}வது~u',
            'meridiemParse' => '~யாமம்|வைகறை|காலை|நண்பகல்|எற்பாடு|மாலை~u',
            'week' => array('dow' => 0, 'doy' => 6),
        );
    }

    protected static $symbolMap = array(
        '1' => '௧', '2' => '௨', '3' => '௩', '4' => '௪', '5' => '௫',
        '6' => '௬', '7' => '௭', '8' => '௮', '9' => '௯', '0' => '௦',
    );

    protected static $numberMap = array(
        '௧' => '1', '௨' => '2', '௩' => '3', '௪' => '4', '௫' => '5',
        '௬' => '6', '௭' => '7', '௮' => '8', '௯' => '9', '௦' => '0',
    );

    public function ordinal($number, $token = '')
    {
        return $number . 'வது';
    }

    public function preparse($string)
    {
        return strtr($string, self::$numberMap);
    }

    public function postformat($string)
    {
        return strtr($string, self::$symbolMap);
    }

    public function meridiem($hour, $minute, $isLower)
    {
        if ($hour < 2) {
            return ' யாமம்';
        } elseif ($hour < 6) {
            return ' வைகறை';
        } elseif ($hour < 10) {
            return ' காலை';
        } elseif ($hour < 14) {
            return ' நண்பகல்';
        } elseif ($hour < 18) {
            return ' எற்பாடு';
        } elseif ($hour < 22) {
            return ' மாலை';
        }

        return ' யாமம்';
    }

    public function meridiemHour($hour, $meridiem)
    {
        if ($hour === 12) {
            $hour = 0;
        }
        if ($meridiem === 'யாமம்') {
            return $hour < 2 ? $hour : $hour + 12;
        } elseif ($meridiem === 'வைகறை' || $meridiem === 'காலை') {
            return $hour;
        } elseif ($meridiem === 'நண்பகல்') {
            return $hour >= 10 ? $hour : $hour + 12;
        }

        return $hour + 12;
    }
}
