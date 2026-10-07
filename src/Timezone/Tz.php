<?php

namespace Wilkques\Moment\Timezone;

use Wilkques\Moment\Calendar;

/**
 * moment.tz.*: the registry of zones. The data (moment-timezone's packed IANA data) is read
 * from data/latest.php the first time a zone is asked for; add()/link()/load() add more.
 */
class Tz
{
    const VERSION = '0.6.5';

    public static $moveInvalidForward = true;
    public static $moveAmbiguousForward = false;

    /** @var Zone|null the default zone of moment.tz.setDefault() */
    public static $defaultZone;

    protected static $zones = array();
    protected static $links = array();
    protected static $names = array();
    protected static $countries = array();
    protected static $dataVersion = '';
    protected static $loaded = false;
    protected static $guess;

    /** @var array offset (minutes, as a string) => normalized zone name => true: moment-timezone's `guesses` */
    protected static $guesses = array();

    /**
     * Whether guess() may answer from PHP's zone name alone (moment-timezone does the same with the
     * name Intl reports). Set to false to always run the offset-scoring search, as for a host that
     * only reports offsets.
     */
    public static $guessByName = true;

    /**
     * Whether the host's zone abbreviation counts when scoring candidates in guess().
     * JavaScript takes it from the zone's long name via ICU (toTimeString(): "(Central Standard Time)" -> CST).
     *   null  (default) use ext-intl's ICU names the same way; without intl only offsets decide
     *   false only offsets decide
     *   true  like null, but without intl fall back to PHP's own abbreviation ("T": CST, EST)
     */
    public static $guessUseAbbr = null;

    /** @internal tests: act as if ext-intl were missing */
    public static $withoutIntl = false;

    protected static function boot()
    {
        if (!self::$loaded) {
            self::$loaded = true;
            self::load(require dirname(dirname(__DIR__)) . '/data/latest.php');
        }
    }

    /** Forget everything added at runtime (add/link/load/setDefault/the move*Forward flags) and reload the bundled data on next use. */
    public static function reset()
    {
        self::$zones = array();
        self::$links = array();
        self::$names = array();
        self::$countries = array();
        self::$dataVersion = '';
        self::$loaded = false;
        self::$guess = null;
        self::$guesses = array();
        self::$defaultZone = null;
        self::$moveInvalidForward = true;
        self::$moveAmbiguousForward = false;
    }

    public static function normalizeName($name)
    {
        return str_replace('/', '_', strtolower((string) $name));
    }

    /** @param string|array $packed one or more packed zone strings */
    public static function add($packed)
    {
        self::boot();
        self::addZones($packed);
    }

    /** @param string|array $aliases one or more "Name|Alias" strings */
    public static function link($aliases)
    {
        self::boot();
        self::addLinks($aliases);
    }

    /** @param array $data array('zones' => array, 'links' => array, 'countries' => array, 'version' => string) */
    public static function load(array $data)
    {
        self::boot();
        self::addZones(isset($data['zones']) ? $data['zones'] : array());
        self::addLinks(isset($data['links']) ? $data['links'] : array());
        self::addCountries(isset($data['countries']) ? $data['countries'] : array());
        self::$dataVersion = isset($data['version']) ? $data['version'] : '';
    }

    protected static function addZones($packed)
    {
        foreach ((array) $packed as $string) {
            $split = explode('|', $string);
            $normalized = self::normalizeName($split[0]);
            self::$zones[$normalized] = $string;
            self::$names[$normalized] = $split[0];
            foreach (explode(' ', $split[2]) as $offset) {
                $key = (string) (Zone::unpackBase60($offset) + 0);
                self::$guesses[$key == '-0' ? '0' : $key][$normalized] = true;
            }
        }
        self::$guess = null;
    }

    protected static function addLinks($aliases)
    {
        foreach ((array) $aliases as $string) {
            $alias = explode('|', $string);
            $n0 = self::normalizeName($alias[0]);
            $n1 = self::normalizeName($alias[1]);

            self::$links[$n0] = $n1;
            self::$names[$n0] = $alias[0];
            self::$links[$n1] = $n0;
            self::$names[$n1] = $alias[1];
        }
    }

    protected static function addCountries($data)
    {
        foreach ((array) $data as $string) {
            $split = explode('|', $string);
            self::$countries[strtoupper($split[0])] = explode(' ', $split[1]);
        }
    }

    /** @internal country code => zone names */
    public static function countryMap()
    {
        self::boot();

        return self::$countries;
    }

