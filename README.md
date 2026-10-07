# Moment for PHP

[![Latest Stable Version](https://poser.pugx.org/wilkques/moment/v/stable)](https://packagist.org/packages/wilkques/moment)
[![License](https://poser.pugx.org/wilkques/moment/license)](https://packagist.org/packages/wilkques/moment)

English | [繁體中文](README_ZH.md)

## Notice

1. A port of [moment.js](https://momentjs.com) (2.30.1) and moment-timezone (0.6.5) to PHP, for when the PHP side of an app has to print, parse and calculate dates **exactly** like the JavaScript side: the same format tokens, `add()` / `diff()` / `startOf()` semantics, durations, relative-time wording, all 138 locales and the same time zone data.
1. Moments are **mutable**, like in moment.js: `add()`, `startOf()`, `month(3)` and friends change the object and return it. Use `copy()` (moment's `clone()`) to keep the original.
1. Months are **0-based**, as in JavaScript.
1. If you don't need that parity, [Carbon](https://carbon.nesbot.com) is the better-known choice.

## ENV

1. php >= 5.3, 32-bit or 64-bit. Timestamps are whole milliseconds: `int` on 64-bit, a `float` holding a whole number on 32-bit (a float is exact up to 2^53, JavaScript's own limit is 8.64e15), so `valueOf()` and `unix()` can return a float there.
1. No required extensions or packages. Optional:
    - `ext-mbstring`: without it, matching upper-case non-ASCII names when parsing (`parse('MÄRZ', 'MMMM')`) uses a built-in lower-casing table for Latin, Greek, Cyrillic, Armenian and Georgian only.
    - `ext-intl`: lets `Tz::guess()` score the host zone's abbreviation from ICU names, as JavaScript does.
    - `ext-zlib`: only to run the gzipped fixtures of the test suite.

## How to use

1. Via Composer
    `composer require wilkques/moment`

    ```php

    require "vendor/autoload.php";
    ```

1. Via PHP require (without Composer)

    ```php

    spl_autoload_register(function ($class) {
        $prefix = 'Wilkques\\Moment\\';

        if (strpos($class, $prefix) === 0) {
            require "path/to/moment/src/" . str_replace('\\', '/', substr($class, strlen($prefix))) . ".php";
        }
    });
    ```

1. start
    ```php

    use Wilkques\Moment\Moment;

    $m = Moment::utc('2024-01-31T12:34:56Z');

    $m->copy()->add(1, 'month')->format('YYYY-MM-DD');   // 2024-02-29  (clamped, not March 2nd)
    $m->format('dddd, MMMM Do YYYY, h:mm:ss a');          // Wednesday, January 31st 2024, 12:34:56 pm
    $m->copy()->startOf('isoWeek')->format('L');          // 01/29/2024
    $m->diff('2024-03-31', 'months');                     // -2
    $m->copy()->locale('ru')->format('LLLL');             // среда, 31 января 2024 г., 12:34

    Moment::duration(array('hours' => 25, 'minutes' => 61))->humanize(true);   // in a day
    ```

## Methods

### Create

1. `create` (moment `moment()`): now, a number of milliseconds, a string, or another moment

    ```php

    use Wilkques\Moment\Moment;

    Moment::create();                              // now
    Moment::create(1718445600000);                 // milliseconds since the epoch
    Moment::create('2024-01-31');                  // ISO 8601
    Moment::create('2024-01-31T12:34:56+09:00');   // converted to the local zone
    Moment::create('Jan 5, 2024 10:30 PM');        // anything else goes to the new Date(string) parser

    // with the PHP default time zone Asia/Taipei:
    // Moment::create('2024-01-31')->format();                  => '2024-01-31T00:00:00+08:00'
    // Moment::create('2024-01-31T12:34:56+09:00')->format();   => '2024-01-31T11:34:56+08:00'
    // Moment::create('Jan 5, 2024 10:30 PM')->format();        => '2024-01-05T22:30:00+08:00'
    ```

1. with a format, strict mode, or a list of formats

    ```php

    Moment::create('31/01/2024', 'DD/MM/YYYY')->format('YYYY-MM-DD');
    // output: 2024-01-31

    Moment::create('31/02/2024', 'DD/MM/YYYY', true)->isValid(); // third argument: strict
    // output: false

    Moment::create('2024-01-31', array('DD/MM/YYYY', 'YYYY-MM-DD'))->format('YYYY-MM-DD'); // the best match wins
    // output: 2024-01-31

    Moment::create('2024-01-31', null, 'fr')->format('dddd D MMMM'); // locale as the third argument
    // output: mercredi 31 janvier
    ```

1. from an array or an object-like array (**months are 0-based**, like JavaScript), or a `DateTime`

    ```php

    Moment::create(array(2024, 0, 31, 12, 34, 56, 789))->format('YYYY-MM-DD HH:mm:ss.SSS');
    // output: 2024-01-31 12:34:56.789

    Moment::create(array('year' => 2024, 'month' => 0, 'day' => 31))->format('YYYY-MM-DD');
    // output: 2024-01-31

    Moment::create(new DateTime('2024-01-31 08:00 UTC'))->format();
    // output: 2024-01-31T16:00:00+08:00
    ```

1. `utc`, `unix`, `tz`, `parseZone`

    ```php

    Moment::utc('2024-01-31T12:34:56Z')->format();
    // output: 2024-01-31T12:34:56Z

    Moment::unix(1706704496)->utc()->format();   // seconds
    // output: 2024-01-31T12:34:56Z

    Moment::tz('2024-03-10T01:30:00', 'America/New_York')->format();   // read in that zone
    // output: 2024-03-10T01:30:00-05:00

    Moment::tz('2024-03-10 08:00', 'Asia/Tokyo', 'YYYY-MM-DD HH:mm')->format();   // zone, then format
    // output: 2024-03-10T08:00:00+09:00

    Moment::parseZone('2024-01-31T12:34:56+05:30')->format();   // keeps the offset in the string
    // output: 2024-01-31T12:34:56+05:30
    ```

1. `invalid`, `isValid`, `invalidAt`, `parsingFlags`

    ```php

    Moment::invalid()->isValid();          // false
    Moment::create(null)->isValid();        // false
    Moment::create('nope')->isValid();      // false

    $m = Moment::create('2024-02-30');
    $m->isValid();      // false
    $m->invalidAt();    // 2 (the day is the part that overflowed: 0 year, 1 month, 2 day, 3 hour ...)

    $flags = $m->parsingFlags();
    $flags['overflow']; // 2
    ```

### Get / Set

1. getters: with no argument they read, with an argument they set and return the moment

    ```php

    $m = Moment::utc('2024-01-31T12:34:56.789Z');

    array($m->year(), $m->month(), $m->date(), $m->hour(), $m->minute(), $m->second(), $m->millisecond());
    // output: [2024, 0, 31, 12, 34, 56, 789]

    array($m->day(), $m->weekday(), $m->isoWeekday());
    // output: [3, 3, 3]

    array($m->dayOfYear(), $m->week(), $m->isoWeek(), $m->weekYear(), $m->quarter());
    // output: [31, 5, 5, 2024, 1]

    array($m->daysInMonth(), $m->isLeapYear(), $m->weeksInYear());
    // output: [31, true, 52]

    array($m->valueOf(), $m->unix());
    // output: [1706704496789, 1706704496]

    array($m->eraName(), $m->eraAbbr(), $m->eraYear());
    // output: ["Anno Domini", "AD", 2024]
    ```

1. setters (they change the moment): values overflow like JavaScript's `Date`, a day-of-month is clamped when the month changes

    ```php

    Moment::utc('2024-01-31')->month(1)->format('YYYY-MM-DD');        // 2024-02-29
    Moment::utc('2024-01-31')->date(35)->format('YYYY-MM-DD');         // 2024-02-04
    Moment::utc('2024-01-01')->dayOfYear(60)->format('YYYY-MM-DD');    // 2024-02-29
    Moment::utc('2024-01-31')->isoWeekday(7)->format('YYYY-MM-DD dddd'); // 2024-02-04 Sunday
    Moment::utc('2024-01-31T12:00')->hour(5)->format();                // 2024-01-31T05:00:00Z
    ```

1. `get` and `set` take a unit name; `set` also takes an array

    ```php

    $m->get('quarter');
    // output: 1

    Moment::utc('2024-01-31T12:00')->set('hour', 5)->format();
    // output: 2024-01-31T05:00:00Z

    Moment::utc('2024-01-31')->set(array('year' => 2020, 'month' => 1))->format('YYYY-MM-DD');
    // output: 2020-02-29

    Moment::normalizeUnits('M');
    // output: month
    ```

### Manipulate

1. `add` and `subtract` (the moment changes; **month and year additions clamp to the end of the month**)

    ```php

    Moment::utc('2024-01-31')->add(1, 'month')->format('YYYY-MM-DD');
    // output: 2024-02-29

    Moment::utc('2024-01-31T00:00')->add(array('days' => 1, 'hours' => 2))->format();
    // output: 2024-02-01T02:00:00Z

    Moment::utc('2024-01-01')->add(Moment::duration(1, 'w'))->format('YYYY-MM-DD');
    // output: 2024-01-08

    Moment::utc('2024-02-29')->subtract(1, 'y')->format('YYYY-MM-DD');
    // output: 2023-02-28
    ```

1. `startOf` and `endOf`

    ```php

    Moment::utc('2024-01-31T12:34')->startOf('isoWeek')->format();
    // output: 2024-01-29T00:00:00Z

    Moment::utc('2024-01-31T12:34')->endOf('month')->format('YYYY-MM-DD HH:mm:ss.SSS');
    // output: 2024-01-31 23:59:59.999

    Moment::utc('2024-05-20')->startOf('quarter')->format('YYYY-MM-DD');
    // output: 2024-04-01
    ```

1. moments are **mutable**: use `copy()` (moment's `clone()`) to keep the original

    ```php

    $orig = Moment::utc('2024-01-31');
    $next = $orig->copy()->add(1, 'd');

    array($orig->format('YYYY-MM-DD'), $next->format('YYYY-MM-DD'));
    // output: ["2024-01-31", "2024-02-01"]

    $orig->add(1, 'd');   // no copy: $orig itself moves
    $orig->format('YYYY-MM-DD');
    // output: 2024-02-01
    ```

1. `utc`, `local`, `utcOffset`, `tz` (the second argument keeps the wall-clock time and moves the instant)

    ```php

    Moment::utc('2024-01-31T12:00')->utcOffset(480)->format();
    // output: 2024-01-31T20:00:00+08:00

    Moment::utc('2024-01-31T12:00')->utcOffset('+05:30', true)->format();
    // output: 2024-01-31T12:00:00+05:30

    Moment::utc('2024-01-31T12:00')->local()->format();   // PHP's date_default_timezone_get()
    // output: 2024-01-31T20:00:00+08:00

    Moment::utc('2024-01-31T12:00')->tz('Asia/Tokyo')->format();
    // output: 2024-01-31T21:00:00+09:00

    Moment::utc('2024-01-31T12:00')->tz('Asia/Tokyo', true)->format();
    // output: 2024-01-31T12:00:00+09:00
    ```

### Display

1. `format`

    ```php

    $m = Moment::utc('2024-01-31T12:34:56.789Z');

    $m->format();
    // output: 2024-01-31T12:34:56Z

    $m->format('LLLL');
    // output: Wednesday, January 31, 2024 12:34 PM

    $m->format('dddd, MMMM Do YYYY, h:mm:ss a');
    // output: Wednesday, January 31st 2024, 12:34:56 pm

    $m->format('[Q]Q W gggg Z ZZ X x');   // text in [brackets] is kept as is
    // output: Q1 5 2024 +00:00 +0000 1706704496 1706704496789
    ```

1. format tokens (all of moment's)

    ```text

    YYYY YY Y        year                 MMMM MMM MM M Mo   month
    Q Qo              quarter              DD D Do            day of month
    DDDD DDD DDDo     day of year          dddd ddd dd d do e E  weekday
    W WW Wo / w ww wo ISO week / week      GGGG gggg          ISO week-year / week-year
    HH H hh h kk k    hour                 mm m / ss s        minute / second
    S SS SSS ...      fraction             a A                am / pm
    Z ZZ              offset +08:00 +0800  z zz               zone abbreviation / name
    X x               unix seconds / ms    N NN NNN NNNN NNNNN y yy yyy yyyy yo   era
    LT LTS L LL LLL LLLL l ll lll llll     locale formats       [text]  escaped text
    ```

1. `toISOString`, `toJSON`, `json_encode`, `toArray`, `toObject`, `toDate`, `toString`, `inspect`

    ```php

    $m->toISOString();
    // output: 2024-01-31T12:34:56.789Z

    Moment::parseZone('2024-01-31T12:34:56+05:30')->toISOString(true);   // true keeps the offset
    // output: 2024-01-31T12:34:56.000+05:30

    json_encode(array('at' => $m));   // PHP 5.4+: Moment and Duration implement JsonSerializable
    // output: {"at":"2024-01-31T12:34:56.789Z"}

    $m->toArray();
    // output: [2024, 0, 31, 12, 34, 56, 789]

    $m->toObject();
    // output: ['years' => 2024, 'months' => 0, 'date' => 31, 'hours' => 12, 'minutes' => 34, 'seconds' => 56, 'milliseconds' => 789]

    $m->toDate()->format('Y-m-d\TH:i:s.uP');   // a DateTime, milliseconds kept
    // output: 2024-01-31T12:34:56.789000+00:00

    Moment::invalid()->toDate()->format('Y');   // an InvalidDate: a DateTime whose isValid() is false
    // output: Invalid Date

    $m->toString();
    // output: Wed Jan 31 2024 12:34:56 GMT+0000

    $m->inspect();
    // output: moment.utc("2024-01-31T12:34:56.789+00:00")
    ```

1. `fromNow`, `toNow`, `from`, `to` (the first argument of `fromNow` / `from` / `to` drops the suffix when `true`)

    ```php

    // "now" is 2024-06-15T10:00:00Z here (see Moment::setNow)

    Moment::utc('2024-06-12')->fromNow();       // 3 days ago
    Moment::utc('2024-06-12')->fromNow(true);   // 3 days
    Moment::utc('2024-06-12')->toNow();         // in 3 days

    Moment::utc('2024-01-01')->from(Moment::utc('2024-03-01'));        // 2 months ago
    Moment::utc('2024-01-01')->to(Moment::utc('2024-03-01'), true);    // 2 months
    ```

1. `calendar`

    ```php

    Moment::utc('2024-06-14T09:00')->calendar();
    // output: Yesterday at 9:00 AM

    Moment::utc('2024-06-20T09:00')->calendar();
    // output: Thursday at 9:00 AM

    Moment::utc('2024-06-15T09:00')->calendar(null, array('sameDay' => '[Today at] HH:mm'));   // second argument: your own formats
    // output: Today at 09:00
    ```

1. `diff` (truncates toward zero; the third argument `true` keeps the fraction)

    ```php

    Moment::utc('2024-03-31')->diff(Moment::utc('2024-01-31'), 'months');
    // output: 2

    Moment::utc('2024-02-01T12:00')->diff(Moment::utc('2024-01-31'), 'days', true);
    // output: 1.5

    Moment::utc('2024-01-01T00:00:01')->diff(Moment::utc('2024-01-01'));   // milliseconds by default
    // output: 1000
    ```

### Query

1. `isBefore`, `isAfter`, `isSame`, `isSameOrBefore`, `isSameOrAfter`, `isBetween` (an optional unit, and for `isBetween` an inclusivity like `'[]'`)

    ```php

    $a = Moment::utc('2024-01-01');
    $b = Moment::utc('2024-06-01');

    array($a->isBefore($b), $a->isAfter($b), $a->isSame('2024-01-01'));
    // output: [true, false, false]

    Moment::utc('2024-01-31')->isSame(Moment::utc('2024-01-01'), 'month');   // compared at month granularity
    // output: true

    Moment::utc('2024-03-01')->isBetween($a, $b);          // exclusive by default
    // output: true

    $a->isBetween($a, $b, null, '[]');                      // inclusive of both ends
    // output: true
    ```

1. `isValid`, `isUtc`, `isLocal`, `isUtcOffset`, `isDST`, `zoneAbbr`, `zoneName`

    ```php

    array($a->isValid(), $a->isUtc(), $a->isLocal(), $a->isUtcOffset());
    // output: [true, true, false, true]

    Moment::tz('2024-07-01', 'America/New_York')->isDST();
    // output: true

    Moment::tz('2024-07-01', 'America/New_York')->zoneAbbr();
    // output: EDT
    ```

1. `min`, `max`, `isMoment`

    ```php

    Moment::min($a, $b)->format('YYYY-MM-DD');   // 2024-01-01
    Moment::max($a, $b)->format('YYYY-MM-DD');   // 2024-06-01
    Moment::isMoment($a);                         // true
    ```

### Duration

1. create: numbers, arrays, ISO 8601, `hh:mm:ss`, or a pair of dates

    ```php

    Moment::duration(2, 'weeks')->asDays();                                   // 14.0
    Moment::duration(array('hours' => 25, 'minutes' => 61))->toISOString();   // PT26H1M
    Moment::duration('P1Y2M3DT4H5M6S')->toISOString();                         // P1Y2M3DT4H5M6S
    Moment::duration('12:30:15')->asSeconds();                                 // 45015.0
    Moment::duration(array('from' => '2024-01-01', 'to' => '2024-03-01'))->asDays();   // 61.0
    ```

1. getters, `as` / `asHours` ...  (`as()` and `clone()` work as methods on PHP 7+, and always as `toUnit()` / `copy()`)

    ```php

    $d = Moment::duration(array('hours' => 25, 'minutes' => 61));

    array($d->days(), $d->hours(), $d->minutes());
    // output: [1, 2, 1]

    array($d->asHours(), $d->asDays());
    // output: [26.016666666666666, 1.0840277777777778]

    $d->as('minutes');
    // output: 1561.0

    $d->get('hours');
    // output: 2
    ```

1. `add`, `subtract`, `abs`

    ```php

    Moment::duration(1, 'h')->add(30, 'm')->subtract(10, 's')->toISOString();
    // output: PT1H29M50S

    Moment::duration(-90, 'm')->abs()->asMinutes();
    // output: 90.0
    ```

1. `humanize` and the thresholds

    ```php

    $d->humanize();
    // output: a day

    $d->humanize(true);
    // output: in a day

    Moment::relativeTimeThreshold('s');            // read: 45
    Moment::relativeTimeThreshold('s', 60);        // write
    Moment::duration(50, 's')->humanize();
    // output: a few seconds

    Moment::duration(3, 'days')->locale('fr')->humanize(true);
    // output: dans 3 jours
    ```

### Locale

1. all 138 moment locales are included and loaded the first time they are used

    ```php

    Moment::utc('2024-01-31')->locale('fr')->format('LLLL');
    // output: mercredi 31 janvier 2024 00:00

    Moment::utc('2024-06-12')->locale('ru')->fromNow();
    // output: 3 дня назад

    Moment::utc('2024-01-31')->locale('ja')->format('YYYY年M月D日 dddd');
    // output: 2024年1月31日 水曜日
    ```

1. the global locale (`Moment::locale()` reads, `Moment::locale($name)` sets), `localeData`, and the name lists

    ```php

    Moment::locale('de');
    Moment::utc('2024-01-31')->format('dddd, D. MMMM');
    // output: Mittwoch, 31. Januar

    Moment::locale();
    // output: de

    Moment::locale('en');

    Moment::localeData('fr')->firstDayOfWeek();
    // output: 1

    Moment::months();                                              // ["January", "February", ...]
    Moment::monthsShort(2);                                        // "Mar" (an index)
    Moment::months('MMMM', 0);                                     // "January" (a format, then an index)
    Moment::weekdays(true);                                        // locale-sorted: Sunday first in en
    Moment::weekdaysMin();                                         // ["Su", "Mo", ...]
    ```

1. `defineLocale`, `updateLocale` (passing `null` undoes it), `parentLocale`. Settings that are functions in moment.js are PHP callables

    ```php

    Moment::defineLocale('x-demo', array(
        'months'         => array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l'),
        'monthsShort'    => array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l'),
        'weekdays'       => array('s', 'm', 't', 'w', 't2', 'f', 's2'),
        'weekdaysShort'  => array('s', 'm', 't', 'w', 't2', 'f', 's2'),
        'weekdaysMin'    => array('s', 'm', 't', 'w', 't2', 'f', 's2'),
        'longDateFormat' => array('LT' => 'HH:mm', 'LTS' => 'HH:mm:ss', 'L' => 'DD.MM.YYYY', 'LL' => 'D MMMM YYYY', 'LLL' => 'D MMMM YYYY HH:mm', 'LLLL' => 'dddd D MMMM YYYY HH:mm'),
        'relativeTime'   => array('future' => 'in %s', 'past' => '%s ago', 's' => 'a few secs', 'ss' => '%d secs', 'm' => 'a min', 'mm' => '%d mins',
                                  'h' => 'an hour', 'hh' => '%d hours', 'd' => 'a day', 'dd' => '%d days', 'M' => 'a month', 'MM' => '%d months', 'y' => 'a year', 'yy' => '%d years'),
        'ordinal'        => function ($number) { return $number . '.'; },
        'week'           => array('dow' => 1, 'doy' => 4),
    ));

    Moment::utc('2024-03-05')->locale('x-demo')->format('dddd, D MMMM YYYY [#]Do');
    // output: t, 5 c 2024 #5.

    Moment::updateLocale('x-demo', array('months' => array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L')));
    Moment::utc('2024-03-05')->locale('x-demo')->format('MMMM / MMM');
    // output: C / c

    Moment::updateLocale('x-demo', null);   // undo the update
    Moment::defineLocale('x-demo', null);   // remove the locale

    Moment::defineLocale('x-child', array('parentLocale' => 'fr', 'months' => array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L')));
    Moment::utc('2024-03-05')->locale('x-child')->format('MMMM | dddd');   // the rest comes from fr
    // output: C | mardi
    ```

1. your own locale as a class: extend `Wilkques\Moment\Locale\Locale` (see `src/Locale/Locales/*.php` for 138 examples)

    ```php

    // the class has a static config() array and overrides a method wherever the JavaScript locale has a function:
    // ordinal(), meridiem(), relativeTime(), calendar(), months(), weekdays(), preparse(), postformat() ...
    ```

### Time zone

1. moment-timezone's own data (IANA 2026e) is bundled: `Tz::names()`, `Tz::zone()`, `Tz::countries()`, `Tz::zonesForCountry()`

    ```php

    use Wilkques\Moment\Timezone\Tz;

    Tz::zone('Asia/Taipei')->utcOffset(Moment::now());   // minutes, positive WEST of UTC, as in moment-timezone
    // output: -480

    Tz::zone('America/New_York')->abbr(Moment::now());
    // output: EDT

    Tz::zone('Nope/Zone');                               // unknown name
    // output: null

    Tz::zonesForCountry('TW');
    // output: ["Asia/Taipei"]

    Tz::zonesForCountry('JP', true);                     // with the current offset
    // output: [['name' => 'Asia/Tokyo', 'offset' => -540]]
    ```

1. invalid and ambiguous wall-clock times (spring forward / fall back)

    ```php

    Moment::tz('2024-03-10T02:30:00', 'America/New_York')->format();   // 02:30 doesn't exist: moved forward
    // output: 2024-03-10T03:30:00-04:00

    Tz::$moveInvalidForward = false;
    Moment::tz('2024-03-10T02:30:00', 'America/New_York')->format();
    // output: 2024-03-10T01:30:00-05:00

    Moment::tz('2024-11-03T01:30:00', 'America/New_York')->format();    // 01:30 happens twice: the earlier one
    // output: 2024-11-03T01:30:00-04:00

    Tz::$moveAmbiguousForward = true;
    Moment::tz('2024-11-03T01:30:00', 'America/New_York')->format();
    // output: 2024-11-03T01:30:00-05:00
    ```

1. `Tz::setDefault`, `Tz::guess`, `Tz::add` (your own zone, in moment-timezone's packed format), `Tz::link`, `Tz::load`

    ```php

    Tz::setDefault('Asia/Tokyo');   // like moment.tz.setDefault(): strings without an offset are read in that zone
    Moment::create('2024-01-31T12:00:00')->format();
    // output: 2024-01-31T12:00:00+09:00

    Tz::setDefault(null);

    Tz::guess();                      // the host's zone: PHP's default zone name when known, else offset scoring
    // output: Asia/Taipei

    Tz::add('Custom/Zone|CST CDT|60 50|0101|1:00:00 2000');
    ```

### Hooks and extension

1. `macro` (moment's `moment.fn.foo = ...`)

    ```php

    Moment::macro('endOfNoon', function ($m, $minutes) {
        return $m->hour(12)->minute($minutes);
    });

    Moment::utc('2024-01-31T01:00')->endOfNoon(45)->format('HH:mm');
    // output: 12:45
    ```

1. static properties holding a callable: `$calendarFormat`, `$parseTwoDigitYear`, `$createFromInputFallback`, `$updateOffset`, `$deprecationHandler`, `$suppressDeprecationWarnings`

    ```php

    Moment::$calendarFormat = function ($m, $now) {
        return 'sameElse';
    };
    Moment::utc('2024-06-14')->calendar();
    // output: 14/06/2024
    Moment::$calendarFormat = null;

    Moment::$parseTwoDigitYear = function ($s) {
        return (int) $s + ($s > 30 ? 1900 : 2000);
    };
    Moment::create('45', 'YY')->format('YYYY');
    // output: 1945
    Moment::$parseTwoDigitYear = null;

    Moment::$createFromInputFallback = function ($string, $useUtc) {   // strings moment can't parse itself
        return array('ms' => 0);                                       // a parse result, or null for invalid
    };
    Moment::create('Jan 5, 2024')->valueOf();
    // output: 0
    Moment::$createFromInputFallback = null;

    $calls = array();
    Moment::$updateOffset = function ($m, $keepTime) use (&$calls) {   // null: a new moment, true: keep the wall time, false: move the instant
        $calls[] = $keepTime;
    };
    Moment::utc('2024-01-31')->add(1, 'day')->add(1, 'hour');
    Moment::$updateOffset = null;
    // $calls: [null, true, false]
    ```

1. constants and defaults: `Moment::VERSION`, `Moment::ISO_8601`, `Moment::RFC_2822`, `Moment::$HTML5_FMT`, `Moment::$defaultFormat`

    ```php

    Moment::VERSION;                  // 2.30.1 (the moment.js version this follows)
    Moment::$HTML5_FMT['DATE'];        // YYYY-MM-DD
    Moment::$defaultFormat;            // YYYY-MM-DDTHH:mm:ssZ
    ```

1. `Moment::now()` and `Moment::setNow()`: freeze "now" in your tests (`moment.now = ...` in JavaScript)

    ```php

    Moment::setNow(1718445600000);   // 2024-06-15T10:00:00Z
    Moment::now();
    // output: 1718445600000

    Moment::setNow(null);             // back to the clock
    ```

### Deprecated

1. deprecated spellings work and raise `E_USER_DEPRECATED` once each, like moment's console warnings: `lang()`, `zone()`, instance `min()` / `max()`, `moment.lang`, `moment.langData`, `add('days', 1)`

    ```php

    Moment::$suppressDeprecationWarnings = true;   // silence them

    Moment::utc('2024-01-31')->add('days', 1)->format('YYYY-MM-DD');
    // output: 2024-02-01
    ```

### Parsing like `new Date(string)`

When a string is not ISO 8601, not RFC 2822 and no format is given, moment hands it to JavaScript's `Date`. This library does the same through a port of V8's date parser (`Parse\V8DateParser`), so `Moment::create('Jan 5, 2024 10:30 PM')` or `'2024/01/05'` work as in Chrome and Node. Like moment, it isn't used in strict mode, and ISO/RFC strings that look right but are invalid stay invalid.

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
