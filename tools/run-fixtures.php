<?php
// Dependency-free runner: php tools/run-fixtures.php [suite ...]  (replays tests/fixtures against src/)
spl_autoload_register(function ($c) {
    $prefix = 'Wilkques\\Moment\\';
    if (strpos($c, $prefix) !== 0) return;
    $rel = str_replace('\\', '/', substr($c, strlen($prefix)));
    foreach (array(__DIR__ . '/../src/', __DIR__ . '/../tests/') as $dir) {
        foreach (array($dir . $rel . '.php', $dir . preg_replace('#^Tests/#', '', $rel) . '.php') as $f) {
            if (is_file($f)) { require $f; return; }
        }
    }
});
use Wilkques\Moment\Tests\Support\Fixtures;
$suites = array_slice($argv, 1) ?: array('format', 'add', 'start-end', 'diff', 'parse', 'duration', 'relative', 'fallback', 'api', 'tzcheck', 'guess', 'guess-icu', 'guess-table', 'cross', 'fuzz', 'extra', 'gaps');
function firstDiffs($a, $b, $path = '', &$out = array(), $limit = 4)
{
    if (count($out) >= $limit) return $out;
    if (is_array($a) && is_array($b)) {
        foreach ($a as $k => $v) {
            if (!array_key_exists($k, $b)) { $out[] = "$path/$k: only in got"; continue; }
            firstDiffs($v, $b[$k], "$path/$k", $out, $limit);
        }
        foreach ($b as $k => $v) if (!array_key_exists($k, $a)) $out[] = "$path/$k: only in want";
    } elseif ($a !== $b && !(is_numeric($a) && is_numeric($b) && abs($a - $b) < 1e-9 * max(1, abs($b)))) {
        $out[] = "$path: got " . json_encode($a) . ' want ' . json_encode($b);
    }
    return $out;
}
$bad = 0;
$diffs = getenv('DIFFS');
$group = getenv('GROUP');
foreach ($suites as $s) {
    $f = Fixtures::run($s);
    echo str_pad($s, 12), count($f), " mismatches\n";
    if ($diffs) {
        foreach (array_slice($f, 0, (int) $diffs) as $line) {
            if (!preg_match('/^(.*)\n  got:  (.*)\n  want: (.*)$/s', $line, $m)) { echo "  $line\n"; continue; }
            $case = json_decode($m[1], true);
            $label = isset($case['kind']) ? $case['kind'] : '';
            foreach (array('locale', 'zone', 'base', 'iso', 'obj', 'mode') as $k) if (isset($case[$k])) $label .= " $k=" . json_encode($case[$k]);
            echo "  [$label]\n";
            foreach (firstDiffs(json_decode($m[2], true), json_decode($m[3], true)) as $d) echo "     $d\n";
        }
        continue;
    }
    if ($group) {
        $seen = array();
        foreach ($f as $line) {
            preg_match('/"kind":"(\w+)"/', $line, $k);
            preg_match('/"(?:method|fn)":"(\w+)"/', $line, $m);
            $key = (isset($k[1]) ? $k[1] : '') . '/' . (isset($m[1]) ? $m[1] : '');
            if (!isset($seen[$key])) { $seen[$key] = 0; $first[$key] = $line; }
            $seen[$key]++;
        }
        foreach ($seen as $key => $n) echo "  [$key] $n\n    " . str_replace("\n", "\n    ", substr($first[$key], 0, 2500)) . "\n";
        continue;
    }
    foreach (array_slice($f, 0, 3) as $line) echo "  $line\n";
    $bad += count($f);
}
exit($bad ? 1 : 0);
