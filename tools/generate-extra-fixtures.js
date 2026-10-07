// Random differential data for Duration, calendar() with custom formats, and runtime defineLocale/updateLocale.
// Run with TZ=UTC. Writes tests/fixtures/extra.json.gz
var fs = require('fs');
var path = require('path');
var moment = require('moment-timezone');
moment.suppressDeprecationWarnings = true;
var NOW = Date.UTC(2024, 5, 15, 10, 0, 0);
moment.now = function () { return NOW; };
['fr', 'ru', 'zh-tw', 'ar', 'de', 'pl', 'ja'].forEach(function (l) { require('moment/locale/' + l); });
moment.locale('en');

var seed = parseInt(process.env.FUZZ_SEED || '777', 10);
var N = parseInt(process.env.FUZZ_N || '1', 10);
var OUT = process.env.FUZZ_OUT || 'extra.json.gz';
function rnd() { seed = (seed * 1664525 + 1013904223) % 4294967296; return seed / 4294967296; }
function pick(a) { return a[Math.floor(rnd() * a.length)]; }
function int(lo, hi) { return lo + Math.floor(rnd() * (hi - lo + 1)); }
var cases = [];

// ------------------------------------------------------------------ Duration
var D_UNITS = ['years', 'quarters', 'months', 'weeks', 'days', 'hours', 'minutes', 'seconds', 'milliseconds', 'y', 'Q', 'M', 'w', 'd', 'h', 'm', 's', 'ms', 'isoWeek', 'year', 'month', 'day', 'hour', 'minute', 'second', 'millisecond', 'week', 'quarter', 'bogus'];
var D_KEYS = ['years', 'quarters', 'months', 'weeks', 'days', 'hours', 'minutes', 'seconds', 'milliseconds', 'y', 'M', 'd', 'h', 'm', 's', 'ms', 'w', 'Q', 'isoWeek', 'foo'];
var ISO = ['P1Y', 'P2M', 'P3W', 'P4D', 'PT5H', 'PT6M', 'PT7S', 'PT0.5S', 'P1Y2M3DT4H5M6S', 'P1.5Y', 'PT1.5H', '-P1D', '+P2D', 'P-1D', 'P1DT-2H', 'PT36H', 'P0D', 'PT', 'P', 'P1M1W', 'PT1,5S', 'P1Y2M3W4DT5H6M7.89S'];
var ASP = ['1:30:00', '-0:15', '2.12:30:15.5', '0:0:0', '12:00', '1.00:00:00', '10.23:59:59.999', ':30', '-1.02:03:04.5'];
var GARBAGE = ['', 'x', 'hello', '1:2:3:4', '1e3', ' 5', '5 ', '0x10', 'NaN'];
function amount() { return pick([0, 1, -1, 2, 3, 5, 7, 10, 12, 13, 24, 25, 30, 31, 45, 59, 60, 90, 100, 365, 1000, 0.5, 1.5, 2.25, -0.5, -2.7, 1e6]); }
function spec() {
    var k = rnd();
    if (k < 0.4) return ['num', amount(), rnd() < 0.15 ? null : pick(D_UNITS)];
    if (k < 0.65) { var o = {}, n = int(0, 4); for (var i = 0; i < n; i++) o[pick(D_KEYS)] = rnd() < 0.08 ? 'x' : (rnd() < 0.2 ? amount() : int(-40, 400)); return ['obj', o]; }
    if (k < 0.8) return ['str', pick(ISO)];
    if (k < 0.9) return ['str', rnd() < 0.7 ? pick(ASP) : pick(GARBAGE)];
    return ['range', int(-3e12, 3e12), int(-3e12, 3e12)];
}
function build(sp) {
    if (sp[0] === 'num') return sp[2] === null ? moment.duration(sp[1]) : moment.duration(sp[1], sp[2]);
    if (sp[0] === 'range') return moment.duration({from: moment.utc(sp[1]), to: moment.utc(sp[2])});
    return moment.duration(sp[1]);
}
function addTo(d, sp, sub) {
    var f = sub ? 'subtract' : 'add';
    if (sp[0] === 'num') { if (sp[2] === null) d[f](sp[1]); else d[f](sp[1], sp[2]); }
    else if (sp[0] === 'range') d[f](build(sp));
    else d[f](sp[1]);
}
var D_LOCALES = ['en', 'fr', 'ru', 'zh-tw', 'ar', 'de', 'pl', 'ja'];
var count = 4000 * N;
for (var i = 0; i < count; i++) {
    var sp = spec(), ops = [], loc = pick(D_LOCALES);
    for (var j = int(0, 3); j > 0; j--) { var t = pick(['add', 'subtract', 'abs', 'clone']); ops.push(t === 'add' || t === 'subtract' ? [t, spec()] : [t]); }
    var d = build(sp).locale(loc);
    ops.forEach(function (op) {
        if (op[0] === 'abs') d.abs(); else if (op[0] === 'clone') d = d.clone(); else addTo(d, op[1], op[0] === 'subtract');
    });
    var valid = d.isValid();
    var out = {valid: valid, humanize: d.humanize(), humanizeSuffix: d.humanize(true), humanizeTh: d.humanize(true, {ss: 5, s: 30, m: 30, h: 12, d: 15, w: 3, M: 6}), iso: d.toISOString(), json: d.toJSON(), str: d.toString()};
    ['years', 'months', 'days', 'hours', 'minutes', 'seconds', 'milliseconds', 'weeks'].forEach(function (u) { out[u] = d[u](); out['get_' + u] = d.get(u); });
    ['ms', 's', 'm', 'h', 'd', 'w', 'M', 'Q', 'y'].forEach(function (u) { out['as_' + u] = d.as(u); });
    out.valueOf = d.valueOf();
    cases.push({kind: 'duration', spec: sp, ops: ops, locale: loc, expected: out});
}

