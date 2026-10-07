// Per-locale golden data from the real moment.js.
//   node tools/generate-locale-fixtures.js            all locales
//   node tools/generate-locale-fixtures.js ru de ar   only these
var fs = require('fs');
var path = require('path');
var moment = require('moment');
moment.now = function () { return Date.UTC(2024, 5, 15, 10, 0, 0); }; // time-only strings are read as "today": keep it fixed

var out = path.join(__dirname, '..', 'tests', 'fixtures', 'locales');
fs.mkdirSync(out, {recursive: true});

var all = fs.readdirSync(path.join(__dirname, 'node_modules', 'moment', 'locale'))
    .filter(function (f) { return /\.js$/.test(f); }).map(function (f) { return f.replace(/\.js$/, ''); });
var names = process.argv.length > 2 ? process.argv.slice(2) : all;
if (names.indexOf('en') < 0 && process.argv.length <= 2) names.unshift('en');

var DATES = [];
for (var i = 0; i < 24; i++) {
    var month = i % 12, day = 1 + (i * 5) % 28, minute = (i * 7) % 60;
    DATES.push(Date.UTC(2024, month, day, i, i % 2 ? 30 : minute, (i * 13) % 60, (i * 111) % 1000));
}
DATES.push(Date.UTC(2024, 0, 31, 12, 0), Date.UTC(2024, 1, 29, 23, 59, 59, 999), Date.UTC(2024, 11, 31, 0, 0), Date.UTC(2021, 0, 3, 5, 6, 7, 8),
    Date.UTC(1999, 6, 4, 18, 0), Date.UTC(2100, 2, 1, 11, 11), Date.UTC(2024, 4, 9, 12, 0), Date.UTC(2024, 4, 9, 0, 0));

var TOKENS = ['MMMM', 'MMM', 'MM', 'M', 'Mo', 'dddd', 'ddd', 'dd', 'd', 'do', 'D', 'DD', 'Do', 'DDDo', 'Qo', 'wo', 'Wo', 'w', 'W', 'a', 'A',
    'h', 'hh', 'H', 'HH', 'k', 'hmm', 'LT', 'LTS', 'L', 'LL', 'LLL', 'LLLL', 'l', 'll', 'lll', 'llll', 'D MMMM', 'MMMM D', 'dddd, D MMMM YYYY',
    'D MMMM YYYY HH:mm', '[x]D MMMM', 'Do MMMM', 'MMM D', 'ddd D MMM', 'YYYY MMMM', 'dddd [at] h:mm A', 'N', 'NNNN', 'y', 'yo', 'gg GG e E', 'D. MMMM', 'D-MMMM-YYYY', 'MMMM [de] D'];

var ORD_DATES = [];
for (var d = 0; d < 366; d += 1) ORD_DATES.push(Date.UTC(2024, 0, 1 + d, 12));
for (var dd = 1; dd <= 31; dd++) ORD_DATES.push(Date.UTC(2024, 0, dd, 12));
var ORD_TOKENS = ['Do', 'DDDo', 'Mo', 'Qo', 'wo', 'Wo', 'do', 'MMMM', 'dddd', 'D MMMM', 'MMMM D'];

var NUMBERS = [1, 2, 3, 4, 5, 6, 10, 11, 12, 13, 14, 20, 21, 22, 24, 25, 30, 31, 44, 45, 59, 100, 101, 111];
var UNITS = ['seconds', 'minutes', 'hours', 'days', 'weeks', 'months', 'years'];

var PARSE_FORMATS = ['LL', 'LLL', 'LLLL', 'L', 'll', 'lll', 'llll', 'LT', 'D MMMM YYYY', 'MMM D YYYY', 'dddd D MMMM YYYY h:mm A', 'MMMM YYYY',
    'YYYY-MM-DD HH:mm', 'Do MMMM YYYY', 'dddd', 'MMMM', 'MMM', 'ddd D', 'D MMMM YYYY, HH:mm:ss', 'h A'];
var PARSE_DATES = [DATES[0], DATES[3], DATES[7], DATES[10], DATES[13], DATES[16], DATES[19], DATES[22], DATES[24], DATES[26]];

