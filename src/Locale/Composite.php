<?php

namespace Wilkques\Moment\Locale;

/**
 * A locale made of a parent locale plus runtime settings (defineLocale() with a parentLocale, updateLocale()).
 *
 * moment merges settings key by key, so a child that sets `ordinal` replaces the parent's ordinal even when the
 * parent implements it as a function. A PHP locale class implements such settings as methods, and a subclass
 * method always wins over a property, so this class decides per call: when the child sets the setting a method
 * stands for, a plain Locale built from the merged settings answers; otherwise the parent does.
 */
class Composite extends Locale
{
    /** @var Locale */
    protected $source;
    /** @var Locale the merged settings as plain data (what moment's merged config would be) */
    protected $plain;
    /** @var array the settings the child itself gave */
    protected $own;

    public function __construct(Locale $parent, array $config = array())
    {
        $this->source = $parent;
        $this->abbr = $parent->abbr;
        $this->rebuild($config);
    }

    protected function rebuild(array $own)
    {
        $this->own = $own;
        $data = $own;
        unset($data['abbr'], $data['parentLocale']);
        $this->plain = new Locale(Locale::mergeConfigs($this->source->getConfig(), $data));
        $this->plain->abbr = $this->abbr;
    }

    /** the locale that answers for the settings $keys: the plain one if the child set any of them */
    protected function route($keys)
    {
        foreach ((array) $keys as $k) {
            if (isset($this->own[$k])) {
                return $this->plain;
            }
        }

        return $this->source;
    }

    protected function routeSub($key, $sub)
    {
        return isset($this->own[$key][$sub]) ? $this->plain : $this->source;
    }

    public function getConfig()
    {
        return $this->plain->getConfig();
    }

    public function set(array $config)
    {
        $this->rebuild(array_merge($this->own, $config));

        return $this;
    }

    public function months($m = null, $format = '')
    {
        return $this->route('months')->months($m, $format);
    }

    public function monthsShort($m = null, $format = '')
    {
        return $this->route('monthsShort')->monthsShort($m, $format);
    }

    protected static $monthKeys = array('months', 'monthsShort', 'monthsParseExact', 'monthsRegex', 'monthsShortRegex', 'monthsStrictRegex',
        'monthsShortStrictRegex', 'monthsParse', 'longMonthsParse', 'shortMonthsParse');
    protected static $weekdayKeys = array('weekdays', 'weekdaysShort', 'weekdaysMin', 'weekdaysParseExact', 'weekdaysRegex', 'weekdaysShortRegex',
        'weekdaysMinRegex', 'weekdaysStrictRegex', 'weekdaysShortStrictRegex', 'weekdaysMinStrictRegex', 'weekdaysParse', 'fullWeekdaysParse',
        'shortWeekdaysParse', 'minWeekdaysParse');

    public function monthsParse($monthName, $format = null, $strict = false)
    {
        return $this->route(self::$monthKeys)->monthsParse($monthName, $format, $strict);
    }

    public function monthsRegex($isStrict = false)
    {
        return $this->route(self::$monthKeys)->monthsRegex($isStrict);
    }

    public function monthsShortRegex($isStrict = false)
    {
        return $this->route(self::$monthKeys)->monthsShortRegex($isStrict);
    }

    public function weekdays($m = null, $format = '')
    {
        return $this->route(array('weekdays', 'week'))->weekdays($m, $format);
    }

    public function weekdaysShort($m = null)
    {
        return $this->route(array('weekdaysShort', 'week'))->weekdaysShort($m);
    }

    public function weekdaysMin($m = null)
    {
        return $this->route(array('weekdaysMin', 'week'))->weekdaysMin($m);
    }

    public function weekdaysParse($weekdayName, $format = null, $strict = false)
    {
        return $this->route(self::$weekdayKeys)->weekdaysParse($weekdayName, $format, $strict);
    }

    public function weekdaysRegex($isStrict = false)
    {
        return $this->route(self::$weekdayKeys)->weekdaysRegex($isStrict);
    }

    public function weekdaysShortRegex($isStrict = false)
    {
        return $this->route(self::$weekdayKeys)->weekdaysShortRegex($isStrict);
    }

