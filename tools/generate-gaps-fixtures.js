// Targeted golden data for behaviour the coverage report showed was never executed: invalid moments, era and
// compact-time parse tokens, string setters, RFC 2822 / ISO_8601 inputs, number strings, extreme durations,
// locale lookup. Run with TZ=UTC. Writes tests/fixtures/gaps.json.gz
var fs = require('fs');
var path = require('path');
var moment = require('moment-timezone');
moment.suppressDeprecationWarnings = true;
var NOW = Date.UTC(2024, 5, 15, 10, 0, 0);
moment.now = function () { return NOW; };
['fr', 'ru', 'zh-tw', 'ar', 'de', 'ja', 'pt-br', 'sr-cyrl'].forEach(function (l) { require('moment/locale/' + l); });
moment.locale('en');
var cases = [];
function add(c) { cases.push(c); }
function iso(s) { return moment.utc(s).valueOf(); }
function J(v) { if (typeof v === 'number' && !isFinite(v)) return isNaN(v) ? 'NaN' : (v > 0 ? 'Infinity' : '-Infinity'); return v; }
var F = 'YYYY-MM-DDTHH:mm:ss.SSSZ';
function conv(r) {
    if (r === undefined) return null;
    if (r && r._isAMomentObject) return r.isValid() ? 'M:' + r.format(F) : 'M:invalid';
    if (r instanceof Date) return isNaN(r.getTime()) ? 'D:invalid' : 'D:' + r.getTime();
    if (Array.isArray(r)) return r.map(conv);
    if (r && typeof r === 'object') { var o = {}; Object.keys(r).forEach(function (k) { o[k] = conv(r[k]); }); return o; }
    return J(r);
}

// ---- A. every method on invalid moments
var INVALIDS = [['moment.invalid()', function () { return moment.invalid(); }], ['bad string', function () { return moment.utc('nope', 'YYYY'); }],
    ['bad tz', function () { return moment.tz('nope', 'YYYY', 'Asia/Taipei'); }], ['nan', function () { return moment.utc(NaN); }]];
var NOARG = ['valueOf', 'unix', 'year', 'month', 'date', 'day', 'hour', 'minute', 'second', 'millisecond', 'weekday', 'isoWeekday', 'dayOfYear', 'week', 'isoWeek', 'weekYear', 'isoWeekYear', 'quarter', 'daysInMonth',
    'isLeapYear', 'format', 'toISOString', 'toJSON', 'toString', 'toArray', 'toObject', 'toDate', 'isValid', 'utcOffset', 'zoneAbbr', 'zoneName', 'isUtc', 'isLocal', 'isUtcOffset', 'inspect', 'invalidAt', 'locale', 'fromNow',
    'toNow', 'calendar', 'weeksInYear', 'isoWeeksInYear', 'weeksInWeekYear', 'isoWeeksInISOWeekYear', 'eraName', 'eraAbbr', 'eraNarrow', 'eraYear', 'isDST', 'hasAlignedHourOffset', 'clone', 'tz'];
var WITHARG = [['add', 1, 'd'], ['subtract', 1, 'd'], ['startOf', 'day'], ['endOf', 'month'], ['set', 'year', 2000], ['diff', NOW], ['diff', NOW, 'days', true], ['isSame', NOW], ['isBefore', NOW], ['isAfter', NOW], ['isBetween', NOW, NOW + 1],
    ['isSameOrBefore', NOW], ['from', NOW], ['to', NOW], ['year', 2020], ['month', 1], ['date', 5], ['hour', 3], ['day', 2], ['weekday', 1], ['isoWeekday', 3], ['dayOfYear', 10], ['week', 4], ['quarter', 2], ['utcOffset', 60], ['utcOffset', '+08:00'],
    ['tz', 'Asia/Taipei'], ['local'], ['utc'], ['format', 'YYYY'], ['locale', 'fr'], ['get', 'year'], ['calendar', NOW]];
INVALIDS.forEach(function (iv) {
    var res = {};
    NOARG.forEach(function (n) { var m = iv[1](); res[n] = conv(m[n]()); });
    WITHARG.forEach(function (a) { var m = iv[1](); res[a.join('|')] = conv(m[a[0]].apply(m, a.slice(1))); });
    add({kind: 'invalid', which: iv[0], noarg: NOARG, witharg: WITHARG, expected: res});
});

