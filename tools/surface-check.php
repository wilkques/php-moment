<?php
spl_autoload_register(function ($c) { $f = __DIR__ . '/../src/' . str_replace('\\', '/', substr($c, 16)) . '.php'; if (is_file($f)) require $f; });
$s = json_decode(file_get_contents(__DIR__ . '/surface.json'), true);
$src = file_get_contents(__DIR__ . '/../src/Moment.php');
preg_match("/function __call\(.*?\n    }\n\n    public static function __callStatic/s", $src, $a);
preg_match("/function __callStatic\(.*?\n    protected static function listOne/s", $src, $b);
preg_match_all("/case '(\w+)':/", $a[0], $ia); preg_match_all("/case '(\w+)':/", $b[0], $sa);
$dsrc = file_get_contents(__DIR__ . '/../src/Duration.php');
preg_match("/function __call\(.*/s", $dsrc, $d); preg_match_all("/case '(\w+)':/", $d[0], $da);
$M = 'Wilkques\Moment\Moment'; $D = 'Wilkques\Moment\Duration'; $L = 'Wilkques\Moment\Locale\Locale'; $T = 'Wilkques\Moment\Timezone\Tz'; $Z = 'Wilkques\Moment\Timezone\Zone';
$has = function ($class, $name, $magic = array()) {
    return method_exists($class, $name) || in_array($name, $magic) || property_exists($class, $name) || defined("$class::" . strtoupper($name));
};
$alias = array('clone' => 'copy', 'as' => 'toUnit');
$missing = array();
foreach ($s['fn'] as $n) if (!$has($M, $n, $ia[1]) && !$has($M, isset($alias[$n]) ? $alias[$n] : $n)) $missing['moment.fn'][] = $n;
foreach ($s['static'] as $n) if (!$has($M, $n, $sa[1]) && !in_array($n, array('fn'))) $missing['moment.*'][] = $n;
foreach ($s['dur'] as $n) if (!$has($D, $n, $da[1]) && !$has($D, isset($alias[$n]) ? $alias[$n] : $n)) $missing['duration'][] = $n;
foreach ($s['locale'] as $n) if (!$has($L, $n)) $missing['locale'][] = $n;
foreach ($s['tz'] as $n) if (!$has($T, $n)) $missing['moment.tz'][] = $n;
foreach ($s['zone'] as $n) if (!$has($Z, $n)) $missing['Zone'][] = $n;
foreach ($missing as $k => $v) echo "$k: " . implode(' ', $v) . "\n";
echo $missing ? '' : "nothing missing\n";
