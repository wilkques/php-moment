<?php

require __DIR__ . '/../vendor/autoload.php';

// PHPUnit < 6 (e.g. 4.8 on PHP 5.3) only exposes the global
// \PHPUnit_Framework_TestCase; PHPUnit >= 6 exposes \PHPUnit\Framework\TestCase.
// Alias the old name so every test can write `use PHPUnit\Framework\TestCase`.
if (!class_exists('PHPUnit\\Framework\\TestCase') && class_exists('PHPUnit_Framework_TestCase')) {
    class_alias('PHPUnit_Framework_TestCase', 'PHPUnit\\Framework\\TestCase');
}
