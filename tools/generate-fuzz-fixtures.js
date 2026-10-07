// Seeded random differential tests against the real moment: chains of operations and parse round trips.
// Run with TZ=UTC. Writes tests/fixtures/fuzz.json.gz
var fs = require('fs');
var path = require('path');
var moment = require('moment-timezone');
moment.suppressDeprecationWarnings = true;
var NOW = Date.UTC(2024, 5, 15, 10, 0, 0);
moment.now = function () { return NOW; };
var localeDir = path.join(__dirname, 'node_modules', 'moment', 'locale');
var LOCALES = fs.readdirSync(localeDir).filter(function (f) { return /\.js$/.test(f); }).map(function (f) { return f.replace(/\.js$/, ''); });
LOCALES.forEach(function (n) { require('moment/locale/' + n); });
moment.locale('en');
LOCALES.unshift('en');

var seed = parseInt(process.env.FUZZ_SEED || '20241005', 10);
var CHAINS = parseInt(process.env.FUZZ_CHAINS || '4000', 10), PARSES = parseInt(process.env.FUZZ_PARSE || '3000', 10);
var OUT = process.env.FUZZ_OUT || 'fuzz.json.gz';
function rnd() { seed = (seed * 1664525 + 1013904223) % 4294967296; return seed / 4294967296; }
function pick(a) { return a[Math.floor(rnd() * a.length)]; }
function int(lo, hi) { return lo + Math.floor(rnd() * (hi - lo + 1)); }

var ZONES = ['UTC', 'America/New_York', 'Europe/London', 'Asia/Kolkata', 'Asia/Taipei', 'Australia/Lord_Howe', 'America/Sao_Paulo', 'Pacific/Apia', 'Africa/Casablanca', 'Asia/Tehran', 'Europe/Dublin', 'Pacific/Chatham'];
var UNITS = ['year', 'quarter', 'month', 'week', 'isoWeek', 'day', 'date', 'hour', 'minute', 'second', 'millisecond', 'y', 'Q', 'M', 'w', 'W', 'd', 'D', 'h', 'm', 's', 'ms'];
var SET_UNITS = ['year', 'month', 'date', 'day', 'weekday', 'isoWeekday', 'dayOfYear', 'hour', 'minute', 'second', 'millisecond', 'quarter', 'week', 'isoWeek', 'weekYear', 'isoWeekYear'];
var GETSET = ['year', 'month', 'date', 'hour', 'minute', 'second', 'millisecond', 'day', 'weekday', 'isoWeekday', 'dayOfYear', 'week', 'isoWeek', 'quarter', 'weekYear', 'isoWeekYear'];
var TOKENS = ['YYYY', 'YY', 'Y', 'MMMM', 'MMM', 'MM', 'M', 'Mo', 'DD', 'D', 'Do', 'DDDD', 'DDD', 'DDDo', 'dddd', 'ddd', 'dd', 'd', 'do', 'e', 'E', 'ww', 'w', 'wo', 'WW', 'W', 'Wo', 'gggg', 'gg', 'GGGG', 'GG', 'Q', 'Qo',
    'HH', 'H', 'hh', 'h', 'kk', 'k', 'mm', 'm', 'ss', 's', 'SSS', 'SS', 'S', 'SSSS', 'a', 'A', 'Z', 'ZZ', 'z', 'x', 'X', 'LT', 'LTS', 'L', 'LL', 'LLL', 'LLLL', 'l', 'll', 'lll', 'llll', 'N', 'NNNN', 'y', ' ', '-', '/', ':', ',', '[at]', '.', 'T'];
var F = 'YYYY-MM-DDTHH:mm:ss.SSSZ';

function randomMs() {
    var kind = rnd();
    if (kind < 0.5) return Date.UTC(int(1990, 2040), int(0, 11), int(1, 31), int(0, 23), int(0, 59), int(0, 59), int(0, 999));
    if (kind < 0.8) return Date.UTC(2024, pick([2, 3, 9, 10]), int(1, 31), int(0, 23), pick([0, 30, 59]), pick([0, 59]), pick([0, 999])) ; // near DST changes
    return int(-6e12, 6e12);
}
function randomOp() {
    var k = int(0, 11);
    switch (k) {
        case 0: return ['add', pick([-25, -3, -1, 0, 1, 2, 5, 13, 0.5, 1.5, 2.7]), pick(UNITS)];
        case 1: return ['subtract', pick([-3, -1, 0, 1, 7, 12, 30, 0.25]), pick(UNITS)];
        case 2: return ['startOf', pick(UNITS)];
        case 3: return ['endOf', pick(UNITS)];
        case 4: return ['set', pick(SET_UNITS), pick([-1, 0, 1, 5, 12, 28, 29, 30, 31, 32, 59, 60, 100, 366, 2000, 2024])];
        case 5: return [pick(GETSET), pick([-1, 0, 1, 7, 11, 12, 23, 24, 28, 31, 53, 59, 60, 366, 1999, 2024])];
        case 6: return ['utcOffset', pick([0, 60, 480, -300, 330, 8, -5, '+05:30', '-08:00', 'Z']), pick([false, true])];
        case 7: return ['tz', pick(ZONES), pick([false, true])];
        case 8: return pick([['utc', false], ['utc', true], ['local', false], ['local', true]]);
        case 9: return ['locale', pick(LOCALES)];
        case 10: return ['add', pick([1, 2, 3]), pick(['month', 'year', 'week', 'day'])];
        default: return ['startOf', pick(['day', 'month', 'week'])];
    }
}
function apply(m, op) {
    var name = op[0], args = op.slice(1);
    var r = m[name].apply(m, args);
    return r;
}
function snapshot(m, extraFormat, orig, dunit) {
    var o = moment.tz(orig.ms, orig.zone);
    var other = m.isValid() ? [m.diff(o, dunit), m.diff(o, dunit, true), m.fromNow(), m.calendar(), m.toISOString(), m.isBefore(o), m.isSame(o, dunit)] : null;
    return [m.format(F), m.valueOf(), m.format('LLLL'), m.format(extraFormat), m.isValid(), m.utcOffset(), m.format('z'), other];
}

