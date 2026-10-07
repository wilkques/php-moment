// moment.tz.guess() as the offset-scoring search answers it, for a host in each zone (Intl hidden,
// "now" fixed to 2024-06-15). One node process per zone: TZ can't change mid-process reliably.
var fs = require('fs');
var path = require('path');
var cp = require('child_process');

if (process.argv[2] === '--child') {
    var FIXED = Date.UTC(2024, 5, 15, 10, 0, 0), RealDate = Date;
    global.Date = function () {
        var a = Array.prototype.slice.call(arguments);
        if (!(this instanceof global.Date)) return new RealDate(FIXED).toString();
        return a.length === 0 ? new RealDate(FIXED) : new (Function.prototype.bind.apply(RealDate, [null].concat(a)))();
    };
    global.Date.prototype = RealDate.prototype;
    global.Date.UTC = RealDate.UTC; global.Date.parse = RealDate.parse; global.Date.now = function () { return FIXED; };
    global.Intl = undefined;
    var moment = require('moment-timezone');
    process.stdout.write(JSON.stringify(moment.tz.guess(true) || null));
} else {
    var moment = require('moment-timezone');
    var out = [];
    // guess.json.gz: a host whose ICU names aren't ASCII, so no abbreviation is found and only offsets decide;
    // guess-icu.json.gz: English names, so abbreviations count (needs ext-intl on the PHP side)
    var ICU = process.argv[2] === 'icu';
    var LOCALE = ICU ? 'en_US.UTF-8' : 'zh_TW.UTF-8';
    moment.tz.names().forEach(function (name) {
        var res = cp.spawnSync(process.execPath, [__filename, '--child'], {env: Object.assign({}, process.env, {TZ: name, LC_ALL: LOCALE, LANG: LOCALE}), maxBuffer: 1 << 24});
        out.push({tz: name, guess: JSON.parse(res.stdout.toString() || 'null')});
    });
    fs.writeFileSync(path.join(__dirname, '..', 'tests', 'fixtures', ICU ? 'guess-icu.json.gz' : 'guess.json.gz'), require('zlib').gzipSync(JSON.stringify(out), {level: 9}));
    console.log('guess', out.length);
}