// ------------------------------------------------------------------ calendar() with custom formats
var ORIG_CALENDAR_FORMAT = moment.calendarFormat;
var C_LOCALES = ['en', 'fr', 'ru', 'zh-tw', 'ar', 'ja'];
var C_FORMATS = [
    null,
    {sameDay: '[T] LT', sameElse: 'YYYY'},
    {nextWeek: 'dddd', lastWeek: '[L] dddd', lastDay: '[yesterday]'},
    {sameDay: '[sd]', nextDay: '[nd]', nextWeek: '[nw] ddd', lastDay: '[ld]', lastWeek: '[lw] ddd', sameElse: 'L'},
    {sameDay: 'FN_WEEK', nextWeek: 'FN_WEEK', lastWeek: 'FN_WEEK'},
    {sameDay: 'FN_EMPTY', sameElse: 'FN_EMPTY'}
];
function fnFormat(kind) {
    if (kind === 'FN_WEEK') return function (now) { return this.isSame(now, 'week') ? '[same wk] ddd' : '[other wk] ddd'; };
    return function (now) { return ''; };
}
function realFormats(f) {
    if (!f) return undefined;
    var o = {};
    Object.keys(f).forEach(function (k) { o[k] = /^FN_/.test(f[k]) ? fnFormat(f[k]) : f[k]; });
    return o;
}
for (i = 0; i < 1500 * N; i++) {
    var off = pick([-9 * 86400000, -7 * 86400000, -6.5 * 86400000, -86400000, -3600000, -1, 0, 1, 3600000, 5 * 3600000, 14 * 3600000, 86400000, 1.5 * 86400000, 2 * 86400000, 6 * 86400000, 7 * 86400000, 8 * 86400000]);
    var zone = pick(['UTC', 'America/New_York', 'Asia/Kolkata']), cl = pick(C_LOCALES), fmts = pick(C_FORMATS);
    var refOff = pick([null, 0, -86400000, 86400000, 5 * 86400000, 10 * 86400000]);
    var useHook = rnd() < 0.15;
    var m = moment.tz(NOW + off, zone).locale(cl), ref = refOff === null ? undefined : moment.tz(NOW + refOff, zone);
    var form = pick(['none', 'time', 'formats', 'both']);
    if (useHook) moment.calendarFormat = function (mm, now) { return mm.diff(now, 'days') > 1 ? 'nextWeek' : (mm.diff(now, 'days') < -1 ? 'lastDay' : 'sameDay'); };
    var res;
    if (form === 'none') res = m.calendar();
    else if (form === 'time') res = m.calendar(ref);
    else if (form === 'formats') res = m.calendar(realFormats(fmts));
    else res = m.calendar(ref, realFormats(fmts));
    if (useHook) moment.calendarFormat = ORIG_CALENDAR_FORMAT;
    cases.push({kind: 'calendar', locale: cl, zone: zone, offset: off, refOff: refOff, formats: fmts, form: form, hook: useHook, expected: res});
}

