# Moment for PHP

English | [繁體中文](README_ZH.md)

A port of [moment.js](https://momentjs.com) (2.30.1) and moment-timezone (0.6.5) to PHP, for when the PHP side of an app has to print, parse and calculate dates **exactly** like the JavaScript side: the same format tokens, `add()` / `diff()` / `startOf()` semantics, durations, relative-time wording, all 138 locales and the same time zone data. Kept compatible all the way back to **PHP 5.3**, with no dependencies.

If you don't need that parity, [Carbon](https://carbon.nesbot.com) is the better-known choice.

## Requirements

- PHP >= 5.3, 32-bit or 64-bit. Timestamps are whole milliseconds: `int` on 64-bit, a `float` holding a whole number on 32-bit (a float is exact up to 2^53, JavaScript's own limit is 8.64e15), so `valueOf()` and `unix()` can return a float there.
- Nothing else is required. `ext-mbstring` is recommended: without it, matching upper-case non-ASCII names when parsing (`parse('MÄRZ', 'MMMM')`) uses a built-in lower-casing table for Latin, Greek, Cyrillic, Armenian and Georgian only.

## Installation

```
composer require wilkques/moment
```

## Usage

```php
use Wilkques\Moment\Moment;

$m = Moment::utc('2024-01-31T12:34:56Z');

$m->copy()->add(1, 'month')->format('YYYY-MM-DD');   // "2024-02-29"  (clamped, not March 2nd)
$m->format('dddd, MMMM Do YYYY, h:mm:ss a');          // "Wednesday, January 31st 2024, 12:34:56 pm"
$m->copy()->startOf('isoWeek')->format('L');          // "01/29/2024"
$m->diff('2024-03-31', 'months');                     // -2
$m->fromNow();                                        // "2 years ago"
$m->copy()->locale('ru')->format('LLLL');             // "среда, 31 января 2024 г., 12:34"

Moment::tz('2024-03-10T01:30:00', 'America/New_York')->add(1, 'hour')->format();
// "2024-03-10T03:30:00-04:00"   (02:30 doesn't exist that night)

Moment::duration(array('hours' => 25, 'minutes' => 61))->humanize(true);   // "in a day"
Moment::locale('fr');                                                      // global locale, like moment.locale('fr')
```

Moments are **mutable**, like in moment.js: `add()`, `startOf()`, `month(3)` and friends change the object and return it. Use `copy()` (moment's `clone()`) to keep the original.

Input can be `null` (invalid, like `moment(null)`), nothing (now), an int/float of milliseconds, another `Moment`, a `DateTime`, a list `array(2024, 0, 31, 12, 34, 56, 789)` (month is 0-based), an object-like array `array('year' => 2024, 'month' => 0, 'day' => 31)`, or a string. Formats and strict mode work as in moment: `Moment::create('31/01/2024', 'DD/MM/YYYY', true)`, or an array of formats.

## moment.js → PHP

Almost everything keeps its name. PHP can't give a static function and an instance method the same name, and `as` / `clone` are reserved words in PHP 5, so those are served through `__call` / `__callStatic`: both spellings work.

| moment.js | PHP |
| --- | --- |
| `moment(x)` | `Moment::create($x)` |
| `moment.utc(x)` | `Moment::utc($x)` (or `createUtc`) |
| `moment.tz(x, zone)` | `Moment::tz($x, $zone)` (or `createInZone`) |
| `moment.unix(s)` | `Moment::unix($s)` (or `fromUnix`) |
| `m.clone()` | `$m->clone()` (or `copy()`) |
| `m.utc()`, `m.unix()`, `m.tz()`, `m.locale()`, `m.localeData()`, `m.months()` | same names on instances |
| `moment.locale('fr')`, `moment.localeData()`, `moment.defineLocale()`, `moment.updateLocale()`, `moment.locales()` | `Moment::locale('fr')` etc. |
| `moment.months()`, `moment.weekdays(true)`, … | `Moment::months()`, `Moment::weekdays(true)`, … |
| `moment.min()`, `moment.max()` | `Moment::min()`, `Moment::max()` |
| `moment.duration(...)`, `d.as('hours')`, `d.clone()` | `Moment::duration(...)`, `$d->as('hours')`, `$d->clone()` |
| `moment.parseZone(s)` / `m.parseZone()` | `Moment::parseZone($s)` / `$m->parseZone()` |
| `moment.HTML5_FMT.DATE`, `moment.defaultFormat`, `moment.ISO_8601` | `Moment::$HTML5_FMT['DATE']`, `Moment::$defaultFormat`, `Moment::ISO_8601` |
| `moment.relativeTimeThreshold()`, `relativeTimeRounding()` | same, on `Moment` (or `Duration`) |
| `moment.parseTwoDigitYear`, `calendarFormat`, `createFromInputFallback`, `deprecationHandler`, `suppressDeprecationWarnings` | static properties of the same name on `Moment`, holding a callable |
| `moment.tz.names()`, `.zone()`, `.add()`, `.link()`, `.load()`, `.guess()`, `.setDefault()`, `.zonesForCountry()`, `.moveInvalidForward` … | `Wilkques\Moment\Timezone\Tz::names()`, `::zone()`, `::add()`, … and `Tz::$moveInvalidForward` |
| `moment.fn.foo = function () {…}` | `Moment::macro('foo', function ($m, …) {…})`, then `$m->foo(…)` |

Deprecated spellings (`lang()`, `zone()`, instance `min()` / `max()`, `moment.lang`, `moment.langData`, `add('days', 1)`) work and raise `E_USER_DEPRECATED` once each, like moment's console warnings. Set `Moment::$suppressDeprecationWarnings = true` to silence them.

## What's covered

- **Core**: create/parse (ISO 8601, RFC 2822, ASP.NET `/Date()/`, custom formats, strict, multiple formats, and the fallback to `new Date(string)`), every getter/setter, `add`/`subtract`, `startOf`/`endOf`, comparisons, `diff`, `format` with every token, `toISOString`, `toArray`, `toObject`, `parsingFlags`, `invalidAt`, `creationData`, `inspect`, eras, `isDST`, `hasAlignedHourOffset`.
- **JSON**: `json_encode()` of a `Moment` or `Duration` gives what `JSON.stringify` gives (`toJSON()`; null for an invalid moment), on PHP 5.4+ (5.3 has no JsonSerializable: call `toJSON()`).
- **Duration**: from numbers, objects, ISO 8601, ASP.NET and `array('from' => , 'to' => )`; `as*()`, getters, `add`/`subtract`/`abs`, `humanize()` with moment's thresholds, `toISOString()`.
- **Relative time**: `from`, `to`, `fromNow`, `toNow`, `calendar`.
- **Locales**: all 138 locales of moment 2.30.1 (`Wilkques\Moment\Locale\Locales\*`, loaded on first use). Your own: `Moment::defineLocale('xx', array(...))`, or a class extending `Wilkques\Moment\Locale\Locale`.
- **Time zones**: moment-timezone's own data (IANA 2026e) is bundled in `data/latest.php`, so results are the same on every PHP version and as in the browser. `utc()`, `local()`, `utcOffset()`, `tz()`, `Tz::setDefault()`, `Tz::add()` for your own zones. "Local" means PHP's `date_default_timezone_get()`.

### Parsing like `new Date(string)`

When a string is not ISO 8601, not RFC 2822 and no format is given, moment hands it to JavaScript's `Date`. This library does the same through a port of V8's date parser (`Parse\V8DateParser`), so `Moment::create('Jan 5, 2024 10:30 PM')` or `'2024/01/05'` work as in Chrome and Node. Like moment, it isn't used in strict mode, and ISO/RFC strings that look right but are invalid stay invalid.

## Known differences from moment.js

- `tz.guess()` answers from PHP's default time zone name when moment-timezone knows it (as it does with Intl's); otherwise it runs moment-timezone's offset-scoring search. With `ext-intl` the search scores the host abbreviation from ICU's English long names exactly as moment-timezone does with `toTimeString()` (a zone's name can differ between ICU releases, e.g. `Eire`); without it only offsets decide, which differs for a handful of legacy zones such as `CET`. `Tz::$guessUseAbbr = false` forces offsets only, `true` also uses PHP's own abbreviation when intl is missing.
- `Moment::$updateOffset` is called at the same points and with the same `keepTime` as moment's `updateOffset` hook. `Moment::$createFromInputFallback` receives `($string, $useUtc)` and returns a parse result such as `array('ms' => $ms)`, or null for invalid, rather than moment's `config` object.
- `Moment::locale($name)` / `Registry::get()` fall back to the global locale for an unknown name, as `moment.localeData()` does; `Registry::find()` returns null instead. `toDate()` on an invalid moment returns an `InvalidDate` (a `DateTime` subclass: `isValid()` is false, `getTimestamp()` is false, `format()` gives "Invalid Date"), the closest PHP has to JavaScript's Invalid Date.
- Time zone names given to `tz()` that moment-timezone doesn't know are ignored (as in moment-timezone, which logs an error).

## Tests

`tests/fixtures/` holds golden data produced by the real moment.js and moment-timezone: about 9,000 cases for the core, 138 locales × about 10,500 checks (including every relative-time key forced with thresholds, `calendar()` for every weekday, every day's ordinal, and each locale function called directly with inputs `humanize()` never produces: `relativeTime`, `calendar`, `ordinal`, `meridiem`, `meridiemHour`, `isPM`, month and weekday names for many format strings, `preparse`, `postformat`), 2,600 for the API, 1,400 for the fallback parser under four host time zones, 223 cross-product groups (every locale × five zones × DST edges, week rules for each `dow/doy`, `get`/`set` of every unit with overflow, `add`/`diff`/`startOf` across DST, offsets, `humanize` variants, odd format strings, comparisons) 7,000 seeded random operation chains and parse round trips, and 5,900 more random cases for `Duration` (every way to create one, `add`/`subtract`/`abs`/`clone`, all getters and `as()` units, `humanize` variants), `calendar()` with every argument shape and custom formats (strings and functions), and runtime locales (`defineLocale` with and without a parent, `updateLocale` and undoing it). `gaps` fixtures target behaviour the coverage report showed was never exercised: every method on invalid moments, era and compact-time parse tokens, string setters, number strings, extreme durations, locale lookup, which strings reach `Moment::$createFromInputFallback`, when `Moment::$updateOffset` is called and with which `keepTime`, `Tz::zonesForCountry()` with offsets, the `updateOffset` calls of `moment.tz()` for every kind of input, and V8's date parser on its own (about 300 `new Date(string)` strings). Larger random batches were run once against the same generator without finding further differences. The PHPUnit suites replay them against this library, together with hand-written tests.

```
cd tools && npm install                   # moment 2.30.1 + moment-timezone (only needed to regenerate fixtures)
node generate-fixtures.js                 # core fixtures
node generate-locale-fixtures.js          # per-locale fixtures
node generate-api-fixtures.js             # API fixtures (run with TZ=UTC)
node generate-fallback-fixtures.js        # new Date(string) fixtures
TZ=UTC node generate-cross-fixtures.js    # locale x zone, weeks, get/set, add/diff, offsets, ...
TZ=UTC node generate-extra-fixtures.js    # Duration, calendar() formats, defineLocale/updateLocale
TZ=UTC node generate-fuzz-fixtures.js     # seeded random chains (FUZZ_SEED / FUZZ_CHAINS / FUZZ_PARSE for bigger runs)
node generate-guess-fixtures.js           # tz.guess() for a host in every zone (offsets only)
node generate-guess-fixtures.js icu       # the same with English ICU names, so abbreviations count (needs ext-intl in PHP)
node generate-localefn-fixtures.js        # each locale's functions called directly
node generate-tzdata.js                   # data/latest.php from moment-timezone

vendor/bin/phpunit -c phpunit-higher.xml  # PHP 7.1 - 8.0
vendor/bin/phpunit -c phpunit-latest.xml  # PHP 8.1+ (PHPUnit 10+ config schema)
vendor/bin/phpunit -c phpunit-lower.xml   # PHP 5.3 - 5.6
tools/run-matrix.sh                       # every PHP version (and a 32-bit one) in docker, from /var/www/packages
php tools/locale-check.php ru de          # one locale, with details on mismatches
```

The locale fixtures are gzipped: the locale tests need the zlib extension and are skipped without it; the ICU guess test is skipped without ext-intl.

Line coverage of `src/` (PHP 8.4 with pcov, intl and mbstring): 99.2% for the core and 99.8% for the locales. What's left is code that only runs on other setups (PHP 5.3 fallbacks, a host zone moment-timezone doesn't know) or that no input reaches (branches ported from JavaScript that can't be triggered).

## License

MIT. Behaviour, locale data and time zone data are derived from [moment.js](https://github.com/moment/moment) and [moment-timezone](https://github.com/moment/moment-timezone) (MIT); the date parser follows [V8](https://v8.dev) (BSD).