names.forEach(function (name) {
    if (name !== 'en') {
        try { require('moment/locale/' + name); } catch (e) { console.error('skip ' + name + ': ' + e.message); return; }
    }
    var ld = moment.localeData(name);
    var rec = {abbr: name, dow: ld.firstDayOfWeek(), doy: ld.firstDayOfYear(), dates: DATES, tokens: TOKENS, format: [], ordDates: ORD_DATES, ordTokens: ORD_TOKENS, ordinals: []};

    DATES.forEach(function (ms) {
        var m = moment.utc(ms).locale(name);
        rec.format.push(TOKENS.map(function (t) { return m.format(t); }));
    });
    ORD_DATES.forEach(function (ms) {
        var m = moment.utc(ms).locale(name);
        rec.ordinals.push(ORD_TOKENS.map(function (t) { return m.format(t); }));
    });

    // lists
    rec.months = ld.months(); rec.monthsShort = ld.monthsShort();
    rec.weekdays = ld.weekdays(); rec.weekdaysShort = ld.weekdaysShort(); rec.weekdaysMin = ld.weekdaysMin();
    rec.weekdaysLocaleSorted = ld.weekdays(true);
    rec.invalid = moment.invalid().locale(name).format();

    // relative time (all keys), with the week threshold on and off
    rec.relative = [];
    // thresholds: weeks off; weeks on; weeks on and days cut short at 5 so that "w" and "ww" are reached
    [[null, 26], [4, 26], [4, 5]].forEach(function (th) {
        var w = th[0];
        moment.relativeTimeThreshold('w', w);
        moment.relativeTimeThreshold('d', th[1]);
        NUMBERS.forEach(function (n) {
            UNITS.forEach(function (u) {
                var du = moment.duration(n, u).locale(name), neg = moment.duration(-n, u).locale(name);
                rec.relative.push({w: w, d: th[1], n: n, u: u, bare: du.humanize(), future: du.humanize(true), past: neg.humanize(true)});
            });
        });
        [0.4, 0.9, 1.4, 1.6].forEach(function (n) {
            UNITS.forEach(function (u) {
                var du = moment.duration(n, u).locale(name);
                rec.relative.push({w: w, d: th[1], n: n, u: u, bare: du.humanize(), future: du.humanize(true), past: moment.duration(-n, u).locale(name).humanize(true)});
            });
        });
    });
    moment.relativeTimeThreshold('w', null);
    moment.relativeTimeThreshold('d', 26);

    // calendar around seven different "now" values (one per weekday), so every nextWeek / lastWeek weekday form is used
    rec.calendarNow = Date.UTC(2024, 5, 12, 10, 0);
    rec.calendar = [];
    for (var wd = 0; wd < 7; wd++) {
        var now = Date.UTC(2024, 5, 9 + wd, 10, 0);
        for (var off = -9 * 24 * 60; off <= 9 * 24 * 60; off += 12 * 60 + 5) {
            var cm = moment.utc(now + off * 60000).locale(name);
            rec.calendar.push({now: now, off: off, text: cm.calendar(moment.utc(now))});
        }
    }

    // every relative-time key, forced with thresholds so any number can reach it
    var FORCE = [
        ['seconds', {ss: 1, s: 1e9}], ['minutes', {ss: -1, s: -1, m: 1e9}], ['hours', {ss: -1, s: -1, m: -1, h: 1e9}],
        ['days', {ss: -1, s: -1, m: -1, h: -1, d: 1e9}], ['weeks', {ss: -1, s: -1, m: -1, h: -1, d: -1, w: 1e9}], ['months', {ss: -1, s: -1, m: -1, h: -1, d: -1, M: 1e9}], ['years', {}]
    ];
    var BIG = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 20, 21, 22, 23, 24, 25, 30, 31, 32, 33, 34, 35, 40, 41, 42, 44, 45, 50, 51, 52, 60, 61, 62, 70, 99, 100, 101, 102, 103, 104, 105, 110, 111, 112, 113, 114, 120, 121, 122, 200, 201, 202, 300, 1000, 1001, 1002, 1003, 1004, 1005, 1011, 1012, 10000, 100000];
    var DEFAULTS = {ss: 44, s: 45, m: 45, h: 22, d: 26, w: null, M: 11};
    rec.force = [];
    FORCE.forEach(function (f) {
        Object.keys(DEFAULTS).forEach(function (k) { moment.relativeTimeThreshold(k, DEFAULTS[k]); });
        Object.keys(f[1]).forEach(function (k) { moment.relativeTimeThreshold(k, f[1][k]); });
        BIG.forEach(function (n) {
            var du = moment.duration(n, f[0]).locale(name);
            rec.force.push({u: f[0], th: f[1], n: n, bare: du.humanize(), future: du.humanize(true), past: moment.duration(-n, f[0]).locale(name).humanize(true)});
        });
    });
    Object.keys(DEFAULTS).forEach(function (k) { moment.relativeTimeThreshold(k, DEFAULTS[k]); });

    // parse round trips
    rec.parse = [];
    PARSE_DATES.forEach(function (ms) {
        PARSE_FORMATS.forEach(function (f) {
            var text = moment.utc(ms).locale(name).format(f);
            [false, true].forEach(function (strict) {
                var p = moment.utc(text, f, name, strict);
                var r = {fmt: f, text: text, strict: strict, valid: p.isValid()};
                if (r.valid) r.ms = p.valueOf();
                rec.parse.push(r);
            });
        });
    });
    // free-form text parsing of names, in lower and upper case
    rec.names = [];
    var nm = [];
    ld.months().forEach(function (x) { nm.push(['MMMM', x]); });
    ld.monthsShort().forEach(function (x) { nm.push(['MMM', x]); });
    ld.weekdays().forEach(function (x) { nm.push(['dddd', x]); });
    ld.weekdaysShort().forEach(function (x) { nm.push(['ddd', x]); });
    ld.weekdaysMin().forEach(function (x) { nm.push(['dd', x]); });
    nm.forEach(function (p) {
        [p[1], p[1].toUpperCase(), p[1].toLowerCase()].forEach(function (text) {
            [false, true].forEach(function (strict) {
                var m = moment.utc(text, p[0], name, strict);
                var r = {fmt: p[0], text: text, strict: strict, valid: m.isValid()};
                if (r.valid) r.ms = m.valueOf();
                rec.names.push(r);
            });
        });
    });

    fs.writeFileSync(path.join(out, name + '.json.gz'), require('zlib').gzipSync(JSON.stringify(rec), {level: 9}));
});
console.log('done', names.length, 'locales');
