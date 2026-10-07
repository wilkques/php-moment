// Direct calls of each locale's functions (relativeTime, calendar, ordinal, meridiem, isPM, preparse, postformat,
// pastFuture), including combinations humanize() never produces, so every branch of a locale is checked against
// the real moment.js. Writes tests/fixtures/localefn/<locale>.json.gz
//   node tools/generate-localefn-fixtures.js            all locales
//   node tools/generate-localefn-fixtures.js ru de      only these
var fs = require('fs');
var path = require('path');
var moment = require('moment');
var NOW = Date.UTC(2024, 5, 15, 10, 0, 0);
moment.now = function () { return NOW; };

var out = path.join(__dirname, '..', 'tests', 'fixtures', 'localefn');
fs.mkdirSync(out, {recursive: true});
var all = fs.readdirSync(path.join(__dirname, 'node_modules', 'moment', 'locale'))
    .filter(function (f) { return /\.js$/.test(f); }).map(function (f) { return f.replace(/\.js$/, ''); });
var names = process.argv.length > 2 ? process.argv.slice(2) : ['en'].concat(all);

var KEYS = ['s', 'ss', 'm', 'mm', 'h', 'hh', 'd', 'dd', 'w', 'ww', 'M', 'MM', 'y', 'yy'];
var NUMBERS = [0, 1, 2, 3, 4, 5, 6, 10, 11, 12, 14, 15, 20, 21, 22, 25, 100, 101, 102, 105, 111, 112, 1000, 1002, 10000, 100000];
var CAL_KEYS = ['sameDay', 'nextDay', 'nextWeek', 'lastDay', 'lastWeek', 'sameElse'];
var PERIODS = ['D', 'M', 'd', 'h', 'm', 's', 'w', 'W', 'Q', 'DDD', 'y', 'Y', 'DDDD', ''];
var ORD_NUMBERS = [];
for (var n = 0; n <= 40; n++) ORD_NUMBERS.push(n);
ORD_NUMBERS.push(50, 99, 100, 101, 111, 112, 113, 121, 200, 365, 1000);
var TEXTS = ['', '0123456789', '1,234.5', 'abc ١٢٣ १२३', '2024-01-05 10:30', '3', '٣٠', '๑๒', '১২', '12, 34', '1 2 3', 'x، y'];

function safe(f) {
    try { var r = f(); return r === undefined ? null : (typeof r === 'number' && !isFinite(r) ? String(r) : r); } catch (e) { return 'THROWS'; }
}

names.forEach(function (name) {
    if (name !== 'en') require('moment/locale/' + name);
    var loc = moment.localeData(name);
    var rec = {};

    rec.relativeTime = [];
    KEYS.forEach(function (k) { NUMBERS.forEach(function (num) {
        [true, false].forEach(function (wo) { [true, false].forEach(function (fut) {
            rec.relativeTime.push(safe(function () { return loc.relativeTime(num, wo, k, fut); }));
        }); });
    }); });

    rec.pastFuture = [];
    [-1000, -1, 0, 1, 1000].forEach(function (diff) { rec.pastFuture.push(safe(function () { return loc.pastFuture(diff, 'X'); })); });

    rec.calendar = [];
    for (var wd = 0; wd < 7; wd++) {
        var mom = moment.utc(Date.UTC(2024, 5, 9 + wd, 12, 0)).locale(name);
        var nowm = moment.utc(NOW).locale(name);
        CAL_KEYS.forEach(function (k) { rec.calendar.push(safe(function () { return loc.calendar(k, mom, nowm); })); });
    }

    rec.ordinal = [];
    PERIODS.forEach(function (p) { ORD_NUMBERS.forEach(function (num) { rec.ordinal.push(safe(function () { return loc.ordinal(num, p); })); }); });

    rec.meridiem = [];
    var seen = {};
    for (var h = 0; h < 24; h++) { [0, 30, 59].forEach(function (mi) { [true, false].forEach(function (lower) {
        var v = safe(function () { return loc.meridiem(h, mi, lower); });
        rec.meridiem.push(v);
        if (typeof v === 'string') seen[v] = true;
    }); }); }
    rec.isPMInputs = Object.keys(seen).concat(['am', 'pm', 'AM', 'PM', 'a.m.', 'p.m.', 'x', '']);
    rec.isPM = rec.isPMInputs.map(function (s) { return safe(function () { return loc.isPM(s); }); });

    // meridiemHour(hour, meridiem) with every meridiem word the locale prints, plus unknown ones
    rec.hasMeridiemHour = typeof loc.meridiemHour === 'function';
    rec.meridiemWords = Object.keys(seen).concat(['x']);
    rec.meridiemHour = [];
    if (rec.hasMeridiemHour) {
        rec.meridiemWords.forEach(function (w) { [0, 1, 2, 5, 6, 11, 12, 13].forEach(function (hh) { rec.meridiemHour.push(safe(function () { return loc.meridiemHour(hh, w); })); }); });
    }

    // month and weekday names for every format string a locale may branch on
    var FMTS = ['', 'MMM', 'D MMM', 'D-MMM-YYYY', 'MMMM', 'D MMMM', 'dddd', 'dddd D', 'dddd, MMMM', 'D-MMMM', '[x] MMMM', 'LL', 'ddd', 'dd', 'MMMM D', 'D. MMMM', 'MMMM [de] YYYY'];
    rec.fmts = FMTS;
    rec.names = [];
    for (var mo = 0; mo < 12; mo++) {
        var mm = moment.utc(Date.UTC(2024, mo, 10)).locale(name);
        FMTS.forEach(function (fmt) {
            rec.names.push(safe(function () { return loc.months(mm, fmt); }), safe(function () { return loc.monthsShort(mm, fmt); }));
        });
    }
    for (var wdd = 0; wdd < 7; wdd++) {
        var wm = moment.utc(Date.UTC(2024, 5, 9 + wdd)).locale(name);
        FMTS.forEach(function (fmt) {
            rec.names.push(safe(function () { return loc.weekdays(wm, fmt); }), safe(function () { return loc.weekdaysShort(wm, fmt); }), safe(function () { return loc.weekdaysMin(wm, fmt); }));
        });
    }
    rec.lists = [safe(function () { return loc.months(); }), safe(function () { return loc.monthsShort(); }), safe(function () { return loc.weekdays(); }), safe(function () { return loc.weekdaysShort(); }),
        safe(function () { return loc.weekdaysMin(); }), safe(function () { return loc.weekdays(true); }), safe(function () { return loc.weekdaysShort(true); }), safe(function () { return loc.weekdaysMin(true); })];

    rec.preparse = TEXTS.map(function (t) { return safe(function () { return loc.preparse(t); }); });
    rec.postformat = TEXTS.map(function (t) { return safe(function () { return loc.postformat(t); }); });
    rec.texts = TEXTS;
    rec.keys = KEYS; rec.numbers = NUMBERS; rec.calKeys = CAL_KEYS; rec.periods = PERIODS; rec.ordNumbers = ORD_NUMBERS;

    fs.writeFileSync(path.join(out, name + '.json.gz'), require('zlib').gzipSync(JSON.stringify(rec), {level: 9}));
});
moment.locale('en');
console.log('done', names.length, 'locales');