// ------------------------------------------------------------------ runtime locales
var NAMES_M = ['Aa', 'Bb', 'Cc', 'Dd', 'Ee', 'Ff', 'Gg', 'Hh', 'Ii', 'Jj', 'Kk', 'Ll'];
function words(prefix, n) { var a = []; for (var q = 0; q < n; q++) a.push(prefix + NAMES_M[q % 12] + (q > 8 ? 'x' : '')); return a; }
var PARENTS = [null, null, 'en', 'fr', 'ru', 'zh-tw', 'de', 'pl'];
function randomConfig(idx) {
    var c = {name: 'zz-r' + idx + '-' + seed, parent: pick(PARENTS)};
    var r = rnd();
    if (rnd() < 0.6) c.months = rnd() < 0.7 ? words('M', 12) : {format: words('F', 12), standalone: words('S', 12), isFormat: 'D[oD]?(\\[[^\\[\\]]*\\]|\\s)+MMMM?'};
    if (rnd() < 0.5) c.monthsShort = words('m', 12);
    if (rnd() < 0.6) c.weekdays = rnd() < 0.7 ? words('W', 7) : {format: words('wf', 7), standalone: words('ws', 7), isFormat: '\\[ ?[Tt] ?\\] ?dddd'};
    if (rnd() < 0.4) c.weekdaysShort = words('w', 7);
    if (rnd() < 0.4) c.weekdaysMin = words('', 7).map(function (x) { return x.slice(0, 2); });
    if (rnd() < 0.6) {
        c.longDateFormat = {}; ['LT', 'LTS', 'L', 'LL', 'LLL', 'LLLL', 'l', 'll'].forEach(function (k) { if (rnd() < 0.5) c.longDateFormat[k] = pick(['HH:mm', 'h:mm A', 'D/M/YY', 'D MMMM [of] YYYY', 'dddd, D MMMM YYYY HH:mm', 'YY.MM.DD', '[x]LT[y]']); });
    }
    if (rnd() < 0.5) { c.calendar = {}; ['sameDay', 'nextDay', 'nextWeek', 'lastDay', 'lastWeek', 'sameElse'].forEach(function (k) { if (rnd() < 0.6) c.calendar[k] = pick(['[a] LT', '[b] dddd', '[c] L', 'dddd [at] LT', 'D MMM']); }); }
    if (rnd() < 0.6) {
        c.relativeTime = {}; ['future', 'past', 's', 'ss', 'm', 'mm', 'h', 'hh', 'd', 'dd', 'w', 'ww', 'M', 'MM', 'y', 'yy'].forEach(function (k) {
            if (rnd() < 0.5) c.relativeTime[k] = k === 'future' ? pick(['in %s', 'soon %s', '%s>']) : (k === 'past' ? pick(['%s ago', '<%s', 'was %s']) : (k.length === 2 && k[0] === k[1] || k === 'ss' ? '%d ' + k : k + '~'));
        });
    }
    var o = rnd();
    if (o < 0.25) c.ordinal = pick(['%dº', '%d.', '#%d']);
    else if (o < 0.5) c.ordinal = {fn: pick(['suffix', 'token'])};
    if (rnd() < 0.5) c.week = {dow: int(0, 6), doy: int(0, 12)};
    if (rnd() < 0.3) c.invalidDate = pick(['bad date', '???']);
    if (rnd() < 0.35) c.meridiem = true;
    return c;
}
function jsConfig(c) {
    var o = {};
    ['months', 'monthsShort', 'weekdays', 'weekdaysShort', 'weekdaysMin', 'longDateFormat', 'calendar', 'relativeTime', 'week', 'invalidDate'].forEach(function (k) {
        if (c[k] === undefined) return;
        o[k] = JSON.parse(JSON.stringify(c[k]));
        if (o[k] && o[k].isFormat) o[k].isFormat = new RegExp(o[k].isFormat);
    });
    if (typeof c.ordinal === 'string') o.ordinal = c.ordinal;
    else if (c.ordinal && c.ordinal.fn === 'suffix') o.ordinal = function (n) { return n + '°'; };
    else if (c.ordinal && c.ordinal.fn === 'token') o.ordinal = function (n, t) { return n + (t === 'M' ? 'ᵐ' : 'ᵈ'); };
    if (c.meridiem) {
        o.meridiem = function (h, mi, isLower) { return h < 12 ? (isLower ? 'vm' : 'VM') : (isLower ? 'nm' : 'NM'); };
        o.isPM = function (input) { return (input + '').toLowerCase().charAt(0) === 'n'; };
        o.meridiemParse = /[vn]m/i;
    }
    if (c.parent) o.parentLocale = c.parent;
    return o;
}
var R_TOKENS = ['MMMM', 'MMM', 'dddd', 'ddd', 'dd', 'Do', 'Mo', 'DDDo', 'wo', 'a', 'A', 'LT', 'LTS', 'L', 'LL', 'LLL', 'LLLL', 'D MMMM', 'MMMM D', '[T] dddd', 'dddd [T]', 'h:mm A', 'w gggg']; // no l/ll: moment caches those derived formats in the locale's settings, which makes results depend on call history
var R_INSTANTS = [Date.UTC(2024, 0, 1, 0, 0), Date.UTC(2024, 1, 29, 13, 5), Date.UTC(2024, 5, 15, 10, 0), Date.UTC(2024, 8, 3, 23, 59), Date.UTC(2024, 11, 31, 11, 30), Date.UTC(2021, 0, 3, 5, 6)];
var R_DUR = [[1, 's'], [30, 's'], [45, 's'], [2, 'm'], [50, 'm'], [1, 'h'], [5, 'h'], [1, 'd'], [10, 'd'], [3, 'w'], [1, 'M'], [8, 'M'], [1, 'y'], [20, 'y']];
function runtimeOutputs(name) {
    var out = {fmt: [], rel: [], cal: [], parse: [], lists: null};
    R_INSTANTS.forEach(function (ms) { var m = moment.utc(ms).locale(name); out.fmt.push(R_TOKENS.map(function (t) { return m.format(t); })); });
    R_DUR.forEach(function (d) { var du = moment.duration(d[0], d[1]).locale(name); out.rel.push([du.humanize(), du.humanize(true), moment.duration(-d[0], d[1]).locale(name).humanize(true)]); });
    [-8, -3, -1, 0, 1, 2, 5, 9].forEach(function (days) { out.cal.push(moment.utc(NOW + days * 86400000 + 3600000).locale(name).calendar(moment.utc(NOW))); });
    ['LLL', 'LL', 'L', 'D MMMM YYYY', 'MMM D YYYY h:mm A', 'dddd'].forEach(function (f) {
        R_INSTANTS.slice(0, 3).forEach(function (ms) {
            var text = moment.utc(ms).locale(name).format(f), p = moment.utc(text, f, name), ps = moment.utc(text, f, name, true);
            out.parse.push([p.isValid() ? p.valueOf() : null, ps.isValid() ? ps.valueOf() : null]);
        });
    });
    var ld = moment.localeData(name);
    out.lists = [ld.months(), ld.monthsShort(), ld.weekdays(), ld.weekdaysShort(), ld.weekdaysMin(), ld.firstDayOfWeek(), ld.firstDayOfYear(), moment.invalid().locale(name).format()];
    return out;
}
for (i = 0; i < 250 * N; i++) {
    var cfg = randomConfig(i);
    var made = moment.defineLocale(cfg.name, jsConfig(cfg));
    var res2 = made ? runtimeOutputs(cfg.name) : null;
    moment.locale('en');
    cases.push({kind: 'defineLocale', config: cfg, expected: res2});
}
// updateLocale on built-in locales, then undone
for (i = 0; i < 120 * N; i++) {
    var base = pick(['en', 'fr', 'ru', 'zh-tw', 'de', 'ar']);
    var upd = randomConfig(i); upd.parent = null; delete upd.name;
    var before = runtimeOutputs(base);
    moment.updateLocale(base, jsConfig(upd));
    var during = runtimeOutputs(base);
    moment.updateLocale(base, null);
    var after = runtimeOutputs(base);
    moment.locale('en');
    cases.push({kind: 'updateLocale', base: base, config: upd, expected: {before: before, during: during, after: after}});
}

fs.writeFileSync(path.join(__dirname, '..', 'tests', 'fixtures', OUT), require('zlib').gzipSync(JSON.stringify(cases), {level: 9}));
console.log('extra', cases.length);
