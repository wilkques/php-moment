// Golden data for the less common API: aliases, era getters, parse flags, creationData, parseZone,
// static helpers, deprecated spellings, and the moment-timezone data API. Writes tests/fixtures/api.json
var fs = require('fs');
var path = require('path');
var moment = require('moment-timezone');
moment.suppressDeprecationWarnings = true;
moment.now = function () { return Date.UTC(2024, 5, 15, 10, 0, 0); };
require('moment/locale/zh-tw'); require('moment/locale/fr'); require('moment/locale/ru'); require('moment/locale/ar');
moment.locale('en'); // requiring a locale file makes it the global locale
var cases = [];
function add(c) { cases.push(c); }

var ISOS = ['2024-01-31T12:34:56.789Z', '2024-07-04T23:00:00.000Z', '2021-01-03T05:06:07.008Z', '0001-06-01T00:00:00.000Z', '-000050-03-01T00:00:00.000Z', '2024-03-10T07:30:00.000Z'];
var ZONES = ['UTC', 'America/New_York', 'Asia/Kolkata', 'Asia/Taipei', 'Europe/London'];

// ---- instance methods without arguments
['weeksInWeekYear', 'isoWeeksInISOWeekYear', 'eraName', 'eraAbbr', 'eraNarrow', 'eraYear', 'isDST', 'inspect', 'invalidAt', 'dates', 'days',
    'hours', 'minutes', 'seconds', 'milliseconds', 'months', 'years', 'weeks', 'isoWeeks', 'quarters', 'zone', 'weekYear', 'isoWeekYear',
    'hasAlignedHourOffset', 'isUTC'].forEach(function (method) {
    ISOS.forEach(function (iso) {
        ZONES.forEach(function (zone) {
            ['en', 'zh-tw', 'fr'].forEach(function (loc) {
                var m = moment.tz(iso, zone).locale(loc);
                if (!m.isValid()) return;
                add({kind: 'method', iso: iso, zone: zone, locale: loc, method: method, args: [], expected: m[method]()});
            });
        });
    });
});
[[null], [0], [480], ['+05:30'], [330]].forEach(function (args) {
    ISOS.slice(0, 3).forEach(function (iso) {
        ZONES.forEach(function (zone) {
            var m = moment.tz(iso, zone);
            add({kind: 'method', iso: iso, zone: zone, locale: 'en', method: 'hasAlignedHourOffset', args: args, expected: m.hasAlignedHourOffset(args[0] === null ? undefined : args[0])});
        });
    });
});