// ---- B. conversions on valid moments
['UTC', 'America/New_York', 'Asia/Kolkata'].forEach(function (zone) {
    [iso('2024-01-31T12:34:56.789Z'), iso('1969-12-31T23:59:59.999Z'), iso('2100-02-28T00:00:00.001Z')].forEach(function (ms) {
        var m = moment.tz(ms, zone);
        add({kind: 'convert', zone: zone, ms: ms, expected: {obj: m.toObject(), arr: m.toArray(), date: m.toDate().getTime(), json: m.toJSON(), str: m.toString(), insp: m.inspect(), unix: m.unix(), isDate: moment.isDate(m.toDate()), isDate2: moment.isDate(m)}});
    });
});

// ---- C. parse tokens (eras, compact time, week years, odd widths) in a few locales
var PTOK = ['N', 'NN', 'NNN', 'NNNN', 'NNNNN', 'y', 'yy', 'yyy', 'yyyy', 'yo', 'N y', 'NNNN yo', 'kk', 'k', 'hmmss', 'Hmmss', 'hmm', 'Hmm', 'gg', 'GG', 'gggg', 'GGGG', 'ggggg', 'GGGGG', 'w', 'ww', 'W', 'WW', 'e', 'E', 'd', 'dd', 'ddd', 'dddd',
    'DDDD', 'DDD', 'SSSSS', 'SSSSSSSSS', 'S', 'SS', 'Q', 'Qo', 'X', 'x', 'ZZ', 'Z', 'a', 'A', 'YYYYY', 'YYYYYY', 'Y', 'YY', 'YYYY-MM', 'GGGG [W]WW E', 'gggg [w]ww e', 'YYYY DDDD', 'hh:mm:ss.SSSS a', 'kk:mm', 'Do', 'M/D', 'MMM', 'MMMM', 'D MMMM'];
var PDATES = ['2024-01-31T13:34:56.789Z', '2000-02-29T00:00:00.000Z', '0044-03-15T01:02:03.004Z', '-000044-03-15T01:02:03.004Z', '2021-01-03T23:59:59.000Z', '1989-01-08T12:00:00.000Z'].map(iso);
var rs = 99;
function rr() { rs = (rs * 1103515245 + 12345) % 2147483648; return rs / 2147483648; }
['en', 'ja', 'zh-tw', 'de'].forEach(function (loc) {
    var list = [];
    PTOK.forEach(function (f) {
        PDATES.forEach(function (ms) {
            var text = moment.utc(ms).locale(loc).format(f), variants = [text];
            if (rr() < 0.5 && text.length > 1) variants.push(text.slice(1));
            if (rr() < 0.3) variants.push(text + '9');
            if (rr() < 0.3) variants.push(text.toLowerCase());
            variants.forEach(function (t) {
                [false, true].forEach(function (strict) {
                    var p = moment.utc(t, f, loc, strict), r = {fmt: f, text: t, strict: strict, valid: p.isValid()};
                    if (r.valid) r.ms = p.valueOf();
                    list.push(r);
                });
            });
        });
    });
    add({kind: 'parseTokens', locale: loc, list: list});
});

// ---- D. string setters
var BASES = ['2024-01-31T12:00:00Z', '2024-03-10T08:00:00Z', '2021-01-03T00:00:00Z'].map(iso);
var SETS = [['month', 'March'], ['month', 'Mar'], ['month', 'mar'], ['month', '3'], ['month', '13'], ['month', 'nonsense'], ['month', ''], ['month', 'Février'], ['month', 'мар'], ['month', 'مارس'], ['month', '三月'],
    ['day', 'Monday'], ['day', 'Mon'], ['day', 'mo'], ['day', '7'], ['day', 'x'], ['day', 'lundi'], ['day', 'понедельник'], ['weekday', 'Tuesday'], ['weekday', 'x'], ['isoWeekday', 'Wed'], ['isoWeekday', 'x'], ['isoWeekday', '7'], ['isoWeekday', 'dimanche'],
    ['set', 'month', 'May'], ['set', 'weekday', 'Fri'], ['set', 'day', 'Sat'], ['set', 'isoWeekday', 'Sun'], ['dayOfYear', '100'], ['week', '5'], ['year', '1999'], ['year', 'x'], ['hour', '5'], ['minute', 'x']];
