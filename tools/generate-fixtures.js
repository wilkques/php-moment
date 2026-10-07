// Generates golden fixtures from the real moment.js. Run: node generate-fixtures.js
var fs = require('fs');
var path = require('path');
var moment = require('moment-timezone');

var out = path.join(__dirname, '..', 'tests', 'fixtures');
fs.mkdirSync(out, {recursive: true});

var INPUTS = [
    '2024-01-31T12:34:56.789Z', '2024-02-29T00:00:00.000Z', '2023-12-31T23:59:59.999Z',
    '2021-01-03T05:06:07.008Z', '2020-12-31T10:00:00.000Z', '2024-03-10T06:59:59.000Z',
    '2024-03-10T07:00:00.000Z', '2024-11-03T05:30:00.000Z', '2024-11-03T06:30:00.000Z',
    '1999-07-04T18:00:00.000Z', '1970-01-01T00:00:00.000Z', '1969-12-31T23:59:59.999Z',
    '2100-02-28T12:00:00.000Z', '0099-05-05T05:05:05.000Z'
];
var ZONES = ['UTC', 'Asia/Taipei', 'America/New_York', 'Europe/London', 'Australia/Lord_Howe'];
var LOCALES = ['en', 'zh-tw', 'zh-cn'];
require('moment/locale/zh-tw');
require('moment/locale/zh-cn');

function mk(iso, zone, locale) {
    return moment.tz(iso, zone).locale(locale || 'en');
}

var FORMATS = [
    'YYYY-MM-DD HH:mm:ss.SSS Z', 'YY Y YYYYY', 'M Mo MM MMM MMMM', 'D Do DD DDD DDDo DDDD',
    'd do dd ddd dddd e E', 'w wo ww W Wo WW', 'gg gggg GG GGGG', 'Q Qo', 'H HH h hh k kk a A',
    'm mm s ss S SS SSS SSSS', 'X x ZZ z', '[escaped YYYY] YYYY \\Q',
    'LT LTS L LL LLL LLLL l ll lll llll', 'hmm Hmm hmmss Hmmss', 'N NNNN y'
];

var format = [];
INPUTS.forEach(function (iso) {
    ZONES.forEach(function (zone) {
        LOCALES.forEach(function (loc) {
            FORMATS.forEach(function (f) {
                var m = mk(iso, zone, loc);
                if (!m.isValid()) return;
                // z/zz need tz data; keep as-is, moment-timezone is the reference
                format.push({iso: iso, zone: zone, locale: loc, format: f, expected: m.format(f)});
            });
        });
    });
});

var UNITS = ['year', 'quarter', 'month', 'week', 'isoWeek', 'day', 'hour', 'minute', 'second', 'millisecond'];
var ADDS = [[1, 'month'], [-1, 'month'], [1, 'year'], [-1, 'year'], [3, 'quarter'], [1, 'week'], [1, 'day'],
    [-1, 'day'], [36, 'hour'], [90, 'minute'], [1500, 'millisecond'], [13, 'month'], [1.5, 'day'], [2.5, 'month']];
var add = [], startEnd = [], diff = [];
INPUTS.forEach(function (iso) {
    ZONES.forEach(function (zone) {
        ADDS.forEach(function (a) {
            var m = mk(iso, zone);
            m.add(a[0], a[1]);
            add.push({iso: iso, zone: zone, amount: a[0], unit: a[1], expected: m.toISOString(), local: m.format()});
        });
        UNITS.forEach(function (u) {
            var s = mk(iso, zone).startOf(u), e = mk(iso, zone).endOf(u);
            startEnd.push({iso: iso, zone: zone, unit: u, start: s.format(), end: e.format(), startMs: s.valueOf(), endMs: e.valueOf()});
        });
    });
});
INPUTS.forEach(function (a) {
    INPUTS.forEach(function (b) {
        ['year', 'quarter', 'month', 'week', 'day', 'hour', 'minute', 'second', 'millisecond'].forEach(function (u) {
            ['UTC', 'America/New_York'].forEach(function (zone) {
                var x = mk(a, zone), y = mk(b, zone);
                diff.push({a: a, b: b, zone: zone, unit: u, expected: x.diff(y, u), expectedFloat: x.diff(y, u, true)});
            });
        });
    });
});

