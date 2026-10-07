// Cross-product golden data: locale x zone, week rules, get/set of every unit, add/diff/startOf across DST,
// offsets, humanize variants, odd format strings, comparisons. Run with TZ=UTC. Writes tests/fixtures/cross.json.gz
var fs = require('fs');
var path = require('path');
var moment = require('moment-timezone');
moment.suppressDeprecationWarnings = true;
var NOW = Date.UTC(2024, 5, 15, 10, 0, 0);
moment.now = function () { return NOW; };

var localeDir = path.join(__dirname, 'node_modules', 'moment', 'locale');
var ALL = fs.readdirSync(localeDir).filter(function (f) { return /\.js$/.test(f); }).map(function (f) { return f.replace(/\.js$/, ''); });
ALL.forEach(function (n) { require('moment/locale/' + n); });
moment.locale('en');
ALL.unshift('en');

var cases = [];
function add(c) { cases.push(c); }
function iso(s) { return moment.utc(s).valueOf(); }

// ---------------------------------------------------------------- A. every locale x zone x DST edge
var ZONES = ['UTC', 'America/New_York', 'Asia/Kolkata', 'Australia/Lord_Howe', 'Europe/London'];
var INSTANTS = ['2024-03-10T06:59:59Z', '2024-03-10T07:00:01Z', '2024-11-03T05:59:59Z', '2024-11-03T06:00:01Z', '2024-04-06T14:59:59Z',
    '2024-10-27T00:59:59Z', '2024-06-15T10:00:00Z', '2023-12-31T23:59:59.999Z'].map(iso);
function opsA(m, loc, zone) {
    var text = m.format('LLLL'), text2 = m.format('L LTS Z');
    var rt1 = moment.tz(text, 'LLLL', loc, zone), rt2 = moment.tz(text2, 'L LTS Z', loc, zone);
    return [m.format('LLLL'), m.format('llll'), m.format('dddd Do MMMM YYYY HH:mm:ss.SSS Z z'), m.format('LTS'), m.format('Wo gggg Qo'), m.format('x X'),
        m.fromNow(), m.calendar(), m.clone().startOf('day').format('LLL'), m.clone().endOf('week').format('L LT'), m.clone().add(1, 'day').format('llll'),
        m.clone().add(1, 'month').format('llll Z'), m.clone().subtract(1, 'hour').format('LTS Z'), rt1.isValid() ? rt1.valueOf() : null, rt2.isValid() ? rt2.valueOf() : null];
}
ALL.forEach(function (loc) {
    var out = [];
    ZONES.forEach(function (zone) { out.push(INSTANTS.map(function (ms) { return opsA(moment.tz(ms, zone).locale(loc), loc, zone); })); });
    add({kind: 'localeZone', locale: loc, zones: ZONES, instants: INSTANTS, expected: out});
});

// ---------------------------------------------------------------- B. week rules
var seen = {}, reps = [];
ALL.forEach(function (l) { var d = moment.localeData(l), k = d.firstDayOfWeek() + ',' + d.firstDayOfYear(); if (!seen[k]) { seen[k] = 1; reps.push(l); } });
var WEEK_DATES = ['2020-12-28', '2020-12-31', '2021-01-01', '2021-01-03', '2021-01-04', '2024-12-26', '2024-12-30', '2025-01-01', '2016-12-30', '2016-01-02', '2015-12-31', '2026-01-01', '2024-02-29', '2024-07-04'];
function D(m) { return m.isValid() ? m.format('YYYY-MM-DD') : 'invalid'; }
function opsB(b) {
    return [b.week(), b.weekYear(), b.weekday(), b.isoWeek(), b.isoWeekYear(), b.weeksInYear(), b.weeksInWeekYear(), b.isoWeeksInYear(), b.isoWeeksInISOWeekYear(), b.dayOfYear(), b.quarter(),
        D(b.clone().startOf('week')), D(b.clone().endOf('week')), D(b.clone().startOf('isoWeek')), D(b.clone().week(1)), D(b.clone().week(2)), D(b.clone().week(52)), D(b.clone().week(53)),
        D(b.clone().weekYear(2020)), D(b.clone().weekYear(2021)), D(b.clone().weekday(0)), D(b.clone().weekday(6)), D(b.clone().isoWeekday(1)), D(b.clone().isoWeekday(7)),
        D(b.clone().isoWeek(1)), D(b.clone().isoWeek(53)), D(b.clone().isoWeekYear(2026)), D(b.clone().dayOfYear(1)), D(b.clone().dayOfYear(366)), D(b.clone().day(0)), D(b.clone().day('Monday'))];
}
reps.forEach(function (loc) {
    add({kind: 'weeks', locale: loc, dates: WEEK_DATES, expected: WEEK_DATES.map(function (d) { return opsB(moment.utc(d).locale(loc)); })});
});