['en', 'fr', 'ru', 'ar', 'zh-tw', 'de'].forEach(function (loc) {
    var out = BASES.map(function (b) { return SETS.map(function (s) { var m = moment.utc(b).locale(loc); try { m[s[0]].apply(m, s.slice(1)); } catch (e) { return 'threw'; } return conv(m); }); });
    add({kind: 'setStrings', locale: loc, bases: BASES, sets: SETS, expected: out});
});

// ---- E. statics and deprecated instance min/max
var base = moment.utc('2024-01-31T12:00:00Z');
var E = [];
['min', 'max'].forEach(function (fn) {
    ['2024-06-01T00:00:00Z', '2023-01-01T00:00:00Z', '2024-01-31T12:00:00Z', 'nope'].forEach(function (o) {
        E.push({fn: fn, arg: o, expected: conv(base.clone()[fn](o === 'nope' ? moment.invalid() : o))});
    });
    E.push({fn: fn + 'Static', args: [], expected: conv(moment[fn]().valueOf() === NOW ? 'now' : 'other')});
});
add({kind: 'minmax', base: base.valueOf(), cases: E});
add({kind: 'statics', expected: {isDate: [moment.isDate(new Date()), moment.isDate(1), moment.isDate(null)], isDuration: [moment.isDuration(moment.duration(1)), moment.isDuration(1)],
    thr: [moment.relativeTimeThreshold('s'), moment.relativeTimeThreshold('ss'), moment.relativeTimeThreshold('w'), moment.relativeTimeThreshold('M'), moment.relativeTimeThreshold('x'), moment.relativeTimeThreshold('s', 50), moment.relativeTimeThreshold('s'), moment.relativeTimeThreshold('ss'), moment.relativeTimeThreshold('s', 45)]}});

// ---- F. RFC 2822 / ISO_8601 / format arrays
var SP = ['Wed, 31 Jan 2024 12:34:56 GMT', 'Wed, 31 Jan 24 12:34 +0800', '31 Jan 99 01:02:03 Z', '31 Jan 099 01:02 EST', '31 Jan 49 00:00 PST', '31 Jan 50 00:00 UT', 'Mon, 31 Jan 2024 12:34:56 GMT', '31 Jan 2024 12:34:56 +0530 (comment)',
    'Wed,   31   Jan   2024   12:34:56   -0000', '31 Jan 2024 12:34:56 CDT', '31 Jan 2024 12:34:56 MST', '31 Jan 2024 12:34:56 X', '31 Feb 2024 12:34 GMT', '2024-01-31T12:34:56Z', '2024-01-31 12:34:56 +08:00', '2024-W05-3T12:00',
    '20240131T123456Z', '2024-031T12:00', '+002024-01-31', '2024-01-31T24:00:00', '2024-01-31T12:60', '2024-01-31T12:34:56.123456789Z', '2024-01-31T12,5', '2024-01-31T12:34:56,789', ' 2024-01-31 ', '2024-01-31T12:34:56 +0800', '/Date(1706704496789)/', '/Date(-1)/', 'Date(5)'];
var SF = [null, 'ISO_8601', 'RFC_2822', ['ISO_8601', 'RFC_2822'], ['YYYY', 'ISO_8601'], ['DD MMM YYYY HH:mm', 'YYYY-MM-DD']];
['utc', 'local', 'tz'].forEach(function (mode) {
    var out = [];
    SP.forEach(function (s) {
        SF.forEach(function (f) {
            [false, true].forEach(function (strict) {
                var fmt = f === null ? undefined : (Array.isArray(f) ? f.map(function (x) { return x === 'ISO_8601' ? moment.ISO_8601 : (x === 'RFC_2822' ? moment.RFC_2822 : x); }) : (f === 'ISO_8601' ? moment.ISO_8601 : (f === 'RFC_2822' ? moment.RFC_2822 : f)));
                var m = mode === 'utc' ? moment.utc(s, fmt, strict) : (mode === 'tz' ? moment.tz(s, fmt, strict, 'America/New_York') : moment(s, fmt, strict));
                var pf = m.parsingFlags();
                out.push([m.isValid(), m.isValid() ? m.valueOf() : null, pf.iso, pf.rfc2822, pf.weekdayMismatch]);
            });
        });
    });
    add({kind: 'special', mode: mode, strings: SP, formats: SF, expected: out});
});