function dump(name, data) {
    fs.writeFileSync(path.join(out, name + '.json'), JSON.stringify(data));
    console.log(name, data.length);
}
var PARSE = [
    // [input, format, locale, strict]
    ['2024-01-31', null], ['2024-01-31T12:34:56', null], ['2024-01-31T12:34:56.789Z', null], ['2024-01-31 12:34', null],
    ['2024-01-31T12:34:56+08:00', null], ['2024-01-31T12:34:56-0530', null], ['20240131', null], ['20240131T123456', null],
    ['2024-W05-3', null], ['2024-W05', null], ['2024-031', null], ['2024-02', null], ['2024', null], ['+002024-01-31', null],
    ['2024-02-30', null], ['2024-13-01', null], ['2024-01-31T25:00', null], ['2024-01-31T24:00:00', null],
    ['Wed, 31 Jan 2024 12:34:56 GMT', null], ['Wed, 31 Jan 2024 12:34:56 +0800', null], ['Tue, 31 Jan 2024 12:34:56 GMT', null],
    ['/Date(1706704496789)/', null], ['', null], ['garbage', null], ['2024-01-31T12:34:56 Z', null],
    ['31/01/2024', 'DD/MM/YYYY'], ['31/01/2024 3:04 PM', 'DD/MM/YYYY h:mm A'], ['12/31/99', 'MM/DD/YY'],
    ['January 5th 2024', 'MMMM Do YYYY'], ['Jan 5 2024', 'MMM D YYYY'], ['Friday Jan 5 2024', 'dddd MMM D YYYY'],
    ['2024-01-31 12:34:56.789', 'YYYY-MM-DD HH:mm:ss.SSS'], ['1706704496', 'X'], ['1706704496789', 'x'],
    ['2024 031', 'YYYY DDD'], ['2024-W05-3', 'GGGG-[W]WW-E'], ['2024 5 3', 'gggg w e'], ['2024-01-31 +08:00', 'YYYY-MM-DD Z'],
    ['12 AM', 'h A'], ['12 PM', 'h A'], ['0 AM', 'h A'], ['13 PM', 'h A'], ['2024-01-31 extra', 'YYYY-MM-DD'],
    ['2024-1-5', 'YYYY-MM-DD', 'en', true], ['2024-01-05', 'YYYY-MM-DD', 'en', true], ['2024-01-05 x', 'YYYY-MM-DD', 'en', true],
    ['2024-01-05', 'YYYY-MM-DD HH', 'en', true], ['2024/01/05', 'YYYY-MM-DD'], ['2024/01/05', 'YYYY-MM-DD', 'en', true],
    ['2024年1月5日', 'YYYY年M月D日', 'zh-tw'], ['2024年1月5日 下午3:04', 'YYYY年M月D日 Ah:mm', 'zh-tw'],
    ['2024年一月5日', 'YYYY年MMMM D日', 'zh-tw'], ['2024年1月5日 晚上9点10分', 'YYYY年M月D日Ah点mm分', 'zh-cn'],
    ['星期五 2024-01-05', 'dddd YYYY-MM-DD', 'zh-tw'], ['2024-01-05', ['YYYY/MM/DD', 'YYYY-MM-DD']], ['05.01.2024', ['YYYY-MM-DD', 'DD.MM.YYYY']],
    ['2024-01-05 7', ['YYYY-MM-DD', 'YYYY-MM-DD H']], ['2024', 'YYYY'], ['24', 'YY'], ['5', 'D'], ['2024-02-29', 'YYYY-MM-DD', 'en', true],
    ['2023-02-29', 'YYYY-MM-DD', 'en', true], ['1130', 'hmm'], ['113045', 'Hmmss'], ['[2024] 01', '[[]YYYY[]] MM']
];
var realNow = moment.now;
moment.now = function () { return Date.UTC(2024, 5, 15, 10, 0, 0); };
var parse = [];
PARSE.forEach(function (c) {
    var input = c[0], fmt = c[1], loc = c[2] || 'en', strict = !!c[3];
    ['UTC', 'Asia/Taipei', 'America/New_York'].forEach(function (zone) {
        var m;
        if (zone === 'UTC') m = fmt ? moment.utc(input, fmt, loc, strict) : moment.utc(input);
        else m = fmt ? moment.tz(input, fmt, loc, strict, zone) : moment.tz(input, zone);
        var rec = {input: input, format: fmt, locale: loc, strict: strict, zone: zone, valid: m.isValid()};
        if (rec.valid) { rec.ms = m.valueOf(); rec.formatted = m.format(); }
        parse.push(rec);
    });
});
moment.now = realNow;
dump('parse', parse);
var DURATIONS = [
    [90, 'minutes'], [1, 'day'], [36, 'hours'], [1.5, 'hours'], [45, 'seconds'], [500, null], [1, 'month'], [13, 'months'],
    [2, 'years'], [-3, 'days'], [-90, 'minutes'], [1, 'week'], [100, 'days'], [400, 'days'], [10, 'seconds'], [1, 'quarter'],
    [{days: 1, hours: 25, minutes: 61}], [{years: 1, months: 2, days: 3, hours: 4, minutes: 5, seconds: 6, milliseconds: 7}],
    [{months: 1, days: -3}], [{hours: 1.5}], ['PT36H'], ['P1Y2M3DT4H5M6.789S'], ['-P1D'], ['P3W'], ['1:30:00'], ['2.12:30:15.5'],
    ['-0:15'], [{from: '2024-01-31T00:00:00.000Z', to: '2024-03-01T12:00:00.000Z'}], [0], [{}], ['bogus'], [{hours: 'x'}],
    [{minutes: 1.5, seconds: 1}], [{days: 0.5}], [{months: 1.5}]
];
var duration = [];
function durInput(d) {
    var x = d[0];
    if (x && typeof x === 'object' && x.from) return {from: moment.utc(x.from), to: moment.utc(x.to)};
    return x;
}
DURATIONS.forEach(function (d) {
    LOCALES.forEach(function (loc) {
        var du = d[1] != null ? moment.duration(d[0], d[1]) : moment.duration(durInput(d));
        du.locale(loc);
        var rec = {input: d, locale: loc, valid: du.isValid()};
        if (rec.valid) {
            ['ms', 's', 'm', 'h', 'd', 'w', 'M', 'Q', 'y'].forEach(function (u) { rec['as_' + u] = du.as(u); });
            ['milliseconds', 'seconds', 'minutes', 'hours', 'days', 'weeks', 'months', 'years'].forEach(function (u) { rec['get_' + u] = du[u](); });
            rec.humanize = du.humanize(); rec.humanizeSuffix = du.humanize(true); rec.iso = du.toISOString();
        } else { rec.humanize = du.humanize(); }
        duration.push(rec);
    });
});