// ---------------------------------------------------------------- C. get/set every unit incl. overflow
var UNITS = ['year', 'month', 'date', 'day', 'weekday', 'isoWeekday', 'dayOfYear', 'hour', 'minute', 'second', 'millisecond', 'quarter', 'week', 'isoWeek', 'weekYear', 'isoWeekYear',
    'y', 'M', 'D', 'd', 'e', 'E', 'DDD', 'h', 'm', 's', 'ms', 'Q', 'w', 'W', 'gg', 'GG', 'years', 'months', 'dates', 'days'];
var VALUES = [-1, 0, 1, 5, 12, 13, 28, 29, 30, 31, 32, 59, 60, 100, 366, 367, 1999, 2000, 2024];
var SET_BASE = [['2024-01-31T12:34:56.789', 'UTC'], ['2024-03-10T01:30:00.000', 'America/New_York'], ['2024-04-07T01:45:00.000', 'Australia/Lord_Howe'], ['2023-12-31T23:59:59.999', 'Asia/Kolkata']];
var F = 'YYYY-MM-DDTHH:mm:ss.SSSZ';
SET_BASE.forEach(function (b) {
    var out = {};
    UNITS.forEach(function (u) {
        out[u] = {get: moment.tz(b[0], b[1]).get(u), set: VALUES.map(function (v) { var m = moment.tz(b[0], b[1]); m.set(u, v); return m.format(F); })};
    });
    add({kind: 'setget', base: b[0], zone: b[1], units: UNITS, values: VALUES, expected: out});
});
[{year: 2024, month: 1, date: 29}, {month: 11, year: 2023, day: 5}, {hour: 25, minute: 61, second: 61}, {weekYear: 2021, week: 1, weekday: 2}, {isoWeekYear: 2020, isoWeek: 53, isoWeekday: 7},
    {dayOfYear: 366, year: 2023}, {quarter: 3, date: 31}, {M: 1, D: 31, y: 2023}, {millisecond: 1500}].forEach(function (o) {
    SET_BASE.forEach(function (b) {
        var m = moment.tz(b[0], b[1]); m.set(o);
        add({kind: 'setObject', base: b[0], zone: b[1], obj: o, expected: m.format(F)});
    });
});

// ---------------------------------------------------------------- D. add / diff / startOf / endOf across DST
var ADD_UNITS = ['year', 'quarter', 'month', 'week', 'day', 'hour', 'minute', 'second', 'millisecond', 'y', 'Q', 'M', 'w', 'd', 'h', 'm', 's', 'ms', 'isoWeek', 'date'];
var AMOUNTS = [-13, -1, 0, 0.5, 1, 2.7, 25];
var D_ZONES = ['UTC', 'America/New_York', 'Australia/Lord_Howe', 'Asia/Kolkata', 'Europe/London'];
var D_BASES = ['2024-03-09T02:30:00Z', '2024-03-10T06:30:00Z', '2024-11-02T05:30:00Z', '2024-11-03T05:30:00Z', '2024-01-31T12:00:00Z', '2024-02-29T00:00:00Z', '2024-10-26T23:30:00Z', '2023-12-31T23:59:59.999Z'].map(iso);
D_ZONES.forEach(function (zone) {
    var out = D_BASES.map(function (ms) {
        var base = moment.tz(ms, zone);
        return {
            add: ADD_UNITS.map(function (u) { return AMOUNTS.map(function (a) { return base.clone().add(a, u).format(F); }); }),
            sub: ADD_UNITS.map(function (u) { return AMOUNTS.map(function (a) { return base.clone().subtract(a, u).format(F); }); }),
            start: UNITS.slice(0, 0).concat(['year', 'quarter', 'month', 'week', 'isoWeek', 'day', 'date', 'hour', 'minute', 'second', 'millisecond', 'y', 'w', 'd']).map(function (u) { return [base.clone().startOf(u).format(F), base.clone().endOf(u).format(F)]; }),
            diff: D_BASES.map(function (other) {
                return ['year', 'quarter', 'month', 'week', 'day', 'hour', 'minute', 'second', 'millisecond', 'y', 'M', 'w', 'd', 'h'].map(function (u) {
                    return [base.diff(moment.tz(other, zone), u), base.diff(moment.tz(other, zone), u, true)];
                });
            })
        };
    });
    add({kind: 'addDiff', zone: zone, bases: D_BASES, addUnits: ADD_UNITS, amounts: AMOUNTS, expected: out});
});

