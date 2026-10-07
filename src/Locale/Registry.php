<?php

namespace Wilkques\Moment\Locale;

/**
 * moment.locale() / defineLocale() / updateLocale() / locales(): the registry of locales.
 *
 * A locale named 'zh-tw' is loaded on first use from the class Locales\ZhTw (name parts
 * joined in StudlyCase). Anything defined at runtime lives here too.
 */
class Registry
{
    protected static $locales = array();
    protected static $global;

    /** @var array name => stack of the locales an update() replaced */
    protected static $previous = array();

    public static function normalize($key)
    {
        return $key ? str_replace('_', '-', strtolower($key)) : $key;
    }

    protected static function studly($name)
    {
        $out = '';
        foreach (explode('-', $name) as $part) {
            $out .= ucfirst($part);
        }

        return $out;
    }

    /** @return Locale|null the locale object for an exact name */
    protected static function load($name)
    {
        if (isset(self::$locales[$name])) {
            return self::$locales[$name];
        }
        if (!preg_match('/^[a-z0-9]+(-[a-z0-9]+)*$/', $name)) {
            return null;
        }
        $class = __NAMESPACE__ . '\\Locales\\' . self::studly($name);
        if (!class_exists($class)) {
            return null;
        }

        return self::$locales[$name] = new $class();
    }

    /**
     * moment's getLocale(): resolve a name ("zh-TW", "zh_tw", "zh"), a list of names (first one found wins,
     * shortening each to its language), a Locale, or an object with a locale() method. No argument, or a name
     * nothing matches, gives the global locale, as in moment.
     *
     * @return Locale
     */
    public static function get($key = null)
    {
        $locale = self::find($key);

        return $locale ?: self::globalLocale_();
    }

    /** Like get(), but null when nothing matches (instead of falling back to the global locale). */
    public static function find($key = null)
    {
        if ($key instanceof Locale) {
            return $key;
        }
        if (is_object($key) && method_exists($key, 'getSetLocale')) {
            $key = $key->getSetLocale();
        } elseif (is_object($key) && method_exists($key, 'locale')) {
            $key = $key->locale();
        }
        if (!$key) {
            return self::globalLocale_();
        }

        return self::choose((array) $key);
    }

    /** moment's chooseLocale(): try "zh-tw", then "zh"; skip ahead when the next name shares the prefix */
    protected static function choose(array $names)
    {
        $names = array_values($names);
        for ($i = 0; $i < count($names); $i++) {
            $split = explode('-', self::normalize($names[$i]));
            $next = isset($names[$i + 1]) ? explode('-', self::normalize($names[$i + 1])) : null;
            for ($j = count($split); $j > 0; $j--) {
                $locale = self::load(implode('-', array_slice($split, 0, $j)));
                if ($locale) {
                    return $locale;
                }
                if ($next && count($next) >= $j && self::commonPrefix($split, $next) >= $j - 1) {
                    break;
                }
            }
        }

        return null;
    }

    protected static function commonPrefix(array $a, array $b)
    {
        $min = min(count($a), count($b));
        for ($i = 0; $i < $min; $i++) {
            if ($a[$i] !== $b[$i]) {
                return $i;
            }
        }

        return $min;
    }

    protected static function globalLocale_()
    {
        if (!self::$global) {
            self::$global = self::load('en');
        }

        return self::$global;
    }

    /** Get or set the global locale; returns its abbreviation. */
    public static function globalLocale($key = null)
    {
        if ($key !== null) {
            $locale = self::get($key);
            if ($locale) {
                self::$global = $locale;
            }
        }

        return self::globalLocale_()->abbr;
    }

    /**
     * moment.defineLocale(): create a locale from a config array (settings as in Locale, plus an
     * optional 'parentLocale' name) or register a ready Locale object; null removes it.
     *
     * @return Locale|null
     */
    public static function define($name, $config = null)
    {
        $name = self::normalize($name);
        if ($config === null) {
            unset(self::$locales[$name]);

            return null;
        }

        if ($config instanceof Locale) {
            $locale = $config;
        } else {
            // an existing locale of this name, or the parentLocale, is what the new settings are merged over
            $existing = self::load($name);
            $parent = $existing;
            if (!$parent && isset($config['parentLocale'])) {
                $parent = self::find($config['parentLocale']);
            }
            $locale = self::derive($parent, $config);
            if ($parent && !$existing) {
                $locale->parentLocale = $parent->abbr;
            }
        }
        $locale->abbr = $name;
        self::$locales[$name] = $locale;
        self::$global = $locale;

        return $locale;
    }

    /**
     * moment.updateLocale(): change settings of an existing locale (or start one from the
     * defaults). Passing null undoes the last update of that name.
     *
     * @return Locale|null
     */
    public static function update($name, $config = null)
    {
        $name = self::normalize($name);

        if ($config === null) {
            if (!empty(self::$previous[$name])) {
                self::$locales[$name] = array_pop(self::$previous[$name]);
                if (self::$global && self::$global->abbr === $name) {
                    self::$global = self::$locales[$name]; // the restored locale is the global one again
                }
            } else {
                unset(self::$locales[$name]);
            }

            return self::load($name);
        }

        $current = self::load($name);
        $locale = self::derive($current, $config);
        $locale->abbr = $name;
        if ($current) {
            self::$previous[$name][] = $current;
        }
        self::$locales[$name] = $locale;
        self::$global = $locale;

        return $locale;
    }

    /** A locale made of $parent (or of the defaults when null) with $config merged over it. */
    protected static function derive($parent, array $config)
    {
        if ($parent === null) {
            return new Locale($config);
        }
        if (get_class($parent) === __NAMESPACE__ . '\\Locale') {
            return new Locale(Locale::mergeConfigs($parent->getConfig(), $config));
        }

        return new Composite($parent, $config);
    }

    /** moment.locales(): the names of the locales loaded so far */
    public static function names()
    {
        self::globalLocale_();

        return array_keys(self::$locales);
    }
}