// ---- creation: values + flags
var CREATE = [
    ['2024-01-31'], ['2024-01-31T12:34:56Z'], ['2024-02-30'], ['Wed, 31 Jan 2024 12:34:56 GMT'], ['Tue, 31 Jan 2024 12:34:56 GMT'],
    ['31/01/2024', 'DD/MM/YYYY'], ['31/13/2024', 'DD/MM/YYYY'], ['2024-01-31 extra', 'YYYY-MM-DD', true], ['5 PM', 'h A'], ['13 PM', 'h A'],
    ['not a date', 'YYYY-MM-DD'], [[2024, 0, 31]], [[2024, 12, 1]], [[2024, 0, 32]], [[2024, 1, 29, 25]], [[2024, 0, 1, 0, 60]],
    [{year: 2024, month: 0, day: 5}], [{year: 2024, month: 5, date: 5, hour: 10, minute: 30}], [{y: 2024, M: 1, d: 29}], [{year: 2023, month: 1, day: 29}],
    [null], [''], [1706704496789], [1e16], [NaN], ['2024-W05-3'], ['2024-W60'], ['2024-366'], ['2023-366'], ['/Date(1706704496789)/'], ['2024-03-10 02:30'],
    ['Jan 5, 2024 10:30 PM'], [{}], [[]]
];
CREATE.forEach(function (c) {
    ['local', 'utc', 'tz'].forEach(function (mode) {
        var input = c[0], fmt = typeof c[1] === 'string' ? c[1] : undefined, strict = c[1] === true || c[2] === true;
        var m;
        if (mode === 'utc') m = moment.utc(input, fmt, strict);
        else if (mode === 'tz') m = fmt === undefined ? moment.tz(input, 'America/New_York') : moment.tz(input, fmt, strict, 'America/New_York');
        else m = moment(input, fmt, strict);
        if (fmt === undefined && c.length < 2 && input !== undefined) { /* plain */ }
        var pf = JSON.parse(JSON.stringify(m.parsingFlags())); // copy: isDSTShifted() below re-parses and appends to the shared arrays
        var dstShifted = m.isDSTShifted();
        var rec = {kind: 'create', input: input === null ? null : (typeof input === 'number' && isNaN(input) ? 'NaN' : input), isNull: input === null, isNaN: typeof input === 'number' && isNaN(input),
            format: fmt === undefined ? null : fmt, strict: strict, mode: mode, valid: m.isValid(), invalidAt: m.invalidAt(),
            flags: {iso: pf.iso, rfc2822: pf.rfc2822, empty: pf.empty, charsLeftOver: pf.charsLeftOver, unusedTokens: pf.unusedTokens, overflow: pf.overflow,
                nullInput: pf.nullInput, userInvalidated: pf.userInvalidated, weekdayMismatch: pf.weekdayMismatch, invalidMonth: pf.invalidMonth, invalidFormat: pf.invalidFormat},
            cd: {format: m.creationData().format === undefined ? null : m.creationData().format, strict: !!m.creationData().strict, isUTC: m.creationData().isUTC},
            dstShifted: dstShifted};
        if (rec.valid) { rec.ms = m.valueOf(); rec.fmt = m.format(); }
        add(rec);
    });
});

// ---- parseZone
['2024-01-31T12:34:56+08:00', '2024-01-31T12:34:56-0530', '2024-01-31T12:34:56Z', '2024-01-31T12:34:56', '2024-01-31', 'Wed, 31 Jan 2024 12:34:56 +0530',
    'Jan 5 2024 10:30 +0800', '2024-01-31 12:00 GMT+0200', '2024-01-31T12:34:56+08', 'xx +09:00 yy -03:30'].forEach(function (s) {
    var m = moment.parseZone(s);
    add({kind: 'parseZone', input: s, valid: m.isValid(), offset: m.isValid() ? m.utcOffset() : null, fmt: m.isValid() ? m.format('YYYY-MM-DD HH:mm Z') : null});
});

// ---- static lists
['en', 'zh-tw', 'fr', 'ru', 'ar'].forEach(function (loc) {
    moment.locale(loc);
    [['months', []], ['months', [3]], ['months', ['MMMM D']], ['months', ['MMMM D', 3]], ['monthsShort', []], ['monthsShort', ['D MMM']], ['weekdays', []], ['weekdays', [2]],
        ['weekdays', [true]], ['weekdays', [true, 1]], ['weekdays', ['dddd D']], ['weekdaysShort', []], ['weekdaysShort', [true]], ['weekdaysMin', []], ['weekdaysMin', [true, 3]]].forEach(function (c) {
        add({kind: 'static', locale: loc, fn: c[0], args: c[1], expected: moment[c[0]].apply(moment, c[1])});
    });
    add({kind: 'static', locale: loc, fn: 'localeAbbr', args: [], expected: moment.locale()});
});
moment.locale('en');
['y', 'years', 'M', 'w', 'W', 'isoWeeks', 'D', 'd', 'e', 'E', 'DDD', 'h', 'ms', 'Q', 'gg', 'GG', 'xx', 'MINUTES'].forEach(function (u) {
    add({kind: 'static', locale: 'en', fn: 'normalizeUnits', args: [u], expected: moment.normalizeUnits(u) === undefined ? null : moment.normalizeUnits(u)});
});

// ---- add with the deprecated inverted arguments, durations, strings
[['days', 1], [5, 'days'], ['PT36H'], [{days: 1, months: 1}], ['1:30:00'], [1.5, 'days'], ['x', 'y']].forEach(function (a) {
    var m = moment.utc('2024-01-31T12:00:00Z');
    try { m.add.apply(m, a); } catch (e) {}
    add({kind: 'add', args: a, expected: m.toISOString()});
});

