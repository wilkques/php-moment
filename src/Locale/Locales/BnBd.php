<?php

namespace Wilkques\Moment\Locale\Locales;

use Wilkques\Moment\Locale\Locale;

class BnBd extends Locale
{
    public $abbr = 'bn-bd';

    public static function config()
    {
        return array(
            'months' => explode('_', 'জানুয়ারি_ফেব্রুয়ারি_মার্চ_এপ্রিল_মে_জুন_জুলাই_আগস্ট_সেপ্টেম্বর_অক্টোবর_নভেম্বর_ডিসেম্বর'),
            'monthsShort' => explode('_', 'জানু_ফেব্রু_মার্চ_এপ্রিল_মে_জুন_জুলাই_আগস্ট_সেপ্ট_অক্টো_নভে_ডিসে'),
            'weekdays' => explode('_', 'রবিবার_সোমবার_মঙ্গলবার_বুধবার_বৃহস্পতিবার_শুক্রবার_শনিবার'),
            'weekdaysShort' => explode('_', 'রবি_সোম_মঙ্গল_বুধ_বৃহস্পতি_শুক্র_শনি'),
            'weekdaysMin' => explode('_', 'রবি_সোম_মঙ্গল_বুধ_বৃহ_শুক্র_শনি'),
            'longDateFormat' => array(
                'LT' => 'A h:mm সময়', 'LTS' => 'A h:mm:ss সময়', 'L' => 'DD/MM/YYYY', 'LL' => 'D MMMM YYYY',
                'LLL' => 'D MMMM YYYY, A h:mm সময়', 'LLLL' => 'dddd, D MMMM YYYY, A h:mm সময়',
            ),
            'calendar' => array(
                'sameDay' => '[আজ] LT', 'nextDay' => '[আগামীকাল] LT', 'nextWeek' => 'dddd, LT',
                'lastDay' => '[গতকাল] LT', 'lastWeek' => '[গত] dddd, LT', 'sameElse' => 'L',
            ),
            'relativeTime' => array(
                'future' => '%s পরে', 'past' => '%s আগে', 's' => 'কয়েক সেকেন্ড', 'ss' => '%d সেকেন্ড',
                'm' => 'এক মিনিট', 'mm' => '%d মিনিট', 'h' => 'এক ঘন্টা', 'hh' => '%d ঘন্টা',
                'd' => 'এক দিন', 'dd' => '%d দিন', 'M' => 'এক মাস', 'MM' => '%d মাস', 'y' => 'এক বছর', 'yy' => '%d বছর',
            ),
            'meridiemParse' => '~রাত|ভোর|সকাল|দুপুর|বিকাল|সন্ধ্যা|রাত~u',
            'week' => array('dow' => 0, 'doy' => 6),
        );
    }

    public function preparse($string)
    {
        return strtr($string, array('১' => '1', '২' => '2', '৩' => '3', '৪' => '4', '৫' => '5', '৬' => '6', '৭' => '7', '৮' => '8', '৯' => '9', '০' => '0'));
    }

    public function postformat($string)
    {
        return strtr($string, array('1' => '১', '2' => '২', '3' => '৩', '4' => '৪', '5' => '৫', '6' => '৬', '7' => '৭', '8' => '৮', '9' => '৯', '0' => '০'));
    }

    public function meridiemHour($hour, $meridiem)
    {
        if ($hour === 12) {
            $hour = 0;
        }
        if ($meridiem === 'রাত') {
            return $hour < 4 ? $hour : $hour + 12;
        } elseif ($meridiem === 'ভোর') {
            return $hour;
        } elseif ($meridiem === 'সকাল') {
            return $hour;
        } elseif ($meridiem === 'দুপুর') {
            return $hour >= 3 ? $hour : $hour + 12;
        } elseif ($meridiem === 'বিকাল') {
            return $hour + 12;
        } elseif ($meridiem === 'সন্ধ্যা') {
            return $hour + 12;
        }

        return null;
    }

    public function meridiem($hour, $minute, $isLower)
    {
        if ($hour < 4) {
            return 'রাত';
        } elseif ($hour < 6) {
            return 'ভোর';
        } elseif ($hour < 12) {
            return 'সকাল';
        } elseif ($hour < 15) {
            return 'দুপুর';
        } elseif ($hour < 18) {
            return 'বিকাল';
        } elseif ($hour < 20) {
            return 'সন্ধ্যা';
        }

        return 'রাত';
    }
}