moment.now = function () { return Date.UTC(2024, 5, 15, 10, 0, 0); };
var OFFSETS_MS = [0, 1000, 30000, 44000, 45000, 89000, 90000, 5 * 60000, 44 * 60000, 45 * 60000, 89 * 60000, 90 * 60000, 3 * 3600000,
    21 * 3600000, 22 * 3600000, 35 * 3600000, 36 * 3600000, 86400000, 2 * 86400000, 6 * 86400000, 7 * 86400000, 25 * 86400000,
    26 * 86400000, 45 * 86400000, 320 * 86400000, 345 * 86400000, 400 * 86400000, 800 * 86400000, 4000 * 86400000];
var relative = [];
OFFSETS_MS.forEach(function (off) {
    [-1, 1].forEach(function (sign) {
        LOCALES.forEach(function (loc) {
            ['UTC', 'Asia/Taipei', 'America/New_York'].forEach(function (zone) {
                var base = moment.tz(Date.UTC(2024, 5, 15, 10, 0, 0), zone).locale(loc);
                var m = moment.tz(Date.UTC(2024, 5, 15, 10, 0, 0) + sign * off, zone).locale(loc);
                relative.push({offset: sign * off, locale: loc, zone: zone, fromNow: m.fromNow(), fromNowBare: m.fromNow(true),
                    toNow: m.toNow(), from: m.from(base), calendar: m.calendar(), calendarFmt: m.calendar(null, {sameElse: 'YYYY|MMM'})});
            });
        });
    });
});
moment.now = realNow;
var thr = {s: 40};
var humanizeThr = [];
[30, 41, 50, 100].forEach(function (sec) { humanizeThr.push({sec: sec, out: moment.duration(sec, 'seconds').humanize(true, thr)}); });
dump('duration', duration);
dump('relative', relative);
dump('humanize-thresholds', humanizeThr);

dump('format', format);
dump('add', add);
dump('start-end', startEnd);
dump('diff', diff);
fs.writeFileSync(path.join(out, 'meta.json'), JSON.stringify({moment: moment.version, tz: moment.tz.version, tzdata: moment.tz.dataVersion}));

// Zone data check: offsets, abbreviations and wall-clock parsing at random instants in every zone
(function () {
    var names = moment.tz.names(), samples = [], seed = 12345;
    function rand() { seed = (seed * 1103515245 + 12345) % 2147483648; return seed / 2147483648; }
    for (var i = 0; i < 20000; i++) {
        var n = names[Math.floor(rand() * names.length)], at = Math.floor((rand() * 4 - 2) * 3.15e12), z = moment.tz.zone(n);
        samples.push({zone: n, at: at, offset: z.utcOffset(at), abbr: z.abbr(at), parse: z.parse(at)});
    }
    fs.writeFileSync(path.join(out, 'tzcheck.json.gz'), require('zlib').gzipSync(JSON.stringify(samples), {level: 9}));
})();
