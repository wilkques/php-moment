<?php
/**
 * Replays tests/fixtures/locales/<name>.json.gz (from the real moment.js) against the PHP locale.
 *
 *   php tools/locale-check.php ru de          check these locales
 *   php tools/locale-check.php --all          check every locale that has a PHP class
 *   php tools/locale-check.php --list         which locales are ported / missing
 *   options: --show=N  examples to print per category (default 3)
 * (The fixtures are gzipped: PHP needs the zlib extension.)
 */
spl_autoload_register(function ($c) {
    $prefix = 'Wilkques\\Moment\\';
    if (strpos($c, $prefix) !== 0) {
        return;
    }
    $rel = str_replace('\\', '/', substr($c, strlen($prefix)));
    foreach (array(__DIR__ . '/../src/' . $rel . '.php', __DIR__ . '/../tests/' . preg_replace('#^Tests/#', '', $rel) . '.php') as $f) {
        if (is_file($f)) {
            require $f;

            return;
        }
    }
});

use Wilkques\Moment\Tests\Support\LocaleFixtures;

$show = 3;
$names = array();
$all = $list = false;
foreach (array_slice($argv, 1) as $a) {
    if ($a === '--all') {
        $all = true;
    } elseif ($a === '--list') {
        $list = true;
    } elseif (strpos($a, '--show=') === 0) {
        $show = (int) substr($a, 7);
    } else {
        $names[] = $a;
    }
}

if ($list || $all) {
    $ported = $missing = array();
    foreach (LocaleFixtures::names() as $n) {
        $class = 'Wilkques\\Moment\\Locale\\Locales\\' . str_replace(' ', '', ucwords(str_replace('-', ' ', $n)));
        if (class_exists($class)) {
            $ported[] = $n;
        } else {
            $missing[] = $n;
        }
    }
    if ($list) {
        echo 'ported (' . count($ported) . '): ' . implode(' ', $ported) . "\n";
        echo 'missing (' . count($missing) . '): ' . implode(' ', $missing) . "\n";
        exit(0);
    }
    $names = $ported;
}

$bad = 0;
foreach ($names as $n) {
    $r = LocaleFixtures::check($n, $show);
    $count = 0;
    foreach ($r['failures'] as $f) {
        $count += $f[0];
    }
    echo str_pad($n, 10) . ($count ? "FAIL $count / {$r['total']}" : "ok   {$r['total']} checks") . "\n";
    foreach ($r['failures'] as $cat => $f) {
        echo "  [$cat] {$f[0]} mismatches\n";
        foreach ($f[1] as $info) {
            echo "     $info\n";
        }
    }
    $bad += $count;
}
exit($bad ? 1 : 0);