// ---- G/H. number strings and extreme durations
var NS = ['0o17', '0b101', '0X1F', '  12  ', 'Infinity', '-Infinity', '+5', '5.', '.5', '1e3', '-0x10', '0x', '', ' ', '1_000', '12px', '0b2', '0o8', '1e', '--1', '+-1', ' 5 ', '١٢', 'NaN', '1,5', '00012'];
var NUM = NS.map(function (s) { var d = moment.duration(s); return {in: s, valid: d.isValid(), ms: J(d.asMilliseconds()), iso: d.toISOString(), humanize: d.humanize()}; });
add({kind: 'numberStrings', expected: NUM});
var XD = [[1e21, 'ms'], [1e22, 'years'], [1e-7, 'seconds'], [0.1 + 0.2, 'seconds'], [123456789.123456789, 'ms'], [Infinity, 'd'], [-Infinity, 'h'], [NaN, 'd'], [1e15, 'ms'], [9.007199254740993e15, 'ms'], [1.5e300, 'ms'], [0.000001, 'ms'], [100, 'years'], [99999999999999, 'd'], [5e-324, 'ms'], [1234.5678, 'minutes']];
var XDUR = XD.map(function (a) {
    var d = moment.duration(a[0], a[1]), threw = null;
    try { d.as('bogus'); } catch (e) { threw = e.message; }
    return {in: [J(a[0]), a[1]], valid: d.isValid(), iso: d.toISOString(), years: J(d.years()), days: J(d.days()), hours: J(d.hours()), ms: J(d.asMilliseconds()), asY: J(d.asYears()), humanize: d.humanize(), threw: threw};
});
add({kind: 'extremeDurations', expected: XDUR});

// ---- I. locale lookup
var KEYS = ['en', 'EN', 'en-US', 'en_us', 'zh_TW', 'zh-hant', 'xx', 'fr-ca-zz', ['xx', 'fr'], ['xx-yy', 'pt-br-x'], '../etc', 'a/b', 'FR', 'pt-BR', 'ZH-tw', 'sr-cyrl-rs', ['de-xx', 'fr'], ['de', 'fr'], ['xx'], [], '', null, 'fr-'];
var LK = KEYS.map(function (k) { var r = null; try { var l = moment.localeData(k); r = l ? l._abbr : null; } catch (e) { r = 'threw'; } return r; });
var LI = KEYS.map(function (k) { var r = null; try { var m = moment.utc(NOW).locale('ru'); m.locale(k === null ? undefined : k); r = m.locale(); } catch (e) { r = 'threw'; } return r; });
var GL = KEYS.map(function (k) { var r = null; try { moment.locale('en'); r = moment.locale(k === null ? undefined : k); } catch (e) { r = 'threw'; } moment.locale('en'); return r; });
add({kind: 'localeLookup', keys: KEYS, expected: {localeData: LK, instance: LI, global: GL}});

