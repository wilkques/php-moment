# Moment for PHP

[![Latest Stable Version](https://poser.pugx.org/wilkques/moment/v/stable)](https://packagist.org/packages/wilkques/moment)
[![License](https://poser.pugx.org/wilkques/moment/license)](https://packagist.org/packages/wilkques/moment)

[English](README.md) | 繁體中文

## 注意事項

1. 把 [moment.js](https://momentjs.com)（2.30.1）與 moment-timezone（0.6.5）移植到 PHP。適合前後端都要顯示、解析、計算日期，而且結果必須和 JavaScript 端**完全一致**的情境：相同的 format token、`add()` / `diff()` / `startOf()` 語意、Duration、相對時間字串、全部 138 個 locale 與同一份時區資料。
1. 和 moment.js 一樣，Moment 是**可變**物件：`add()`、`startOf()`、`month(3)` 等都會修改並回傳自身。要保留原值請用 `copy()`（即 moment 的 `clone()`）。
1. 月份和 JavaScript 一樣**從 0 開始**。
1. 不需要和 moment 一致的話，[Carbon](https://carbon.nesbot.com) 是更常見的選擇。

## ENV

1. php >= 5.3，32 位元或 64 位元皆可。時間戳是整數毫秒：64 位元上是 `int`，32 位元上是存放整數值的 `float`（float 到 2^53 都精確，JavaScript 自己的上限是 8.64e15），所以那裡的 `valueOf()` 與 `unix()` 可能回傳 float。
1. 不需要任何擴充或套件。可選：
    - `ext-mbstring`：沒有它時，解析大寫的非 ASCII 名稱（`parse('MÄRZ', 'MMMM')`）會改用內建的小寫對照表，只涵蓋拉丁、希臘、斯拉夫、亞美尼亞與喬治亞文字。
    - `ext-intl`：讓 `Tz::guess()` 像 JavaScript 一樣，用 ICU 名稱替主機時區的縮寫計分。
    - `ext-zlib`：只有執行測試套件的 gzip fixtures 時需要。

## 如何使用

1. 透過 Composer
    `composer require wilkques/moment`

    ```php

    require "vendor/autoload.php";
    ```

1. 透過 PHP require（不用 Composer）

    ```php

    spl_autoload_register(function ($class) {
        $prefix = 'Wilkques\\Moment\\';

        if (strpos($class, $prefix) === 0) {
            require "path/to/moment/src/" . str_replace('\\', '/', substr($class, strlen($prefix))) . ".php";
        }
    });
    ```

1. 開始
    ```php

    use Wilkques\Moment\Moment;

    $m = Moment::utc('2024-01-31T12:34:56Z');

    $m->copy()->add(1, 'month')->format('YYYY-MM-DD');   // 2024-02-29（夾到月底，不是 3/2）
    $m->format('dddd, MMMM Do YYYY, h:mm:ss a');          // Wednesday, January 31st 2024, 12:34:56 pm
    $m->copy()->startOf('isoWeek')->format('L');          // 01/29/2024
    $m->diff('2024-03-31', 'months');                     // -2
    $m->copy()->locale('zh-tw')->format('LLLL');          // 2024年1月31日星期三 12:34

    Moment::duration(array('hours' => 25, 'minutes' => 61))->humanize(true);   // in a day
    ```

## 方法

### 建立

1. `create`（moment 的 `moment()`）：現在、毫秒數、字串或另一個 moment

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

1. 指定格式、strict 模式、或格式清單

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

1. 從陣列或物件式陣列（**月份從 0 開始**，同 JavaScript），或 `DateTime`

    ```php

    Moment::create(array(2024, 0, 31, 12, 34, 56, 789))->format('YYYY-MM-DD HH:mm:ss.SSS');
    // output: 2024-01-31 12:34:56.789

    Moment::create(array('year' => 2024, 'month' => 0, 'day' => 31))->format('YYYY-MM-DD');
    // output: 2024-01-31

    Moment::create(new DateTime('2024-01-31 08:00 UTC'))->format();
    // output: 2024-01-31T16:00:00+08:00
    ```

1. `utc`、`unix`、`tz`、`parseZone`

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

1. `invalid`、`isValid`、`invalidAt`、`parsingFlags`

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

### 取得 / 設定

1. getter：不帶參數是讀取，帶參數則設定並回傳自身

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

1. setter（會修改該 moment）：超出範圍的值像 JavaScript 的 `Date` 一樣進位；改月份時日期會夾到月底

    ```php

    Moment::utc('2024-01-31')->month(1)->format('YYYY-MM-DD');        // 2024-02-29
    Moment::utc('2024-01-31')->date(35)->format('YYYY-MM-DD');         // 2024-02-04
    Moment::utc('2024-01-01')->dayOfYear(60)->format('YYYY-MM-DD');    // 2024-02-29
    Moment::utc('2024-01-31')->isoWeekday(7)->format('YYYY-MM-DD dddd'); // 2024-02-04 Sunday
    Moment::utc('2024-01-31T12:00')->hour(5)->format();                // 2024-01-31T05:00:00Z
    ```

1. `get` 與 `set` 接受單位名稱；`set` 也能傳陣列

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

### 運算

1. `add` 與 `subtract`（會修改該 moment；**加減月、年時日期會夾到月底**）

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

1. `startOf` 與 `endOf`

    ```php

    Moment::utc('2024-01-31T12:34')->startOf('isoWeek')->format();
    // output: 2024-01-29T00:00:00Z

    Moment::utc('2024-01-31T12:34')->endOf('month')->format('YYYY-MM-DD HH:mm:ss.SSS');
    // output: 2024-01-31 23:59:59.999

    Moment::utc('2024-05-20')->startOf('quarter')->format('YYYY-MM-DD');
    // output: 2024-04-01
    ```

1. moment 是**可變**的：要保留原值請用 `copy()`（即 moment 的 `clone()`）

    ```php

    $orig = Moment::utc('2024-01-31');
    $next = $orig->copy()->add(1, 'd');

    array($orig->format('YYYY-MM-DD'), $next->format('YYYY-MM-DD'));
    // output: ["2024-01-31", "2024-02-01"]

    $orig->add(1, 'd');   // no copy: $orig itself moves
    $orig->format('YYYY-MM-DD');
    // output: 2024-02-01
    ```

1. `utc`、`local`、`utcOffset`、`tz`（第二個參數 `true` 表示保留牆上時間、移動時間點）

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

### 輸出

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

1. format token（moment 的全部 token）

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

1. `toISOString`、`toJSON`、`json_encode`、`toArray`、`toObject`、`toDate`、`toString`、`inspect`

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

1. `fromNow`、`toNow`、`from`、`to`（`fromNow` 的參數為 `true` 時不加「前／後」字樣）

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

1. `diff`（預設向零取整；第三個參數 `true` 保留小數）

    ```php

    Moment::utc('2024-03-31')->diff(Moment::utc('2024-01-31'), 'months');
    // output: 2

    Moment::utc('2024-02-01T12:00')->diff(Moment::utc('2024-01-31'), 'days', true);
    // output: 1.5

    Moment::utc('2024-01-01T00:00:01')->diff(Moment::utc('2024-01-01'));   // milliseconds by default
    // output: 1000
    ```

### 判斷

1. `isBefore`、`isAfter`、`isSame`、`isSameOrBefore`、`isSameOrAfter`、`isBetween`（可加單位；`isBetween` 還能指定 `'[]'` 之類的包含方式）

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

1. `isValid`、`isUtc`、`isLocal`、`isUtcOffset`、`isDST`、`zoneAbbr`、`zoneName`

    ```php

    array($a->isValid(), $a->isUtc(), $a->isLocal(), $a->isUtcOffset());
    // output: [true, true, false, true]

    Moment::tz('2024-07-01', 'America/New_York')->isDST();
    // output: true

    Moment::tz('2024-07-01', 'America/New_York')->zoneAbbr();
    // output: EDT
    ```

1. `min`、`max`、`isMoment`

    ```php

    Moment::min($a, $b)->format('YYYY-MM-DD');   // 2024-01-01
    Moment::max($a, $b)->format('YYYY-MM-DD');   // 2024-06-01
    Moment::isMoment($a);                         // true
    ```

### Duration

1. 建立：數字、陣列、ISO 8601、`hh:mm:ss`，或兩個日期

    ```php

    Moment::duration(2, 'weeks')->asDays();                                   // 14.0
    Moment::duration(array('hours' => 25, 'minutes' => 61))->toISOString();   // PT26H1M
    Moment::duration('P1Y2M3DT4H5M6S')->toISOString();                         // P1Y2M3DT4H5M6S
    Moment::duration('12:30:15')->asSeconds();                                 // 45015.0
    Moment::duration(array('from' => '2024-01-01', 'to' => '2024-03-01'))->asDays();   // 61.0
    ```

1. getter、`as` / `asHours` ...（`as()`、`clone()` 在 PHP 7 以上可直接當方法呼叫，所有版本都可用 `toUnit()` / `copy()`）

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

1. `add`、`subtract`、`abs`

    ```php

    Moment::duration(1, 'h')->add(30, 'm')->subtract(10, 's')->toISOString();
    // output: PT1H29M50S

    Moment::duration(-90, 'm')->abs()->asMinutes();
    // output: 90.0
    ```

1. `humanize` 與門檻

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

1. 內建 moment 的全部 138 個 locale，第一次使用時才載入

    ```php

    Moment::utc('2024-01-31')->locale('fr')->format('LLLL');
    // output: mercredi 31 janvier 2024 00:00

    Moment::utc('2024-06-12')->locale('ru')->fromNow();
    // output: 3 дня назад

    Moment::utc('2024-01-31')->locale('ja')->format('YYYY年M月D日 dddd');
    // output: 2024年1月31日 水曜日
    ```

1. 全域 locale（`Moment::locale()` 讀取，`Moment::locale($name)` 設定）、`localeData` 與名稱清單

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

1. `defineLocale`、`updateLocale`（傳 `null` 會還原）、`parentLocale`。moment.js 中屬於函式的設定，在 PHP 用 callable

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

1. 自己寫 locale 類別：繼承 `Wilkques\Moment\Locale\Locale`（`src/Locale/Locales/*.php` 有 138 個範例）

    ```php

    // the class has a static config() array and overrides a method wherever the JavaScript locale has a function:
    // ordinal(), meridiem(), relativeTime(), calendar(), months(), weekdays(), preparse(), postformat() ...
    ```

### 時區

1. 內建 moment-timezone 自己的資料（IANA 2026e）：`Tz::names()`、`Tz::zone()`、`Tz::countries()`、`Tz::zonesForCountry()`

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

1. 不存在與重複的牆上時間（春季跳一小時 / 秋季重複一小時）

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

1. `Tz::setDefault`、`Tz::guess`、`Tz::add`（用 moment-timezone 的 packed 格式加入自己的時區）、`Tz::link`、`Tz::load`

    ```php

    Tz::setDefault('Asia/Tokyo');   // like moment.tz.setDefault(): strings without an offset are read in that zone
    Moment::create('2024-01-31T12:00:00')->format();
    // output: 2024-01-31T12:00:00+09:00

    Tz::setDefault(null);

    Tz::guess();                      // the host's zone: PHP's default zone name when known, else offset scoring
    // output: Asia/Taipei

    Tz::add('Custom/Zone|CST CDT|60 50|0101|1:00:00 2000');
    ```

### Hook 與擴充

1. `macro`（moment 的 `moment.fn.foo = ...`）

    ```php

    Moment::macro('endOfNoon', function ($m, $minutes) {
        return $m->hour(12)->minute($minutes);
    });

    Moment::utc('2024-01-31T01:00')->endOfNoon(45)->format('HH:mm');
    // output: 12:45
    ```

1. 放 callable 的靜態屬性：`$calendarFormat`、`$parseTwoDigitYear`、`$createFromInputFallback`、`$updateOffset`、`$deprecationHandler`、`$suppressDeprecationWarnings`

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

1. 常數與預設值：`Moment::VERSION`、`Moment::ISO_8601`、`Moment::RFC_2822`、`Moment::$HTML5_FMT`、`Moment::$defaultFormat`

    ```php

    Moment::VERSION;                  // 2.30.1 (the moment.js version this follows)
    Moment::$HTML5_FMT['DATE'];        // YYYY-MM-DD
    Moment::$defaultFormat;            // YYYY-MM-DDTHH:mm:ssZ
    ```

1. `Moment::now()` 與 `Moment::setNow()`：在測試中凍結「現在」（JavaScript 的 `moment.now = ...`）

    ```php

    Moment::setNow(1718445600000);   // 2024-06-15T10:00:00Z
    Moment::now();
    // output: 1718445600000

    Moment::setNow(null);             // back to the clock
    ```

### 已棄用

1. 已棄用的寫法都能用，並各自只觸發一次 `E_USER_DEPRECATED`，如同 moment 的 console 警告：`lang()`、`zone()`、實例的 `min()` / `max()`、`moment.lang`、`moment.langData`、`add('days', 1)`

    ```php

    Moment::$suppressDeprecationWarnings = true;   // silence them

    Moment::utc('2024-01-31')->add('days', 1)->format('YYYY-MM-DD');
    // output: 2024-02-01
    ```

### 像 `new Date(string)` 一樣解析

字串不是 ISO 8601、不是 RFC 2822、又沒指定格式時，moment 會交給 JavaScript 的 `Date`。這裡用 V8 日期解析器的移植（`Parse\V8DateParser`）做同樣的事，所以 `Moment::create('Jan 5, 2024 10:30 PM')` 或 `'2024/01/05'` 的結果和 Chrome、Node 一致。和 moment 一樣，strict 模式不會用到它，看起來像 ISO/RFC 卻無效的字串仍然是 invalid。

## moment.js → PHP

幾乎所有名稱都不變。PHP 不能讓靜態函式與實例方法同名，PHP 5 也不能用 `as` / `clone` 這類保留字當方法名，所以這些由 `__call` / `__callStatic` 提供：兩種寫法都能用。

| moment.js | PHP |
| --- | --- |
| `moment(x)` | `Moment::create($x)` |
| `moment.utc(x)` | `Moment::utc($x)`（或 `createUtc`） |
| `moment.tz(x, zone)` | `Moment::tz($x, $zone)`（或 `createInZone`） |
| `moment.unix(s)` | `Moment::unix($s)`（或 `fromUnix`） |
| `m.clone()` | `$m->clone()`（或 `copy()`） |
| `m.utc()`、`m.unix()`、`m.tz()`、`m.locale()`、`m.localeData()`、`m.months()` | 實例上同名 |
| `moment.locale('fr')`、`moment.localeData()`、`moment.defineLocale()`、`moment.updateLocale()`、`moment.locales()` | `Moment::locale('fr')` 等 |
| `moment.months()`、`moment.weekdays(true)` … | `Moment::months()`、`Moment::weekdays(true)` … |
| `moment.min()`、`moment.max()` | `Moment::min()`、`Moment::max()` |
| `moment.duration(...)`、`d.as('hours')`、`d.clone()` | `Moment::duration(...)`、`$d->as('hours')`、`$d->clone()` |
| `moment.parseZone(s)` / `m.parseZone()` | `Moment::parseZone($s)` / `$m->parseZone()` |
| `moment.HTML5_FMT.DATE`、`moment.defaultFormat`、`moment.ISO_8601` | `Moment::$HTML5_FMT['DATE']`、`Moment::$defaultFormat`、`Moment::ISO_8601` |
| `moment.relativeTimeThreshold()`、`relativeTimeRounding()` | 同名，在 `Moment`（或 `Duration`）上 |
| `moment.parseTwoDigitYear`、`calendarFormat`、`createFromInputFallback`、`deprecationHandler`、`suppressDeprecationWarnings` | `Moment` 上同名的靜態屬性，放 callable |
| `moment.tz.names()`、`.zone()`、`.add()`、`.link()`、`.load()`、`.guess()`、`.setDefault()`、`.zonesForCountry()`、`.moveInvalidForward` … | `Wilkques\Moment\Timezone\Tz::names()`、`::zone()`、`::add()` …，以及 `Tz::$moveInvalidForward` |
| `moment.fn.foo = function () {…}` | `Moment::macro('foo', function ($m, …) {…})`，之後 `$m->foo(…)` |

已棄用的寫法（`lang()`、`zone()`、實例的 `min()` / `max()`、`moment.lang`、`moment.langData`、`add('days', 1)`）都能用，並各自只觸發一次 `E_USER_DEPRECATED`，如同 moment 的 console 警告。設 `Moment::$suppressDeprecationWarnings = true` 可關閉。

## 與 moment.js 的已知差異

- `tz.guess()` 在 moment-timezone 認得 PHP 預設時區名稱時直接用它（等同 moment-timezone 用 Intl 回報的名稱）；否則執行 moment-timezone 的 offset 計分搜尋。有 `ext-intl` 時，搜尋會像 moment-timezone 用 `toTimeString()` 那樣，從 ICU 的英文長名稱取出主機縮寫一起計分（同一個時區的名稱在不同 ICU 版本可能不同，例如 `Eire`）；沒有 intl 時只看 offset，`CET` 之類少數舊式時區會不同。`Tz::$guessUseAbbr = false` 強制只看 offset，`true` 在沒有 intl 時改用 PHP 自己的縮寫。
- `Moment::$updateOffset` 在與 moment 的 `updateOffset` hook 相同的時機、以相同的 `keepTime` 被呼叫。`Moment::$createFromInputFallback` 收到 `($string, $useUtc)`，回傳像 `array('ms' => $ms)` 的解析結果（invalid 則回 null），而不是 moment 的 `config` 物件。
- `Moment::locale($name)` / `Registry::get()` 遇到不認得的名稱時退回全域 locale，同 `moment.localeData()`；`Registry::find()` 則回傳 null。無效的 moment 呼叫 `toDate()` 會回傳 `InvalidDate`（`DateTime` 的子類別：`isValid()` 為 false、`getTimestamp()` 為 false、`format()` 回傳 "Invalid Date"），這是 PHP 裡最接近 JavaScript Invalid Date 的東西。
- 傳給 `tz()` 的時區名稱若 moment-timezone 不認得會被忽略（moment-timezone 也只是記錄錯誤）。

## 測試

`tests/fixtures/` 是真正的 moment.js 與 moment-timezone 產生的對照資料：核心約 9,000 筆、138 個 locale × 約 10,500 項檢查（含用門檻強制觸發每個相對時間 key、每個星期幾的 `calendar()`、每天的序數，以及直接呼叫各 locale 的函式、餵 `humanize()` 不會產生的輸入：`relativeTime`、`calendar`、`ordinal`、`meridiem`、`meridiemHour`、`isPM`、多種格式字串下的月份與星期名稱、`preparse`、`postformat`）、API 約 2,600 筆、四種主機時區下約 1,400 筆的 `new Date(string)` 解析、223 組交叉組合（每個 locale × 五個時區 × DST 邊界、各種 `dow/doy` 的週規則、所有單位的 `get`/`set` 含溢位、跨 DST 的 `add`/`diff`/`startOf`、offset、`humanize` 變化、特殊格式字串、比較），7,000 筆固定種子的隨機操作鏈與解析來回，再加上 5,900 筆隨機案例：`Duration`（各種建立方式、`add`/`subtract`/`abs`/`clone`、所有 getter 與 `as()` 單位、`humanize` 變化）、`calendar()` 的各種參數與自訂格式（字串與函式）、執行期 locale（有無父層的 `defineLocale`、`updateLocale` 與還原）。`gaps` fixtures 針對覆蓋率報告顯示從未執行到的行為：invalid moment 上的每個方法、era 與緊湊時間的解析 token、字串 setter、數字字串、極端 Duration、locale 查找、哪些字串會進入 `Moment::$createFromInputFallback`、`Moment::$updateOffset` 何時以什麼 `keepTime` 被呼叫，帶 offset 的 `Tz::zonesForCountry()`、各種輸入的 `moment.tz()` 所觸發的 `updateOffset` 呼叫，以及單獨測試 V8 日期解析器（約 300 個 `new Date(字串)`）。另外用同一個產生器跑過更大量的隨機批次，沒有再發現差異，PHPUnit 會把它們重放到本套件上，並搭配手寫測試。

```
cd tools && npm install                   # moment 2.30.1 + moment-timezone（只有重新產生 fixtures 時才需要）
node generate-fixtures.js                 # 核心 fixtures
node generate-locale-fixtures.js          # 各 locale 的 fixtures
node generate-api-fixtures.js             # API fixtures（用 TZ=UTC 執行）
node generate-fallback-fixtures.js        # new Date(string) fixtures
TZ=UTC node generate-cross-fixtures.js    # locale x zone, weeks, get/set, add/diff, offsets, ...
TZ=UTC node generate-extra-fixtures.js    # Duration, calendar() formats, defineLocale/updateLocale
TZ=UTC node generate-fuzz-fixtures.js     # seeded random chains (FUZZ_SEED / FUZZ_CHAINS / FUZZ_PARSE for bigger runs)
node generate-guess-fixtures.js           # 每個時區當主機時的 tz.guess()（只看 offset）
node generate-guess-fixtures.js icu       # 同上，但用英文 ICU 名稱，所以縮寫會計分（PHP 端需要 ext-intl）
node generate-localefn-fixtures.js        # 直接呼叫各 locale 的函式
node generate-tzdata.js                   # 由 moment-timezone 產生 data/latest.php

vendor/bin/phpunit -c phpunit-higher.xml  # PHP 7.1 - 8.0
vendor/bin/phpunit -c phpunit-latest.xml  # PHP 8.1+ (PHPUnit 10+ config schema)
vendor/bin/phpunit -c phpunit-lower.xml   # PHP 5.3 - 5.6
tools/run-matrix.sh                       # 用 docker 跑每個 PHP 版本（含 32 位元）（在 /var/www/packages 執行）
php tools/locale-check.php ru de          # 單一 locale，附不一致的細節
```

locale 的 fixtures 是 gzip 壓縮的：locale 測試需要 zlib 擴充，沒有時會略過；ICU 的 guess 測試沒有 ext-intl 時會略過。

`src/` 的行覆蓋率（PHP 8.4，pcov、intl、mbstring 皆有）：核心 99.2%，locale 99.8%。剩下的是只會在其他環境執行的程式（PHP 5.3 的後備、moment-timezone 不認得的主機時區），或沒有任何輸入能到達的分支（從 JavaScript 移植過來但觸發不了的部分）。

## 授權

MIT。行為、locale 資料與時區資料源自 [moment.js](https://github.com/moment/moment) 和 [moment-timezone](https://github.com/moment/moment-timezone)（MIT）；日期解析器依照 [V8](https://v8.dev)（BSD）。