// ---------------------------------------------------------------- E. offsets and zone switching
var OFFSET_OPS = [['utcOffset', 0], ['utcOffset', 480], ['utcOffset', -330], ['utcOffset', 8], ['utcOffset', -8], ['utcOffset', '+05:30'], ['utcOffset', '-0800'], ['utcOffset', 'Z'], ['utcOffset', '+08'],
    ['utcOffset', 480, true], ['utcOffset', '+05:30', true], ['utcOffset', 8, true, true], ['utcOffset', 90, false, true], ['tz', 'Asia/Taipei'], ['tz', 'America/New_York'], ['tz', 'America/New_York', true],
    ['tz', 'Europe/London', true], ['tz', 'Nope/Zone'], ['local'], ['local', true], ['utc'], ['utc', true]];
['2024-06-15T10:00:00Z', '2024-01-31T23:30:00Z', '2024-03-10T06:59:59Z'].forEach(function (s) {
    [['utc', null], ['tz', 'America/New_York'], ['tz', 'Asia/Kolkata'], ['offset', 540]].forEach(function (mode) {
        var res = OFFSET_OPS.map(function (op) {
            var m = mode[0] === 'utc' ? moment.utc(s) : (mode[0] === 'tz' ? moment.tz(s, mode[1]) : moment.utc(s).utcOffset(mode[1]));
            m[op[0]].apply(m, op.slice(1));
            return [m.format('YYYY-MM-DD HH:mm:ss Z z'), m.valueOf(), m.utcOffset(), m.isUtc(), m.isUtcOffset(), m.isLocal(), m.tz() === undefined ? null : m.tz()];
        });
        add({kind: 'offsets', iso: s, mode: mode, ops: OFFSET_OPS, expected: res});
    });
});

// ---------------------------------------------------------------- F. humanize variants in several locales
var H_LOCALES = ['en', 'ru', 'ar', 'zh-tw', 'fr', 'pl', 'ja', 'de', 'cs', 'he', 'lt', 'hr'];
var H_DURATIONS = [[1, 's'], [44, 's'], [45, 's'], [89, 's'], [90, 's'], [44, 'm'], [45, 'm'], [89, 'm'], [90, 'm'], [21, 'h'], [22, 'h'], [35, 'h'], [36, 'h'], [25, 'd'], [26, 'd'], [29, 'd'], [30, 'd'], [45, 'd'], [10, 'w'],
    [3, 'w'], [4, 'w'], [11, 'M'], [10, 'M'], [18, 'M'], [2, 'y'], [100, 'y'], [-90, 'm'], [-26, 'd'], [-2, 'y'], [1.5, 'h'], [0, 's']];
H_LOCALES.forEach(function (loc) {
    var out = [];
    H_DURATIONS.forEach(function (d) {
        var du = moment.duration(d[0], d[1]).locale(loc);
        var row = [du.humanize(), du.humanize(true), du.humanize(true, {ss: 5, s: 30, m: 30, h: 12, d: 15, w: 3, M: 6}), du.humanize({s: 50}), du.humanize(false, {w: 4})];
        moment.relativeTimeRounding(Math.floor); row.push(du.humanize(true)); moment.relativeTimeRounding(Math.round);
        moment.relativeTimeThreshold('w', 4); row.push(du.humanize()); moment.relativeTimeThreshold('w', null);
        moment.relativeTimeThreshold('d', 15); moment.relativeTimeThreshold('M', 6); row.push(du.humanize(true)); moment.relativeTimeThreshold('d', 26); moment.relativeTimeThreshold('M', 11);
        out.push(row);
    });
    add({kind: 'humanize', locale: loc, durations: H_DURATIONS, expected: out});
});