// ---- K. V8's date parser on its own (new Date(string)), including strings moment's ISO/RFC regexes would take first
var RAW = ['2024-01-05T10:30:00+0800', '2024-01-05T10:30:00+08', '2024-01-05T10:30:00+08:00', '2024-01-05T10:30:00+08:0', '2024-01-05T10:30:00+2400', '2024-01-05T10:30:00+0860', '2024-01-05T10:30:00-2359', '2024-01-05T10:30:00+8:00',
    '2024-01-05T10:30:00+08:00:00', '2024-01-05T10:30:00+08:00x', '2024-01-05T10:30:00+', '2024-01-05T10:30:00-', '2024-01-05T10:30+0800', '2024-01-05T10:30Z', '2024-01-05T10:30:00.123456+0800', '2024-01-05T10:30:00ZZ', '2024-01-05T10:30:00z',
    '+002024-01-05', '-000001-01-05', '-000000-01-05', '+000000-01-05', '+1234567-01-05', '+12345-01-05', '+02024-01-05T10:30:00Z', '-271821-04-20T00:00:00Z', '+275760-09-13T00:00:00Z', '+275760-09-13T00:00:00.001Z', '-271821-04-19T23:59:59.999Z',
    '2024', '2024-01', '2024-01-05', '2024-13', '2024-00', '2024-01-00', '2024-01-32', '2024-02-30', '2023-02-29', '2024-01-05T24:00', '2024-01-05T24:00:00', '2024-01-05T24:00:00.000', '2024-01-05T24:01', '2024-01-05T24:00:01', '2024-01-05T25:00',
    '2024-01-05T10:60', '2024-01-05T10:30:60', '2024-01-05T10', '2024-01-05T10:3', '2024-01-05T1:30', '2024-01-05T10:30:0', '2024-01-05T10:30:00.', '2024-01-05T10:30:00.1', '2024-01-05T10:30:00.12', '2024-01-05T10:30:00.1234567890123',
    '2024-01-05T10:30:00 +0800', '2024-01-05 10:30:00+0800', '2024-01-05T10:30:00 GMT', 'T10:30', '2024-01-05T', '2024-1-05', '20240105', '2024-W01', 'Jan 5 2024 \u{1F600}', '\u{1F600}', 'Jan 5 2024 é', 'Jan 5 2024 ١',
    'Jan 5 2024 10:30:15:20', 'Jan 5 2024 10:30:15:20:25', 'Jan 5 2024 1:2:3:4:5', 'Jan 5 2024 10::30', 'Jan 5 2024 10:30::', 'Jan 5 2024 ::', '10:30:45:12 Jan 5 2024', '10::30 Jan 5 2024', 'Jan 5 2024 10.5', 'Jan 5 2024 10:30.5',
    'Jan 5 2024 10:30:15.123456789012345', 'Jan 5 2024 10:30:15.5.5', 'Jan 5 2024 10:30:15. 5', 'Jan 5 2024 10:30:15.x', 'Jan 5 2024 10:30:15.', 'Jan 5 2024 24:00', 'Jan 5 2024 24:00:00', 'Jan 5 2024 24:00:00.000', 'Jan 5 2024 24:00:00.001', 'Jan 5 2024 24:01',
    'Jan 5 2024 13:00 PM', 'Jan 5 2024 0:00 AM', 'Jan 5 2024 12:00 AM', 'Jan 5 2024 12:00 PM', 'Jan 5 2024 12 AM', 'Jan 5 2024 13 PM', 'Jan 5 2024 0 PM', 'Jan 5 2024 60:00', 'Jan 5 2024 10:60', 'Jan 5 2024 10:30:60', 'Jan 5 2024 10:30:15.1000',
    'Jan 5 2024 GMT+99999999', 'Jan 5 2024 GMT+1073741823', 'Jan 5 2024 GMT+10737418', 'Jan 5 2024 +1073741', 'Jan 5 2024 GMT+2400', 'Jan 5 2024 GMT+24:00', 'Jan 5 2024 GMT-24:60', 'Jan 5 2024 GMT+5:99', 'Jan 5 2024 GMT+5:', 'Jan 5 2024 GMT+:30',
    'Jan 5 2024 GMT+05:30:15', 'Jan 5 2024 GMT+0530', 'Jan 5 2024 GMT+530', 'Jan 5 2024 GMT+5', 'Jan 5 2024 GMT+05', 'Jan 5 2024 GMT+', 'Jan 5 2024 GMT-', 'Jan 5 2024 UTC+8', 'Jan 5 2024 UT+8', 'Jan 5 2024 Z+8', 'Jan 5 2024 EST+1', 'Jan 5 2024 EST-0100',
    'Jan 5 2024 10:30 +05:30', 'Jan 5 2024 10:30 -05:30', 'Jan 5 2024 10:30 +0530', 'Jan 5 2024 10:30 -0530', 'Jan 5 2024 10:30 +05', 'Jan 5 2024 10:30 +5', 'Jan 5 2024 10:30 +5:30', 'Jan 5 2024 10:30 +123', 'Jan 5 2024 10:30 +12345',
    '1/2/3/4', '1/2', '1/2/', '/1/2', '1//2', '1-2-3', '1-2', '01-02-03', '1.2.3', '1.2', '123456789012345678901234567890', '12345678901234567890 Jan 5', 'Jan 12345678901234567890', '0.5', '.5', '5.', '1e5', '0x10', '-0', '+0', '- 5', '+ 5',
    'Jan 5 2024 (comment) 10:30', 'Jan 5 2024 (unclosed', 'Jan 5 2024 (a (b) c) 10:30', '(Jan 5 2024)', 'Jan 5 2024 10:30 (PST)', 'Jan 5 2024 10:30 (', 'Jan 5 2024 10:30 )', '((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((',
    'Sat, 05 Jan 2024 10:30:00 GMT', 'Sat Jan 05 2024 10:30:00 GMT+0800 (CST)', 'Saturday, January 5, 2024', 'sat jan 5 2024', 'SAT JAN 5 2024', 'Satu 5 Jan 2024', 'Saturday Jan 5 2024 saturday',
    'Jan 5 2024 AM 10', 'AM 10:30 Jan 5 2024', 'PM Jan 5 2024', 'Jan 5 2024 PM PM', 'Jan 5 2024 10 AM PM', 'Jan 5 2024 10:30 A', 'Jan 5 2024 10:30 P', 'Jan 5 2024 10:30 a.m.', 'Jan 5 2024 10:30 p.m.', 'Jan 5 2024 10:30 am pm',
    '5 Jan 2024', '5 January 2024', '5 Janu 2024', '5Jan2024', '5-Jan-2024', '5/Jan/2024', '2024/Jan/5', 'Jan/5/2024', 'Jan.5.2024', 'Jan,5,2024', 'Jan, 5, 2024', 'Jan 5th 2024', 'Jan 5, 24', 'Jan 5, 49', 'Jan 5, 50', 'Jan 5, 99', 'Jan 5, 100',
    'Jan 5 0', 'Jan 5 1', 'Jan 5 -1', 'Jan 5 +1', 'Jan 5 -2024', 'Jan 5 +2024', 'Jan 5 275760', 'Jan 5 275761', 'Jan 5 -271821', 'Jan 5 -271822', '1 Jan -271821', 'Sep 13 275760 00:00:00.001 UTC', 'Apr 20 -271821 00:00:00 UTC'];
