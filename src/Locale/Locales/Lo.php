<?php

namespace Wilkques\Moment\Locale\Locales;

use Wilkques\Moment\Locale\Locale;

class Lo extends Locale
{
    public $abbr = 'lo';

    public static function config()
    {
        return array(
            'months' => explode('_', 'ມັງກອນ_ກຸມພາ_ມີນາ_ເມສາ_ພຶດສະພາ_ມິຖຸນາ_ກໍລະກົດ_ສິງຫາ_ກັນຍາ_ຕຸລາ_ພະຈິກ_ທັນວາ'),
            'monthsShort' => explode('_', 'ມັງກອນ_ກຸມພາ_ມີນາ_ເມສາ_ພຶດສະພາ_ມິຖຸນາ_ກໍລະກົດ_ສິງຫາ_ກັນຍາ_ຕຸລາ_ພະຈິກ_ທັນວາ'),
            'weekdays' => explode('_', 'ອາທິດ_ຈັນ_ອັງຄານ_ພຸດ_ພະຫັດ_ສຸກ_ເສົາ'),
            'weekdaysShort' => explode('_', 'ທິດ_ຈັນ_ອັງຄານ_ພຸດ_ພະຫັດ_ສຸກ_ເສົາ'),
            'weekdaysMin' => explode('_', 'ທ_ຈ_ອຄ_ພ_ພຫ_ສກ_ສ'),
            'weekdaysParseExact' => true,
            'longDateFormat' => array(
                'LT' => 'HH:mm',
                'LTS' => 'HH:mm:ss',
                'L' => 'DD/MM/YYYY',
                'LL' => 'D MMMM YYYY',
                'LLL' => 'D MMMM YYYY HH:mm',
                'LLLL' => 'ວັນdddd D MMMM YYYY HH:mm',
            ),
            'meridiemParse' => '~ຕອນເຊົ້າ|ຕອນແລງ~u',
            'calendar' => array(
                'sameDay' => '[ມື້ນີ້ເວລາ] LT',
                'nextDay' => '[ມື້ອື່ນເວລາ] LT',
                'nextWeek' => '[ວັນ]dddd[ໜ້າເວລາ] LT',
                'lastDay' => '[ມື້ວານນີ້ເວລາ] LT',
                'lastWeek' => '[ວັນ]dddd[ແລ້ວນີ້ເວລາ] LT',
                'sameElse' => 'L',
            ),
            'relativeTime' => array(
                'future' => 'ອີກ %s',
                'past' => '%sຜ່ານມາ',
                's' => 'ບໍ່ເທົ່າໃດວິນາທີ',
                'ss' => '%d ວິນາທີ',
                'm' => '1 ນາທີ',
                'mm' => '%d ນາທີ',
                'h' => '1 ຊົ່ວໂມງ',
                'hh' => '%d ຊົ່ວໂມງ',
                'd' => '1 ມື້',
                'dd' => '%d ມື້',
                'M' => '1 ເດືອນ',
                'MM' => '%d ເດືອນ',
                'y' => '1 ປີ',
                'yy' => '%d ປີ',
            ),
            'dayOfMonthOrdinalParse' => '~(ທີ່)\d{1,2}~u',
        );
    }

    public function isPM($input)
    {
        return $input === 'ຕອນແລງ';
    }

    public function meridiem($hour, $minute, $isLower)
    {
        return $hour < 12 ? 'ຕອນເຊົ້າ' : 'ຕອນແລງ';
    }

    public function ordinal($number, $token = '')
    {
        return 'ທີ່' . $number;
    }
}