// ---- Duration spellings
[[90, 'minutes'], [{days: 1, hours: 12}], ['P1Y2M']].forEach(function (a) {
    var d = moment.duration.apply(moment, a);
    add({kind: 'duration', args: a, as: d.as('hours'), asM: d.as('M'), iso: d.toIsoString(), cloneIso: d.clone().add(1, 'h').toISOString(), lang: d.lang('fr').humanize()});
});

// ---- moment-timezone data API
add({kind: 'tz', fn: 'dataVersion', expected: moment.tz.dataVersion});
add({kind: 'tz', fn: 'names', expected: moment.tz.names()});
add({kind: 'tz', fn: 'countries', expected: moment.tz.countries()});
['TW', 'US', 'cn', 'XX', 'jp'].forEach(function (c) { add({kind: 'tz', fn: 'zonesForCountry', args: [c], expected: moment.tz.zonesForCountry(c)}); });
['Asia/Taipei', 'Asia/Calcutta', 'asia/taipei', 'America/Argentina/Buenos_Aires', 'Nope/Zone', 'UTC', 'Etc/GMT+5', 'US/Eastern'].forEach(function (n) {
    var z = moment.tz.zone(n);
    add({kind: 'tz', fn: 'zone', args: [n], expected: z ? {name: z.name, abbrs: z.abbrs, offsets: z.offsets, untils: z.untils.map(function (u) { return u === Infinity ? 'Infinity' : u; }), population: z.population} : null});
    add({kind: 'tz', fn: 'zoneExists', args: [n], expected: !!z});
});
['Asia/Taipei|CST CDT JST|-80 -90 -90|01010101010202|-1ay00 15HC0 ...'].forEach(function () {});
add({kind: 'tz', fn: 'unpackBase60', args: ['1a.8'], expected: moment.tz.unpackBase60('1a.8')});
add({kind: 'tz', fn: 'unpackBase60', args: ['-2ldXH.Q'], expected: moment.tz.unpackBase60('-2ldXH.Q')});
// custom zone data via add/link
var custom = 'Test/Custom|AAA BBB|-60 -120|0101|-1z4C0 8Q40 8Q40|10';
moment.tz.add(custom); moment.tz.link('Test/Custom|Test/Alias');
var cz = moment.tz.zone('Test/Alias');
add({kind: 'tz', fn: 'custom', args: [custom, 'Test/Custom|Test/Alias'], expected: {name: cz.name, offsets: cz.offsets, abbrs: cz.abbrs, untils: cz.untils.map(function (u) { return u === Infinity ? 'Infinity' : u; }),
    at: moment.tz(Date.UTC(2020, 5, 1), 'Test/Alias').format('Z z'), names: moment.tz.names().indexOf('Test/Alias') >= 0}});

// ---- moment.tz.load and Zone.countries
['Asia/Taipei', 'America/New_York', 'Europe/London', 'UTC', 'Asia/Calcutta', 'America/Argentina/Buenos_Aires'].forEach(function (n) {
    add({kind: 'tz', fn: 'zoneCountries', args: [n], expected: moment.tz.zone(n).countries().sort()});
});
var loadData = {version: '9999z', zones: ['Test/Load|LL MM|-30 -60|0101|-1z4C0 8Q40 8Q40|5'], links: ['Test/Load|Test/LoadAlias'], countries: ['XX|Test/Load Asia/Taipei', 'YY|Test/Load']};
moment.tz.load(loadData);
add({kind: 'tz', fn: 'load', args: [loadData], expected: {version: moment.tz.dataVersion, countries: moment.tz.countries().indexOf('XX') >= 0 && moment.tz.countries().indexOf('YY') >= 0,
    xx: moment.tz.zonesForCountry('xx'), yy: moment.tz.zonesForCountry('YY'), loadZone: moment.tz.zone('Test/LoadAlias').name,
    loadCountries: moment.tz.zone('Test/Load').countries().sort(), taipei: moment.tz.zone('Asia/Taipei').countries().sort(),
    at: moment.tz(Date.UTC(2021, 6, 1), 'Test/LoadAlias').format('Z z'), names: moment.tz.names().indexOf('Test/LoadAlias') >= 0}});