var RAWRES = RAW.map(function (str) { var t = new Date(str).getTime(); return isNaN(t) ? null : t; });
add({kind: 'v8Raw', inputs: RAW, expected: RAWRES});

// ---- J. hooks: createFromInputFallback (which strings reach it), updateOffset (when and with which keepTime), zonesForCountry offsets
var FIXED = Date.UTC(2001, 0, 2, 3, 4, 5);
var FB = ['Jan 5, 2024 10:30 PM', '2024/01/05', 'nonsense', '', ' ', '5 January 2024', '2024-13-45', '2024-01-05T25:00', '20240105', 'Thu, 01 Jan 2015 00:00:00 GMT', 'Thu, 99 Jan 2015 00:00:00 GMT', '2024-01-05 10:30', '12/25/2024', 'x2024', '2024', '1e3', 'Sat Jan 05 2024', '2024-02-30', 'tomorrow'];
var origFallback = moment.createFromInputFallback;
var fbRes = [];
moment.createFromInputFallback = function (config) { fbCalls.push([config._i, !!config._useUTC]); config._d = new Date(FIXED); };
var fbCalls = [];
var MODES = [['create', function (x) { return moment(x); }], ['utc', function (x) { return moment.utc(x); }], ['tz', function (x) { return moment.tz(x, 'Asia/Taipei'); }], ['parseZone', function (x) { return moment.parseZone(x); }],
    ['strict-format', function (x) { return moment(x, 'YYYY-MM-DD', true); }], ['format', function (x) { return moment(x, 'YYYY-MM-DD'); }], ['array', function (x) { return moment([x]); }]];
