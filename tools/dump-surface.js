// Lists every public member of moment / moment-timezone into tools/surface.json; then
//   php tools/surface-check.php   reports any that the PHP port lacks.
var m = require('moment-timezone');
function keys(o) { var s = {}; for (var k in o) { if (k[0] !== '_') s[k] = 1; } return Object.keys(s).sort(); }
var localeProto = Object.getPrototypeOf(m.localeData());
require('fs').writeFileSync(__dirname + '/surface.json', JSON.stringify({fn: keys(m.fn), static: keys(m), dur: keys(m.duration.fn),
    locale: keys(localeProto), tz: keys(m.tz), zone: keys(m.tz.Zone.prototype)}));
