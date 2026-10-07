# Moment for PHP

[English](README.md) | 繁體中文

把 [moment.js](https://momentjs.com)（2.30.1）與 moment-timezone（0.6.5）移植到 PHP。適合前後端都要顯示、解析、計算日期，而且結果必須和 JavaScript 端**完全一致**的情境：相同的 format token、`add()` / `diff()` / `startOf()` 語意、Duration、相對時間字串、全部 138 個 locale 與同一份時區資料。相容到 **PHP 5.3**，不依賴任何套件。

不需要和 moment 一致的話，[Carbon](https://carbon.nesbot.com) 是更常見的選擇。

## 需求

- PHP >= 5.3，32 位元或 64 位元皆可。時間戳是整數毫秒：64 位元上是 `int`，32 位元上是存放整數值的 `float`（float 到 2^53 都精確，JavaScript 自己的上限是 8.64e15），所以那裡的 `valueOf()` 與 `unix()` 可能回傳 float。
- 不需要其他套件。建議安裝 `ext-mbstring`：沒有它時，解析大寫的非 ASCII 名稱（`parse('MÄRZ', 'MMMM')`）會改用內建的小寫對照表，只涵蓋拉丁、希臘、斯拉夫、亞美尼亞與喬治亞文字。

## 安裝

```
composer require wilkques/moment
```

## 使用方式

```php
use Wilkques\Moment\Moment;

$m = Moment::utc('2024-01-31T12:34:56Z');

$m->copy()->add(1, 'month')->format('YYYY-MM-DD');   // "2024-02-29"（夾到月底，不是 3/2）
$m->format('dddd, MMMM Do YYYY, h:mm:ss a');          // "Wednesday, January 31st 2024, 12:34:56 pm"
$m->copy()->startOf('isoWeek')->format('L');          // "01/29/2024"
$m->diff('2024-03-31', 'months');                     // -2
$m->fromNow();                                        // "2 years ago"
$m->copy()->locale('zh-tw')->format('LLLL');          // "2024年1月31日星期三 12:34"

Moment::tz('2024-03-10T01:30:00', 'America/New_York')->add(1, 'hour')->format();
// "2024-03-10T03:30:00-04:00"（那天 02:30 不存在）

Moment::duration(array('hours' => 25, 'minutes' => 61))->humanize(true);   // "in a day"
Moment::locale('fr');                                                      // 全域 locale，同 moment.locale('fr')
```

和 moment.js 一樣，Moment 是**可變**物件：`add()`、`startOf()`、`month(3)` 等都會修改並回傳自身。要保留原值請用 `copy()`（即 moment 的 `clone()`）。

輸入可以是 `null`（invalid，同 `moment(null)`）、不傳（現在）、毫秒的 int/float、另一個 `Moment`、`DateTime`、清單 `array(2024, 0, 31, 12, 34, 56, 789)`（月份從 0 開始）、物件式陣列 `array('year' => 2024, 'month' => 0, 'day' => 31)`，或字串。格式與 strict 模式同 moment：`Moment::create('31/01/2024', 'DD/MM/YYYY', true)`，也可傳格式陣列。

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

## 已涵蓋

- **核心**：建立與解析（ISO 8601、RFC 2822、ASP.NET `/Date()/`、自訂格式、strict、多格式，以及退回 `new Date(string)`）、所有 getter/setter、`add`/`subtract`、`startOf`/`endOf`、比較、`diff`、全部 token 的 `format`、`toISOString`、`toArray`、`toObject`、`parsingFlags`、`invalidAt`、`creationData`、`inspect`、era、`isDST`、`hasAlignedHourOffset`。
- **JSON**：`Moment` 或 `Duration` 做 `json_encode()` 的結果同 `JSON.stringify`（`toJSON()`；invalid 的 moment 是 null），PHP 5.4 以上才有（5.3 沒有 JsonSerializable，請呼叫 `toJSON()`）。
- **Duration**：可由數字、物件、ISO 8601、ASP.NET 與 `array('from' => , 'to' => )` 建立；`as*()`、getter、`add`/`subtract`/`abs`、使用 moment 門檻的 `humanize()`、`toISOString()`。
- **相對時間**：`from`、`to`、`fromNow`、`toNow`、`calendar`。
- **Locale**：moment 2.30.1 的全部 138 個 locale（`Wilkques\Moment\Locale\Locales\*`，第一次使用時才載入）。自訂：`Moment::defineLocale('xx', array(...))`，或繼承 `Wilkques\Moment\Locale\Locale`。
- **時區**：內建 moment-timezone 自己的資料（IANA 2026e，放在 `data/latest.php`），所以每個 PHP 版本、以及瀏覽器端的結果都相同。`utc()`、`local()`、`utcOffset()`、`tz()`、`Tz::setDefault()`、以 `Tz::add()` 加入自己的時區。「local」指 PHP 的 `date_default_timezone_get()`。

### 像 `new Date(string)` 一樣解析

字串不是 ISO 8601、不是 RFC 2822、又沒指定格式時，moment 會交給 JavaScript 的 `Date`。這裡用 V8 日期解析器的移植（`Parse\V8DateParser`）做同樣的事，所以 `Moment::create('Jan 5, 2024 10:30 PM')` 或 `'2024/01/05'` 的結果和 Chrome、Node 一致。和 moment 一樣，strict 模式不會用到它，看起來像 ISO/RFC 卻無效的字串仍然是 invalid。

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