// ---- moment.invalid and misc statics
add({kind: 'invalid', flags: null, expected: {valid: false, userInvalidated: moment.invalid().parsingFlags().userInvalidated, invalidAt: moment.invalid().invalidAt(), fmt: moment.invalid().format()}});
add({kind: 'invalid', flags: {invalidMonth: 'foo'}, expected: {valid: moment.invalid({invalidMonth: 'foo'}).isValid(), invalidMonth: moment.invalid({invalidMonth: 'foo'}).parsingFlags().invalidMonth}});
add({kind: 'html5', expected: moment.HTML5_FMT});
add({kind: 'version', expected: moment.version});


// ---- moment.tz.setDefault / moveInvalidForward / moveAmbiguousForward: scenarios replayed step by step
function probe(m) { return m.isValid() ? [m.valueOf(), m.format('YYYY-MM-DD HH:mm Z z')] : null; }
var INPUTS = ['2024-06-15 10:00', '2024-03-10 02:30', '2024-11-03 01:30', 1718445600000, '2024-06-15T10:00:00Z', [2024, 5, 15, 10], '2024-03-10T02:30:00', 'Jan 5, 2024 10:30'];
var scenarios = [
    {name: 'default-NY', steps: [{op: 'setDefault', zone: 'America/New_York'}, {op: 'probe'}, {op: 'setDefault', zone: null}, {op: 'probe'}]},
    {name: 'default-Taipei', steps: [{op: 'setDefault', zone: 'Asia/Taipei'}, {op: 'probe'}, {op: 'ops'}, {op: 'setDefault', zone: null}]},
    {name: 'flags', steps: [{op: 'flags', moveInvalidForward: false, moveAmbiguousForward: false}, {op: 'probeTz', zone: 'America/New_York'},
        {op: 'flags', moveInvalidForward: true, moveAmbiguousForward: true}, {op: 'probeTz', zone: 'America/New_York'},
        {op: 'flags', moveInvalidForward: false, moveAmbiguousForward: true}, {op: 'probeTz', zone: 'Europe/London'},
        {op: 'flags', moveInvalidForward: true, moveAmbiguousForward: false}, {op: 'probeTz', zone: 'Europe/London'}]}
];
scenarios.forEach(function (sc) {
    var out = [];
    sc.steps.forEach(function (st) {
        if (st.op === 'setDefault') moment.tz.setDefault(st.zone);
        else if (st.op === 'flags') { moment.tz.moveInvalidForward = st.moveInvalidForward; moment.tz.moveAmbiguousForward = st.moveAmbiguousForward; }
        else if (st.op === 'probe') out.push(INPUTS.map(function (i) { return [probe(moment(i)), probe(moment.utc(i)), probe(moment.tz(i, 'Asia/Tokyo'))]; }).concat([[probe(moment()), probe(moment.utc()), null]]));
        else if (st.op === 'probeTz') out.push(INPUTS.slice(0, 3).concat(['2024-03-31 01:30', '2024-10-27 01:30']).map(function (i) { return probe(moment.tz(i, st.zone)); }));
        else if (st.op === 'ops') {
            var m = moment('2024-03-09 12:00');
            out.push([probe(m.clone().add(1, 'day')), probe(m.clone().startOf('day')), probe(m.clone().add(1, 'day').startOf('day')), probe(moment(1710054000000).startOf('hour')), probe(m.clone().endOf('month'))]);
        }
        else out.push(null);
    });
    moment.tz.setDefault(null);
    moment.tz.moveInvalidForward = true; moment.tz.moveAmbiguousForward = false;
    add({kind: 'tzsettings', name: sc.name, steps: sc.steps, inputs: INPUTS, expected: out});
});

fs.writeFileSync(path.join(__dirname, '..', 'tests', 'fixtures', 'api.json'), JSON.stringify(cases));
console.log('api', cases.length);