// ---------------------------------------------------------------- G. odd format strings
var FORMATS = ['', '[[]', '\\[', 'Do [of] MMMM', '[YYYY]YYYY', 'YYYYYYYY', 'SSSSSSSSS', 'x X', 'ZZ Z', 'zz z', 'hh:mm:ss a A', 'kk k', 'dddd[,] D', '[ ]', 'LL[x]L', 'l ll lll llll', 'é日本',
    'Q Qo', 'ggggg GGGGG', 'N NNNN NNNNN y yy yyy yyyy yo', 'LT[LT]LT', 'MMMMM', 'DDDDD', 'dddddd', 'wwo', 'hmm Hmm hmmss Hmmss', 'a[A]a', 'ssss', 'Zz', 'YYYYY-YYYYYY'];
var F_INSTANTS = [iso('0000-01-01T00:00:00Z'), iso('-000001-06-15T12:00:00Z'), iso('+010000-01-01T00:00:00Z'), iso('+275760-09-13T00:00:00Z'), iso('2024-02-29T23:59:59.999Z'), iso('1969-12-31T23:59:59.999Z'), iso('0099-12-31T23:59:59Z')];
['en', 'zh-tw', 'ru', 'ar', 'ja'].forEach(function (loc) {
    var out = ['UTC', 'America/New_York'].map(function (zone) {
        return F_INSTANTS.map(function (ms) { var m = moment.tz(ms, zone).locale(loc); return FORMATS.map(function (f) { return m.format(f); }); });
    });
    add({kind: 'formats', locale: loc, formats: FORMATS, instants: F_INSTANTS, zones: ['UTC', 'America/New_York'], expected: out});
});

// ---------------------------------------------------------------- H. comparisons
var C_INST = ['2024-01-31T23:59:59.999Z', '2024-02-01T00:00:00.000Z', '2024-02-01T00:00:00.001Z', '2024-12-31T23:59:59.999Z', '2025-01-01T00:00:00.000Z', '2024-03-10T06:59:59Z', '2024-03-10T07:00:00Z', '2024-02-04T23:59:59Z', '2024-02-05T00:00:00Z'].map(iso);
var C_UNITS = [undefined, 'year', 'quarter', 'month', 'week', 'isoWeek', 'day', 'hour', 'minute', 'second', 'millisecond', 'd', 'M'];
['UTC', 'Asia/Kolkata', 'America/New_York'].forEach(function (zone) {
    var rows = [];
    C_INST.forEach(function (a) {
        C_INST.forEach(function (b) {
            var ma = moment.tz(a, zone), row = [];
            C_UNITS.forEach(function (u) { row.push([ma.isSame(b, u), ma.isBefore(b, u), ma.isAfter(b, u), ma.isSameOrBefore(b, u), ma.isSameOrAfter(b, u)]); });
            rows.push(row);
        });
    });
    var between = [];
    [[0, 1, 2], [1, 0, 2], [0, 3, 4], [5, 6, 6], [7, 8, 3]].forEach(function (t) {
        ['()', '[]', '[)', '(]', undefined].forEach(function (inc) {
            C_UNITS.forEach(function (u) { between.push(moment.tz(C_INST[t[0]], zone).isBetween(C_INST[t[1]], C_INST[t[2]], u, inc)); });
        });
    });
    var minmax = [moment.min(C_INST.map(function (x) { return moment.tz(x, zone); })).valueOf(), moment.max(C_INST.map(function (x) { return moment.tz(x, zone); })).valueOf()];
    add({kind: 'compare', zone: zone, instants: C_INST, units: C_UNITS.map(function (u) { return u === undefined ? null : u; }), expected: {rows: rows, between: between, minmax: minmax}});
});

fs.writeFileSync(path.join(__dirname, '..', 'tests', 'fixtures', 'cross.json.gz'), require('zlib').gzipSync(JSON.stringify(cases), {level: 9}));
console.log('cross', cases.length);