FB.forEach(function (str) {
    MODES.forEach(function (mo) {
        fbCalls = [];
        var m = mo[1](str);
        fbRes.push({in: str, mode: mo[0], calls: fbCalls, valid: m.isValid(), ms: m.isValid() ? m.valueOf() : null});
    });
});
moment.createFromInputFallback = origFallback;
add({kind: 'fallbackHook', fixed: FIXED, inputs: FB, expected: fbRes});

var origUpdate = moment.updateOffset;
var uoLog = [];
moment.updateOffset = function (m, k) { uoLog.push(k === undefined ? 'u' : (k ? 't' : 'f')); return origUpdate.apply(this, arguments); };
var OPS = [
    ['create', function () { return moment(NOW); }], ['createUtc', function () { return moment.utc(NOW); }], ['createTz', function () { return moment.tz(NOW, 'America/New_York'); }],
    ['createMoment', function () { return moment(moment.utc(NOW)); }], ['createNull', function () { return moment(null); }], ['createDate', function () { return moment(new Date(NOW)); }], ['createArray', function () { return moment([2024, 0, 1]); }], ['createOffset', function () { return moment.utc(NOW).utcOffset(120); }], ['parseZone', function () { return moment.parseZone('2024-01-01T00:00:00+05:30'); }],
    ['utc', function (m) { return m.utc(); }], ['local', function (m) { return m.local(); }], ['utcOffset', function (m) { return m.utcOffset(60); }], ['utcOffsetKeep', function (m) { return m.utcOffset(60, true); }],
    ['utcOffsetStr', function (m) { return m.utcOffset('+08:00'); }], ['add d', function (m) { return m.add(1, 'd'); }], ['add h', function (m) { return m.add(1, 'h'); }], ['add M', function (m) { return m.add(1, 'M'); }], ['add 0', function (m) { return m.add(0, 'd'); }],
    ['subtract', function (m) { return m.subtract(1, 'd'); }], ['add dur', function (m) { return m.add(moment.duration(1, 'day')); }], ['month', function (m) { return m.month(1); }], ['date', function (m) { return m.date(5); }], ['hour', function (m) { return m.hour(3); }],
    ['set', function (m) { return m.set('year', 2000); }], ['startOf', function (m) { return m.startOf('day'); }], ['endOf', function (m) { return m.endOf('month'); }], ['tz', function (m) { return m.tz('Asia/Taipei'); }], ['tzKeep', function (m) { return m.tz('Asia/Taipei', true); }],
    ['tzUtc', function (m) { return m.tz('UTC'); }], ['tzBad', function (m) { return m.tz('Nope/Zone'); }], ['dayOfYear', function (m) { return m.dayOfYear(10); }], ['week', function (m) { return m.week(3); }], ['weekday', function (m) { return m.weekday(2); }],
    ['clone', function (m) { return m.clone(); }], ['diff', function (m) { m.diff(moment.utc(NOW + 1e9)); return m; }], ['format', function (m) { m.format(); return m; }], ['invalid', function () { return moment.invalid(); }], ['unix', function () { return moment.unix(1700000000); }]
];
var STARTS = {utc: function () { return moment.utc(NOW); }, local: function () { return moment(NOW); }, ny: function () { return moment.tz(NOW, 'America/New_York'); }, offset: function () { return moment.utc(NOW).utcOffset(-300); }};
var uoRes = [];
OPS.forEach(function (op) {
    Object.keys(STARTS).forEach(function (st) {
        var m = STARTS[st]();
        uoLog = [];
        var r = op[1](m), err = null;
        uoRes.push({op: op[0], start: st, calls: uoLog.join(''), ms: r.isValid() ? r.valueOf() : null, off: r.isValid() ? r.utcOffset() : null});
    });
});
moment.updateOffset = origUpdate;
add({kind: 'updateOffsetHook', ops: OPS.map(function (o) { return o[0]; }), starts: Object.keys(STARTS), expected: uoRes});