    public function weekdaysMinRegex($isStrict = false)
    {
        return $this->route(self::$weekdayKeys)->weekdaysMinRegex($isStrict);
    }

    public function longDateFormat($key)
    {
        $own = isset($this->own['longDateFormat']) ? $this->own['longDateFormat'] : array();
        $plain = isset($own[$key]) || isset($own[strtoupper($key)]);

        $locale = $plain ? $this->plain : $this->source;

        return $locale->longDateFormat($key);
    }

    public function calendar($key, $mom = null, $now = null)
    {
        $locale = $this->routeSub('calendar', $key);
        if ($locale === $this->source && !isset($this->own['calendar'][$key]) && isset($this->own['calendar']['sameElse'])
            && !$this->sourceHas('calendar', $key)) {
            $locale = $this->plain; // the key falls back to sameElse, which the child changed
        }

        return $locale->calendar($key, $mom, $now);
    }

    /** whether the parent's own settings name a calendar entry for $key */
    protected function sourceHas($setting, $key)
    {
        $config = $this->source->getConfig();

        return isset($config[$setting][$key]);
    }

    public function relativeTime($number, $withoutSuffix, $key, $isFuture)
    {
        return $this->routeSub('relativeTime', $key)->relativeTime($number, $withoutSuffix, $key, $isFuture);
    }

    public function pastFuture($diff, $output)
    {
        return $this->routeSub('relativeTime', $diff > 0 ? 'future' : 'past')->pastFuture($diff, $output);
    }

    public function ordinal($number, $token = '')
    {
        return $this->route('ordinal')->ordinal($number, $token);
    }

    public function meridiem($hours, $minutes, $isLower)
    {
        return $this->route('meridiem')->meridiem($hours, $minutes, $isLower);
    }

    public function isPM($input)
    {
        return $this->route('isPM')->isPM($input);
    }

    public function meridiemHour($hour, $meridiem)
    {
        return $this->route('meridiemHour')->meridiemHour($hour, $meridiem);
    }

    public function hasMeridiemHour()
    {
        return $this->route('meridiemHour')->hasMeridiemHour();
    }

    public function meridiemParse()
    {
        return $this->route('meridiemParse')->meridiemParse();
    }

    public function dayOfMonthOrdinalParse($isStrict = true)
    {
        return $this->route(array('dayOfMonthOrdinalParse', 'ordinal'))->dayOfMonthOrdinalParse($isStrict);
    }

    public function week($mom = null)
    {
        return $this->route('week')->week($mom);
    }

    public function firstDayOfWeek()
    {
        return $this->route('week')->firstDayOfWeek();
    }

    public function firstDayOfYear()
    {
        return $this->route('week')->firstDayOfYear();
    }

    public function invalidDate()
    {
        return $this->route('invalidDate')->invalidDate();
    }

    public function preparse($string)
    {
        return $this->route('preparse')->preparse($string);
    }

    public function postformat($string)
    {
        return $this->route('postformat')->postformat($string);
    }

    public function eras($m = null, $format = null)
    {
        return $this->route('eras')->eras($m, $format);
    }

    public function erasParse($eraName, $format, $strict)
    {
        return $this->route('eras')->erasParse($eraName, $format, $strict);
    }

    public function erasConvertYear($era, $year = null)
    {
        return $this->route('eras')->erasConvertYear($era, $year);
    }

    public function erasNameRegex($isStrict = false)
    {
        return $this->route('eras')->erasNameRegex($isStrict);
    }

    public function erasAbbrRegex($isStrict = false)
    {
        return $this->route('eras')->erasAbbrRegex($isStrict);
    }

    public function erasNarrowRegex($isStrict = false)
    {
        return $this->route('eras')->erasNarrowRegex($isStrict);
    }

    public function eraYearOrdinalRegex()
    {
        return $this->route(array('eraYearOrdinalRegex', 'eras'))->eraYearOrdinalRegex();
    }

    public function eraYearOrdinalParse($input, $match)
    {
        return $this->route(array('eraYearOrdinalParse', 'eras'))->eraYearOrdinalParse($input, $match);
    }
}