    /** moment.tz.zone(name): a Zone or null */
    public static function zone($name, $caller = null)
    {
        self::boot();
        $name = self::normalizeName($name);
        $zone = isset(self::$zones[$name]) ? self::$zones[$name] : null;

        if ($zone instanceof Zone) {
            return $zone;
        }
        if (is_string($zone)) {
            return self::$zones[$name] = new Zone($zone);
        }
        if (isset(self::$links[$name]) && $caller !== 'link') {
            $link = self::zone(self::$links[$name], 'link');
            if ($link) {
                $zone = self::$zones[$name] = new Zone();
                $zone->set(array('name' => $link->name, 'abbrs' => $link->abbrs, 'untils' => $link->untils,
                    'offsets' => $link->offsets, 'population' => $link->population));
                $zone->name = self::$names[$name];

                return $zone;
            }
        }

        return null;
    }

    /** @deprecated use zone() */
    public static function zoneExists($name)
    {
        return (bool) self::zone($name);
    }

    /** @return string[] sorted zone names (links included) */
    public static function names()
    {
        self::boot();
        $out = array();
        foreach (self::$names as $normalized => $name) {
            if ((isset(self::$zones[$normalized]) || (isset(self::$links[$normalized]) && isset(self::$zones[self::$links[$normalized]]))) && $name) {
                $out[] = $name;
            }
        }
        sort($out, SORT_STRING);

        return $out;
    }

    /** @return string[] ISO country codes */
    public static function countries()
    {
        self::boot();

        return array_keys(self::$countries);
    }

    /** @return array|null zone names, or array(array('name' =>, 'offset' =>)) with $withOffset */
    public static function zonesForCountry($country, $withOffset = false)
    {
        self::boot();
        $code = strtoupper($country);
        if (!isset(self::$countries[$code])) {
            return null;
        }
        $zones = self::$countries[$code];
        sort($zones, SORT_STRING);
        if (!$withOffset) {
            return $zones;
        }

        $out = array();
        $now = \Wilkques\Moment\Moment::now();
        foreach ($zones as $name) {
            $zone = self::zone($name);
            $out[] = array('name' => $name, 'offset' => $zone->utcOffset($now));
        }

        return $out;
    }

    public static function dataVersion()
    {
        self::boot();

        return self::$dataVersion;
    }

    /**
     * moment.tz.guess(): the host's zone. PHP's own zone name is used when moment-timezone knows it;
     * otherwise (or with $guessByName = false) the zone whose offsets and abbreviations best match
     * the host's over the last two and next two years wins, like moment-timezone's search.
     */
    public static function guess($ignoreCache = false)
    {
        self::boot();
        if (self::$guess === null || $ignoreCache) {
            self::$guess = self::rebuildGuess();
        }

        return self::$guess;
    }

    protected static function rebuildGuess()
    {
        if (self::$guessByName) {
            $name = date_default_timezone_get();
            if (strlen($name) > 3 && isset(self::$names[self::normalizeName($name)])) {
                return self::$names[self::normalizeName($name)];
            }
        }

        $offsets = static::userOffsets();
        $candidates = array();
        $checked = array();
        foreach ($offsets as $o) {
            $key = (string) $o['offset'];
            if (isset($checked[$key])) {
                continue;
            }
            $checked[$key] = true;
            if (isset(self::$guesses[$key])) {
                foreach (self::$guesses[$key] as $normalized => $true) {
                    $candidates[$normalized] = true;
                }
            }
        }

        $scores = array();
        foreach (array_keys($candidates) as $normalized) {
            $zone = self::zone(self::$names[$normalized]);
            $offsetScore = 0;
            $abbrScore = 0;
            foreach ($offsets as $o) {
                $offsetScore += abs($zone->utcOffset($o['at']) - $o['offset']);
                if ($o['abbr'] === null || preg_replace('/[^A-Z]/', '', $zone->abbr($o['at'])) !== $o['abbr']) {
                    $abbrScore++;
                }
            }
            $scores[] = array($offsetScore, $abbrScore, $zone);
        }

        usort($scores, array(__CLASS__, 'compareScores'));

        return $scores ? $scores[0][2]->name : null;
    }

    protected static function compareScores($a, $b)
    {
        if ($a[0] !== $b[0]) {
            return $a[0] < $b[0] ? -1 : 1;
        }
        if ($a[1] !== $b[1]) {
            return $a[1] - $b[1];
        }
        if ($a[2]->population !== $b[2]->population) {
            return $b[2]->population - $a[2]->population;
        }

        return strcmp($b[2]->name, $a[2]->name);
    }