// moment.tz(...) creation: the updateOffset sequence for each kind of input
var ZH = 'Pacific/Kiritimati', NYZ = 'America/New_York';
var TZSPECS = [
    {n: 'num', i: NOW, z: ZH}, {n: 'iso', i: '2024-03-10T01:30:00', z: NYZ}, {n: 'iso Z', i: '2024-03-10T01:30:00Z', z: NYZ}, {n: 'iso +08', i: '2024-03-10T01:30:00+08:00', z: NYZ},
    {n: 'date only', i: '2024-03-10', z: NYZ}, {n: 'fmt', i: '2024-03-10', f: 'YYYY-MM-DD', z: NYZ}, {n: 'fmt time', i: '10:30', f: 'HH:mm', z: ZH}, {n: 'fmt month-day', i: '06-20', f: 'MM-DD', z: ZH},
    {n: 'fmt year', i: '2024', f: 'YYYY', z: NYZ}, {n: 'fmt strict', i: '2024-03-10', f: 'YYYY-MM-DD', strict: true, z: NYZ}, {n: 'fmt Z', i: '2024-03-10 10:00 +08:00', f: 'YYYY-MM-DD HH:mm Z', z: NYZ},
    {n: 'fallback', i: 'Jan 5, 2024 10:30 PM', z: NYZ}, {n: 'bad', i: 'zzz', z: NYZ}, {n: 'unknown zone', i: '2024-03-10', z: 'Nope/Zone'}, {n: 'array', i: [2024, 0, 1], z: NYZ}, {n: 'array partial', i: [2024], z: NYZ},
    {n: 'obj', i: {year: 2024, month: 1}, z: NYZ}, {n: 'obj hour', i: {hour: 10}, z: ZH}, {n: 'Date', i: {date: NOW}, z: NYZ}, {n: 'moment', i: {moment: NOW}, z: NYZ}, {n: 'null', i: null, z: NYZ},
    {n: 'unix', i: '1700000000', f: 'X', z: NYZ}, {n: 'locale fmt', i: '2024-03-10', f: 'YYYY-MM-DD', l: 'fr', z: NYZ}, {n: 'formats', i: '2024-03-10', f: ['YYYY-MM-DD', 'DD/MM/YYYY'], z: NYZ}
];
var tzOrigUpdate = moment.updateOffset, tzLog = [];
moment.updateOffset = function (m, k) { tzLog.push(k === undefined ? 'u' : (k ? 't' : 'f')); return tzOrigUpdate.apply(this, arguments); };
var TZRES = TZSPECS.map(function (sp) {
    var inp = sp.i;
    if (inp && inp.moment !== undefined) inp = moment.utc(inp.moment); else if (inp && inp.date !== undefined) inp = new Date(inp.date);
    tzLog = [];
    var args = [inp];
    if (sp.f !== undefined) args.push(sp.f);
    if (sp.strict) args.push(true);
    if (sp.l) args.push(sp.l);
    args.push(sp.z);
    var m = moment.tz.apply(null, args);
    return {n: sp.n, calls: tzLog.join(''), valid: m.isValid(), text: m.isValid() ? m.format() : null};
});
moment.updateOffset = tzOrigUpdate;
add({kind: 'tzCreateHook', specs: TZSPECS, expected: TZRES});

var RealDate = Date;
global.Date = (function () {
    function FD() { var a = Array.prototype.slice.call(arguments); if (!(this instanceof FD)) return new RealDate(NOW).toString(); return a.length ? new (Function.prototype.bind.apply(RealDate, [null].concat(a)))() : new RealDate(NOW); }
    FD.prototype = RealDate.prototype; FD.now = function () { return NOW; }; FD.UTC = RealDate.UTC; FD.parse = RealDate.parse;
    return FD;
})();
var ZC = ['TW', 'US', 'RU', 'AU', 'GB', 'XX', 'tw', 'BR', 'IN', 'NZ', 'CL', 'AQ', 'CN', ''];
var zfc = ZC.map(function (c) { return {c: c, plain: moment.tz.zonesForCountry(c), off: moment.tz.zonesForCountry(c, true)}; });
global.Date = RealDate;
add({kind: 'zonesForCountry', expected: zfc});

fs.writeFileSync(path.join(__dirname, '..', 'tests', 'fixtures', 'gaps.json.gz'), require('zlib').gzipSync(JSON.stringify(cases), {level: 9}));
console.log('gaps', cases.length);
