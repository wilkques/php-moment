<?php

namespace Wilkques\Moment\Tests\Units\Php\Higher;

use PHPUnit\Framework\TestCase;
use Wilkques\Moment\Tests\Support\Fixtures;
use Wilkques\Moment\Tests\Support\LocaleFixtures;

/**
 * Every case was produced by the real moment.js (see tools/generate-fixtures.js).
 */
class FixturesTest extends TestCase
{
    protected function check($suite)
    {
        $failures = Fixtures::run($suite);

        $this->assertEquals(array(), array_slice($failures, 0, 5), count($failures) . " mismatches in '$suite'");
    }

    public function testFormat()
    {
        $this->check('format');
    }

    public function testAdd()
    {
        $this->check('add');
    }

    public function testStartAndEndOf()
    {
        $this->check('start-end');
    }

    public function testDiff()
    {
        $this->check('diff');
    }

    public function testParse()
    {
        $this->check('parse');
    }

    public function testDuration()
    {
        $this->check('duration');
    }

    public function testRelativeTime()
    {
        $this->check('relative');
    }

    public function testFallbackToJsDateParsing()
    {
        $this->check('fallback');
    }

    public function testBundledTimezoneData()
    {
        if (!in_array('compress.zlib', stream_get_wrappers())) {
            $this->markTestSkipped('the fixtures are gzipped: needs the zlib extension');
        }
        $this->check('tzcheck');
    }

    public function testGuessByOffsetScoring()
    {
        if (!in_array('compress.zlib', stream_get_wrappers())) {
            $this->markTestSkipped('the fixtures are gzipped: needs the zlib extension');
        }
        $this->check('guess');
    }

    public function testGuessWithIcuNames()
    {
        if (!in_array('compress.zlib', stream_get_wrappers())) {
            $this->markTestSkipped('the fixtures are gzipped: needs the zlib extension');
        }
        if (!class_exists('IntlTimeZone')) {
            $this->markTestSkipped('scoring the host abbreviation needs ext-intl');
        }
        $this->check('guess-icu');
    }

    public function testCrossProducts()
    {
        if (!in_array('compress.zlib', stream_get_wrappers())) {
            $this->markTestSkipped('the fixtures are gzipped: needs the zlib extension');
        }
        $this->check('cross');
    }

    public function testRandomDifferential()
    {
        if (!in_array('compress.zlib', stream_get_wrappers())) {
            $this->markTestSkipped('the fixtures are gzipped: needs the zlib extension');
        }
        $this->check('fuzz');
    }

    public function testDurationCalendarAndRuntimeLocales()
    {
        if (!in_array('compress.zlib', stream_get_wrappers())) {
            $this->markTestSkipped('the fixtures are gzipped: needs the zlib extension');
        }
        $this->check('extra');
    }

    public function testBehaviourFoundByCoverage()
    {
        if (!in_array('compress.zlib', stream_get_wrappers())) {
            $this->markTestSkipped('the fixtures are gzipped: needs the zlib extension');
        }
        $this->check('gaps');
    }

    public function testApi()
    {
        $this->check('api');
    }

    /** All locales; set MOMENT_LOCALES=ru,de to run only some */
    public function testLocales()
    {
        if (!in_array('compress.zlib', stream_get_wrappers())) {
            $this->markTestSkipped('the locale fixtures are gzipped: needs the zlib extension');
        }
        $only = getenv('MOMENT_LOCALES');
        $failures = array();
        foreach (LocaleFixtures::names() as $name) {
            if ($only && !in_array($name, explode(',', $only))) {
                continue;
            }
            $r = LocaleFixtures::check($name, 2);
            foreach ($r['failures'] as $cat => $f) {
                $failures[] = "$name [$cat] {$f[0]} mismatches, e.g. " . $f[1][0];
            }
        }

        $this->assertEquals(array(), array_slice($failures, 0, 10), count($failures) . ' locale problems');
    }
}