    /** the host's offset (minutes, positive west) and abbreviation at the instant $at (ms) */
    protected static function hostOffsetAt($at)
    {
        $tz = new \DateTimeZone(date_default_timezone_get());
        $d = new \DateTime('@' . Calendar::str(floor($at / 1000)));
        $d->setTimezone($tz);
        $abbr = self::hostAbbr($tz->getName(), $at, $d);

        return array('at' => $at, 'abbr' => $abbr, 'offset' => -$tz->getOffset($d) / 60);
    }

    /** moment-timezone's OffsetAt() abbreviation of the host zone at $at (ms): from the ICU long name, as V8 prints it */
    protected static function hostAbbr($name, $at, \DateTime $d)
    {
        if (self::$guessUseAbbr === false) {
            return null;
        }
        if (!self::$withoutIntl && class_exists('IntlTimeZone')) {
            $icu = \IntlTimeZone::createTimeZone($name);
            if ($icu === null) {
                return null;
            }
            $raw = 0;
            $dst = 0;
            $icu->getOffset((float) $at, false, $raw, $dst);
            $long = $icu->getDisplayName($dst != 0, \IntlTimeZone::DISPLAY_LONG, 'en_US');
            $timeString = '12:00:00 GMT+0000 (' . $long . ')';
            if (preg_match('/\([a-z ]+\)/i', $timeString, $paren)) {
                $abbr = preg_match_all('/[A-Z]/', $paren[0], $caps) ? implode('', $caps[0]) : null;
            } else {
                $abbr = preg_match_all('/[A-Z]{3,5}/', $timeString, $words) ? $words[0][0] : null;
            }

            return $abbr === 'GMT' ? null : $abbr;
        }
        if (self::$guessUseAbbr === true) {
            $abbr = $d->format('T');

            return preg_match('/^[A-Z]{3,5}$/', $abbr) && $abbr !== 'GMT' ? $abbr : null;
        }

        return null;
    }

    /** the host's local midnight of the first of month $month0 (0-based, may exceed 11) of $year, as ms */
    protected static function hostMonthStart($year, $month0)
    {
        $d = new \DateTime('now', new \DateTimeZone(date_default_timezone_get()));
        $d->setDate($year + (int) floor($month0 / 12), $month0 % 12 + 1, 1);
        $d->setTime(0, 0, 0);

        return $d->getTimestamp() * 1000;
    }

    protected static function findChange($low, $high)
    {
        while (($diff = (int) (($high['at'] - $low['at']) / 12e4) * 6e4) != 0) {
            $mid = static::hostOffsetAt($low['at'] + $diff);
            if ($mid['offset'] == $low['offset']) {
                $low = $mid;
            } else {
                $high = $mid;
            }
        }

        return $low;
    }

    protected static function userOffsets()
    {
        $now = \Wilkques\Moment\Moment::now();
        $startYear = (int) gmdate('Y', Calendar::int(floor($now / 1000))) - 2;
        $last = static::hostOffsetAt(static::hostMonthStart($startYear, 0));
        $lastOffset = $last['offset'];
        $offsets = array($last);

        for ($i = 1; $i < 48; $i++) {
            $at = static::hostMonthStart($startYear, $i);
            $next = static::hostOffsetAt($at);
            if ($next['offset'] != $lastOffset) {
                $change = static::findChange($last, $next);
                $offsets[] = $change;
                $offsets[] = static::hostOffsetAt($change['at'] + 6e4);
                $last = $next;
                $lastOffset = $next['offset'];
            }
        }
        for ($i = 0; $i < 4; $i++) {
            $offsets[] = static::hostOffsetAt(static::hostMonthStart($startYear + $i, 0));
            $offsets[] = static::hostOffsetAt(static::hostMonthStart($startYear + $i, 6));
        }

        return $offsets;
    }

    /** moment.tz.setDefault(): zones for moments created through createInZone-less parsing; null clears. */
    public static function setDefault($name = null)
    {
        self::$defaultZone = $name ? self::zone($name) : null;
    }

    /** moment.tz.needsOffset(): whether the moment was read from wall-clock parts that still need a zone's offset */
    public static function needsOffset($m)
    {
        return $m->needsOffset();
    }

    public static function unpack($string)
    {
        return Zone::unpack($string);
    }

    public static function unpackBase60($string)
    {
        return Zone::unpackBase60($string);
    }
}