var cases = [];
for (var i = 0; i < CHAINS; i++) {
    var zone = pick(ZONES), loc = pick(LOCALES), ms = randomMs();
    var n = int(1, 4), ops = [];
    for (var j = 0; j < n; j++) ops.push(randomOp());
    var fmt = []; for (var t = 0; t < int(2, 7); t++) fmt.push(pick(TOKENS));
    fmt = fmt.join(pick(['', ' ', '-']));
    var m = moment.tz(ms, zone).locale(loc), res = [];
    ops.forEach(function (op) { var r = apply(m, op); res.push(r === m ? null : (typeof r === 'number' && isNaN(r) ? null : r)); });
    var dunit = pick(['year', 'quarter', 'month', 'week', 'day', 'hour', 'minute', 'second', 'millisecond']);
    cases.push({kind: 'chain', zone: zone, locale: loc, ms: ms, ops: ops, fmt: fmt, dunit: dunit, got: res, expected: snapshot(m, fmt, {ms: ms, zone: zone}, dunit)});
}

// parse round trips: format a moment in some pattern, damage it sometimes, parse it back
var PARSE_PATTERNS = ['YYYY-MM-DD', 'YYYY-MM-DD HH:mm:ss', 'YYYY-MM-DD HH:mm:ss.SSS Z', 'DD/MM/YYYY', 'MM/DD/YY', 'D MMMM YYYY', 'MMM D, YYYY h:mm A', 'dddd, D MMMM YYYY', 'Do MMMM YYYY HH:mm',
    'YYYY-DDDD', 'GGGG-[W]WW-E', 'gggg wo dddd', 'YYYY[年]M[月]D[日]', 'hh:mm:ss a', 'H:m:s', 'YYYYMMDD', 'YYYYMMDDHHmmss', 'X', 'x', 'LL', 'LLL', 'LLLL', 'l', 'lll', 'LT', 'LTS', 'MMMM YYYY', 'Q YYYY', 'ddd MMM DD YYYY HH:mm:ss [GMT]ZZ', 'ZZ', 'YYYY-MM-DDTHH:mm:ssZ'];
for (var k = 0; k < PARSES; k++) {
    var zone2 = pick(ZONES), loc2 = pick(LOCALES), ms2 = randomMs();
    if (rnd() < 0.7) ms2 = Date.UTC(int(1950, 2060), int(0, 11), int(1, 31), int(0, 23), int(0, 59), int(0, 59), int(0, 999));
    var pattern = pick(PARSE_PATTERNS);
    var text = moment.tz(ms2, zone2).locale(loc2).format(pattern);
    var dmg = rnd();
    if (dmg < 0.2 && text.length > 2) { var p = int(0, text.length - 1); text = text.slice(0, p) + text.slice(p + 1); }
    else if (dmg < 0.3) text = text + ' x';
    else if (dmg < 0.35) text = text.toUpperCase();
    else if (dmg < 0.4) text = text.toLowerCase();
    var strict = rnd() < 0.4, mode = pick(['utc', 'tz', 'local']);
    var p2 = mode === 'utc' ? moment.utc(text, pattern, loc2, strict) : (mode === 'tz' ? moment.tz(text, pattern, loc2, strict, zone2) : moment(text, pattern, loc2, strict));
    var rec = {kind: 'parse', zone: zone2, locale: loc2, text: text, pattern: pattern, strict: strict, mode: mode, valid: p2.isValid()};
    if (rec.valid) { rec.ms = p2.valueOf(); rec.fmt = p2.format(F); }
    cases.push(rec);
}
fs.writeFileSync(path.join(__dirname, '..', 'tests', 'fixtures', OUT), require('zlib').gzipSync(JSON.stringify(cases), {level: 9}));
console.log('fuzz', cases.length);
