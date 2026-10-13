<?php

namespace Wilkques\Moment\Tests\Units;

use PHPUnit\Framework\TestCase;
use Wilkques\Moment\Calendar;
use Wilkques\Moment\Duration;
use Wilkques\Moment\Locale\Composite;
use Wilkques\Moment\Locale\Locale;
use Wilkques\Moment\Locale\Registry;
use Wilkques\Moment\Moment;
use Wilkques\Moment\Timezone\Tz;

/** Probe for the lower-casing table used when mbstring is missing. */
class CoverageLowerProbe extends Locale
{
    public static function fallback($s)
    {
        return preg_replace_callback('/[^\x00-\x7F]/u', array('Wilkques\Moment\Tests\Units\CoverageLowerProbe', 'lowerChar'), strtolower($s));
    }
}

/**
 * PHP-specific paths that have no JavaScript counterpart to compare with.
 */
class CoverageTest extends TestCase
{
    protected function frozen()
    {
        date_default_timezone_set('UTC');
        Moment::setNow(1718445600000); // 2024-06-15T10:00:00Z
        Moment::$suppressDeprecationWarnings = true;
        Registry::globalLocale('en');
    }

    public function testDateTimeInputKeepsMilliseconds()
    {
        $d = \DateTime::createFromFormat('Y-m-d H:i:s.u', '2024-01-31 12:34:56.789000', new \DateTimeZone('UTC'));
        $m = Moment::createUtc($d);

        $this->assertEquals(1706704496789, $m->valueOf());
        $back = $m->toDate();
        $this->assertSame('2024-01-31 12:34:56.789000', $back->format('Y-m-d H:i:s.u'));
        $this->assertTrue(Moment::isMoment($m));
        $this->assertFalse(Moment::isMoment($d));
        $gone = Moment::invalid()->toDate();
        $this->assertInstanceOf('Wilkques\\Moment\\InvalidDate', $gone);
        $this->assertInstanceOf('DateTime', $gone);
        $this->assertFalse($gone->isValid());
        $this->assertFalse($gone->getTimestamp());
        $this->assertSame('Invalid Date', $gone->format('Y-m-d'));
        $this->assertSame('Invalid Date', (string) $gone);
        $this->assertFalse(Moment::create($gone)->isValid());
    }

    public function testArrayInputWithMissingLeadingPartsUsesToday()
    {
        $this->frozen();

        $this->assertSame('2024-06-05T00:00:00Z', Moment::createUtc(array(null, null, 5))->format());
        $this->assertSame('2024-06-15T07:08:00Z', Moment::createUtc(array(null, null, null, 7, 8))->format());
    }

    public function testLowerCasingTableMatchesMbstring()
    {
        if (!function_exists('mb_strtolower')) {
            $this->markTestSkipped('needs mbstring to compare with');
        }
        $ranges = array(array(0xC0, 0xD6), array(0xD8, 0xDE), array(0x100, 0x12F), array(0x131, 0x137), array(0x139, 0x148), array(0x14A, 0x17E),
            array(0x388, 0x38A), array(0x38C, 0x38C), array(0x38E, 0x38F), array(0x391, 0x3A1), array(0x3A3, 0x3AB), array(0x400, 0x42F), array(0x531, 0x556),
            array(0x180, 0x24F), array(0x370, 0x3FF), array(0x400, 0x52F), array(0x10A0, 0x10FF), array(0x1C90, 0x1CBF), array(0x1E00, 0x1EFF), array(0x1F00, 0x1FFF));
        $bad = array();
        foreach ($ranges as $r) {
            for ($cp = $r[0]; $cp <= $r[1]; $cp++) {
                $ch = html_entity_decode('&#' . $cp . ';', ENT_QUOTES, 'UTF-8');
                if (PHP_VERSION_ID < 70300 && mb_strtolower($ch, 'UTF-8') === $ch) {
                    continue; // that mbstring's Unicode tables predate the letter (Georgian Mtavruli, Latin Extended-D...)
                }
                if (CoverageLowerProbe::fallback($ch) !== mb_strtolower($ch, 'UTF-8')) {
                    $bad[] = dechex($cp);
                }
            }
        }

        $this->assertSame(array(), $bad);
        $this->assertSame('ə', CoverageLowerProbe::fallback('Ə'));
        $this->assertSame("i\xCC\x87", CoverageLowerProbe::fallback('İ'));
        $this->assertSame('plain ascii', CoverageLowerProbe::fallback('PLAIN ASCII'));
    }

    public function testCompositeAnswersEveryLocaleMethod()
    {
        $this->frozen();
        $composite = Registry::define('zz-cov', array('parentLocale' => 'ru', 'ordinal' => '%dº', 'week' => array('dow' => 3, 'doy' => 9),
            'eras' => array(array('since' => '0001-01-01', 'until' => INF, 'offset' => 1, 'name' => 'Era', 'narrow' => 'E', 'abbr' => 'E')),
            'dayOfMonthOrdinalParse' => '~\d{1,2}º~u', 'weekdaysShort' => array('a', 'b', 'c', 'd', 'e', 'f', 'g')));
        $this->assertTrue($composite instanceof Composite);
        $ru = Registry::find('ru');
        $mom = Moment::createUtc('2024-03-05')->locale('zz-cov');

        $this->assertSame('5º', $composite->ordinal(5, 'D'));
        $this->assertSame(3, $composite->firstDayOfWeek());
        $this->assertSame(9, $composite->firstDayOfYear());
        $this->assertEquals($mom->week(), $composite->week($mom));
        $this->assertSame(array('dow' => 3, 'doy' => 9), $composite->week());
        $this->assertSame($ru->months(), $composite->months());                   // untouched settings still come from the parent
        $this->assertSame($ru->monthsShort(), $composite->monthsShort());
        $this->assertSame($ru->weekdays(), $composite->weekdays());
        $this->assertSame(array('a', 'b', 'c', 'd', 'e', 'f', 'g'), $composite->weekdaysShort());
        $this->assertSame($ru->weekdaysMin(), $composite->weekdaysMin());
        $this->assertSame($ru->monthsRegex(), $composite->monthsRegex());
        $this->assertSame($ru->monthsShortRegex(true), $composite->monthsShortRegex(true));
        $this->assertSame($ru->monthsParse('март', 'MMMM', false), $composite->monthsParse('март', 'MMMM', false));
        $this->assertNotNull($composite->weekdaysRegex());
        $this->assertNotNull($composite->weekdaysShortRegex(true));
        $this->assertNotNull($composite->weekdaysMinRegex());
        $this->assertSame(1, $composite->weekdaysParse('b', 'ddd', false));
        $this->assertSame('~\d{1,2}º~u', $composite->dayOfMonthOrdinalParse(true));
        $this->assertSame($ru->longDateFormat('LL'), $composite->longDateFormat('LL'));
        $this->assertSame($ru->calendar('sameDay'), $composite->calendar('sameDay'));
        $this->assertSame($ru->invalidDate(), $composite->invalidDate());
        $this->assertSame($ru->preparse('x'), $composite->preparse('x'));
        $this->assertSame($ru->postformat('x'), $composite->postformat('x'));
        $this->assertSame($ru->meridiem(10, 0, true), $composite->meridiem(10, 0, true));
        $this->assertSame($ru->isPM('PM'), $composite->isPM('PM'));
        $this->assertSame($ru->hasMeridiemHour(), $composite->hasMeridiemHour());
        $this->assertSame($ru->meridiemHour(5, 'вечера'), $composite->meridiemHour(5, 'вечера'));
        $this->assertSame($ru->meridiemParse(), $composite->meridiemParse());
        $this->assertSame($ru->relativeTime(5, true, 'mm', false), $composite->relativeTime(5, true, 'mm', false));
        $this->assertSame($ru->pastFuture(1, 'x'), $composite->pastFuture(1, 'x'));
        $eras = $composite->eras();
        $this->assertSame('Era', $eras[0]['name']);
        $this->assertNotNull($composite->erasParse('era', 'N', false));
        $this->assertNotNull($composite->erasConvertYear($eras[0], 5));
        $this->assertNotNull($composite->erasNameRegex());
        $this->assertNotNull($composite->erasAbbrRegex());
        $this->assertNotNull($composite->erasNarrowRegex());
        $this->assertNull($composite->eraYearOrdinalRegex());
        $this->assertSame(7, $composite->eraYearOrdinalParse('7', null));
        $config = $composite->getConfig();
        $this->assertSame('%dº', $config['ordinal']);

        $composite->set(array('invalidDate' => 'nothing'));
        $this->assertSame('nothing', $composite->invalidDate());
        $this->assertSame('5º', $composite->ordinal(5, 'D'));                       // earlier settings stay
        Registry::define('zz-cov', null);
    }

    public function testLocaleSettingsHelpers()
    {
        $merged = Locale::mergeConfigs(array('a' => 1, 'b' => array('x' => 1, 'y' => 2), 'c' => array(1, 2)),
            array('a' => null, 'b' => array('y' => 3, 'z' => 4), 'c' => array(9)));
        $this->assertSame(array('b' => array('x' => 1, 'y' => 3, 'z' => 4), 'c' => array(9)), $merged);

        $locale = new Locale(array('abbr' => 'qq', 'parentLocale' => 'en'));
        $this->assertSame('qq', $locale->abbr);
        $this->assertSame('en', $locale->parentLocale);
    }

    public function testRegistryEdges()
    {
        $this->assertSame('xx-local', Registry::define('xx-local', new Locale())->abbr);
        $this->assertContains('xx-local', Registry::names());
        $this->assertSame('xx-local', Registry::get(Registry::find('xx-local'))->abbr);
        $this->assertSame('xx-local', Registry::get(Moment::createUtc('2024-01-01')->locale('xx-local'))->abbr);
        $this->assertSame('xx-local', Registry::globalLocale());
        Registry::define('xx-local', null);
        Registry::globalLocale('en');
        $this->assertNull(Registry::find('../escape'));
    }

    public function testZonesForCountryWithOffsets()
    {
        $this->frozen();

        $this->assertSame(array(array('name' => 'Asia/Taipei', 'offset' => -480)), $this->plain(Tz::zonesForCountry('tw', true)));
        $us = Tz::zonesForCountry('US', true);
        $this->assertSame('America/Adak', $us[0]['name']);
        $this->assertSame(Tz::zone('America/Adak')->utcOffset(Moment::now()), $us[0]['offset']);
        $this->assertEquals(-480, Tz::zone('Asia/Taipei')->offset(Moment::now()));  // deprecated spelling
        $unpacked = Tz::unpack('Test|AAA|0|0|0|0');
        $this->assertSame('Test', $unpacked['name']);
    }

    protected function plain($rows)
    {
        foreach ($rows as $i => $row) {
            $rows[$i]['offset'] = (int) $row['offset'];
        }

        return $rows;
    }

    public function testGuessUsesPhpZoneName()
    {
        $this->frozen();
        date_default_timezone_set('Asia/Taipei');
        $this->assertSame('Asia/Taipei', Tz::guess(true));
        date_default_timezone_set('UTC');
        // a three-letter name isn't trusted (moment-timezone requires more than 3 characters), so offsets decide
        $this->assertSame('Africa/Abidjan', Tz::guess(true));
    }

    public function testHostZoneUnknownToMomentTimezone()
    {
        $this->frozen();
        try {
            new \DateTimeZone('Factory');
        } catch (\Exception $e) {
            $this->markTestSkipped('this PHP has no "Factory" zone');
        }
        if (Tz::zone('Factory')) {
            $this->markTestSkipped('moment-timezone knows "Factory"');
        }
        date_default_timezone_set('Factory');

        $m = Moment::create('2024-01-31T12:34:56');
        $this->assertTrue($m->isValid());
        $this->assertEquals(1706704496000, $m->valueOf());
        $this->assertSame('2024-02-01T00:00:00+00:00', $m->copy()->add(1, 'day')->startOf('day')->format());
        $this->assertEquals(0, $m->utcOffset());
        date_default_timezone_set('UTC');
    }

    public function testDeprecationWarningIsRaisedOnceAsUserDeprecated()
    {
        $seen = array();
        set_error_handler(function ($no, $msg) use (&$seen) {
            $seen[] = $msg;

            return true;
        }, E_USER_DEPRECATED);
        Moment::$suppressDeprecationWarnings = false;
        Moment::deprecate('coverage-test', 'a message');
        Moment::deprecate('coverage-test', 'a message');
        restore_error_handler();
        Moment::$suppressDeprecationWarnings = true;

        $this->assertSame(array('Deprecation warning: a message'), $seen);
    }

    public function testNumberConversionLikeJavaScript()
    {
        $this->assertSame(1, Calendar::toNumber(true));
        $this->assertSame(0, Calendar::toNumber(false));
        $this->assertSame(0, Calendar::toNumber(null));
        $this->assertTrue(is_nan(Calendar::toNumber(array())));
        $this->assertSame(15.0, (float) Calendar::toNumber('0o17'));
        $this->assertSame(5.0, (float) Calendar::toNumber('0b101'));
        $this->assertTrue(is_infinite(Calendar::toNumber('-Infinity')));
        $this->assertSame('1700000000000', Calendar::str(1.7e12));
        $this->assertEquals(1700000000000, Calendar::int(1.7e12));
    }

    public function testMacro()
    {
        Moment::macro('plusDaysCoverage', function ($m, $n) {
            return $m->add($n, 'day');
        });
        $this->assertSame('2024-01-04', Moment::createUtc('2024-01-01')->plusDaysCoverage(3)->format('YYYY-MM-DD'));
        try {
            Moment::createUtc('2024-01-01')->noSuchMethodAnywhere();
            $this->fail('expected an exception');
        } catch (\BadMethodCallException $e) {
            $this->assertTrue(true);
        }
    }

    /** the paths used without ext-mbstring and with PHP 5.3's PCRE give the same parses as the normal ones */
    public function testParsingWithoutMbstringAndWithOldPcre()
    {
        if (!function_exists('mb_strtoupper')) {
            $this->markTestSkipped('needs mbstring to build the upper-case names');
        }
        $results = array();
        foreach (array(array(false, false), array(true, false), array(false, true), array(true, true)) as $i => $mode) {
            Locale::$withoutMbstring = $mode[0];
            Locale::$oldPcre = $mode[1];
            $out = array();
            try {
                foreach (array('ru', 'tr', 'de', 'el', 'hy-am', 'ka', 'uk', 'pl', 'vi', 'fr', 'az', 'lt') as $name) {
                    $loc = Registry::get($name);
                    $mom = Moment::utc(Calendar::utcMs(2024, 5, 15, 10, 0, 0));
                    foreach (array('MMMM' => $loc->months(), 'MMM' => $loc->monthsShort(), 'dddd' => $loc->weekdays(), 'ddd' => $loc->weekdaysShort(), 'dd' => $loc->weekdaysMin()) as $fmt => $names) {
                        foreach ($names as $text) {
                            foreach (array($text, mb_strtoupper($text, 'UTF-8'), mb_strtolower($text, 'UTF-8'), $text . ' x') as $variant) {
                                $m = Moment::create($variant, $fmt, $name);
                                $out[] = $name . '|' . $fmt . '|' . $variant . '|' . ($m->isValid() ? $m->format('YYYY-MM-DD') : 'invalid');
                            }
                        }
                    }
                }
            } catch (\Exception $e) {
                Locale::$withoutMbstring = false;
                Locale::$oldPcre = false;
                throw $e;
            }
            $results[$i] = $out;
        }
        Locale::$withoutMbstring = false;
        Locale::$oldPcre = false;

        $this->assertGreaterThan(1000, count($results[0]));
        $this->assertSame($results[0], $results[1], 'without mbstring');
        $this->assertSame($results[0], $results[2], 'old PCRE');
        $this->assertSame($results[0], $results[3], 'both');
    }

    /** edges the coverage report showed untested; every expectation comes from the real moment.js */
    public function testApiEdgesFoundByCoverage()
    {
        Moment::$suppressDeprecationWarnings = true;
        Moment::setNow(Calendar::utcMs(2024, 5, 15, 10, 0, 0));
        $m = Moment::utc('2024-01-31T12:34:56Z');

        // a locale given along with a moment
        $this->assertSame('fr', Moment::create($m, null, 'fr')->locale());
        $this->assertSame('fr', Moment::createUtc($m, null, 'fr')->locale());
        // array parts that aren't numbers, or overflow
        $this->assertFalse(Moment::create(array(NAN))->isValid());
        $this->assertFalse(Moment::create(array(INF))->isValid());
        $overflow = Moment::create(array(2024, 0, 1, 0, 0, 0, 1000));
        $flags = $overflow->parsingFlags();
        $this->assertFalse($overflow->isValid());
        $this->assertSame(6, $flags['overflow']);
        // getters, unknown units, odd arguments
        $this->assertSame(29, Moment::utc('2024-02-10')->daysInMonth());
        $now = Moment::now();
        $this->assertSame($now, Moment::utc($now)->set('bogus', 1)->valueOf());
        $this->assertSame($now, Moment::utc($now)->startOf('bogus')->valueOf());
        $this->assertSame($now, Moment::utc($now)->endOf('bogus')->valueOf());
        $this->assertTrue(is_nan(Moment::utc($now)->diff(Moment::invalid())));
        $utc = Moment::utc($now);
        $this->assertSame($utc, $utc->utcOffset('garbage'));
        $this->assertSame(0, $utc->utcOffset());
        $this->assertSame(0, Moment::offsetFromString('x', '/x/'));
        // output
        $this->assertSame('2024-01-31T12:34:56.000Z', $m->toJSON());
        $this->assertSame('2024-01-31T12:34:56.000Z', $m->jsonSerialize());
        $this->assertSame('Wed Jan 31 2024 12:34:56 GMT+0000', $m->toString());
        $this->assertSame('Wed Jan 31 2024 12:34:56 GMT+0000', (string) $m);
        // ISO week strings
        $this->assertFalse(Moment::create('2024-W01T10:30')->isValid());
        $this->assertTrue(Moment::create('2024-W01-1T10:30')->isValid());
        $this->assertTrue(Moment::create('2024-W01-1')->isValid());
        $this->assertFalse(Moment::create('2024', array())->isValid());
        $this->assertNull(\Wilkques\Moment\Parser::parse(Moment::invalid(), '', null));
        // a moment as a locale lookup
        $this->assertSame('fr', Registry::find(Moment::create($m, null, 'fr'))->abbr);

        // Duration
        $this->assertEquals(0, Moment::duration(null)->asMilliseconds());
        $this->assertTrue(Moment::duration(null)->isValid());
        $fromInvalid = Moment::duration(array('from' => Moment::invalid(), 'to' => Moment::utc($now)));
        $this->assertTrue($fromInvalid->isValid());
        $this->assertEquals(0, $fromInvalid->asMilliseconds());
        $this->assertSame('en', Moment::duration(1, 'd')->locale());
        $this->assertSame('PT10S', Moment::duration(9999.6, 'ms')->toISOString());
        $this->assertSame('PT60S', Moment::duration(59999.9996, 'ms')->toISOString());
        $this->assertSame('PT1S', Moment::duration(0.9996, 's')->toISOString());
        $this->assertTrue(is_callable(Moment::relativeTimeRounding()));
        $this->assertTrue(is_callable(Duration::relativeTimeRounding()));
        $this->assertFalse(Moment::relativeTimeRounding('not a function'));
        $invalid = Duration::invalid();
        $this->assertFalse($invalid->isValid());
        $this->assertSame('Invalid date', $invalid->toISOString());
        $this->assertSame('PT1H', Moment::duration(1, 'h')->toJSON());
        $this->assertSame('PT1H', Moment::duration(1, 'h')->toIsoString());
        try {
            Moment::duration(1, 'h')->nope();
            $this->fail('expected a BadMethodCallException');
        } catch (\BadMethodCallException $e) {
            $this->assertTrue(strpos($e->getMessage(), 'nope') !== false);
        }
        try {
            Moment::nope();
            $this->fail('expected a BadMethodCallException');
        } catch (\BadMethodCallException $e) {
            $this->assertTrue(strpos($e->getMessage(), 'nope') !== false);
        }

        // time zone data given NaN
        $this->assertSame(300, Tz::zone('America/New_York')->utcOffset(NAN));
        $this->assertSame(0, Tz::zone('UTC')->utcOffset(NAN));
        $this->assertSame('EST', Tz::zone('America/New_York')->abbr(NAN));

        // locale registry
        $this->assertSame('fr-ca', Registry::get(array('fr-ca', 'fr-ca-x'))->abbr);
        $this->assertSame('en-gb', Registry::get(array('en-gb', 'en'))->abbr);
        $this->assertSame('en', Moment::localeData()->abbr);
        $this->assertNull(Moment::localeData()->longDateFormat('bogus'));
        $fr = Moment::localeData('fr');
        $this->assertSame(array('lun.', 'mar.', 'mer.', 'jeu.', 'ven.', 'sam.', 'dim.'), $fr->weekdaysShort(true));
        $this->assertSame(array('lu', 'ma', 'me', 'je', 've', 'sa', 'di'), $fr->weekdaysMin(true));
        $this->assertNull(Moment::localeData()->erasParse('bogus', 'N', false));

        $this->assertSame('zz-t', Moment::locale('zz-t', array('months' => array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l'))));
        $this->assertSame('zz-t', Moment::locale());
        Moment::locale('en');
        Moment::defineLocale('zz-t', null);

        // updateLocale: the global locale stays, and undoing the update restores the old one
        Moment::defineLocale('zz2', array('months' => array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l')));
        Moment::locale('zz2');
        Moment::updateLocale('zz2', array('monthsShort' => array('1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12')));
        $this->assertSame('6', Moment::localeData('zz2')->monthsShort(Moment::utc($now)));
        Moment::updateLocale('zz2', null);
        $this->assertSame('zz2', Moment::locale());
        $this->assertSame('Jun', Moment::localeData('zz2')->monthsShort(Moment::utc($now)));
        Moment::locale('en');
        Moment::defineLocale('zz2', null);

        // a child of a plain (data only) locale
        Moment::defineLocale('zz-p', array('months' => array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l'), 'week' => array('dow' => 3, 'doy' => 9)));
        $child = Moment::defineLocale('zz-c', array('parentLocale' => 'zz-p', 'monthsShort' => array('1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12')));
        $this->assertSame('f', $child->months(Moment::utc($now)));
        $this->assertSame('6', $child->monthsShort(Moment::utc($now)));
        $this->assertSame(3, $child->firstDayOfWeek());
        Moment::defineLocale('zz-c', null);
        Moment::defineLocale('zz-p', null);

        // eras that don't cover the date
        Moment::defineLocale('zz-e', array('eras' => array(array('since' => '2000-01-01', 'until' => INF, 'offset' => 1, 'name' => 'New', 'narrow' => 'N', 'abbr' => 'N'))));
        $before = Moment::utc('1990-05-05')->locale('zz-e');
        $this->assertSame('', $before->eraName());
        $this->assertSame('', $before->eraAbbr());
        $this->assertSame('', $before->eraNarrow());
        $this->assertSame(1990, $before->eraYear());
        $this->assertSame('     1990', $before->format('N NN NNN NNNN NNNNN y'));
        $after = Moment::utc('2005-05-05')->locale('zz-e');
        $this->assertSame('New', $after->eraName());
        $this->assertSame(6, $after->eraYear());
        $this->assertSame('N 6', $after->format('N y'));
        Moment::defineLocale('zz-e', null);

        // a locale whose settings are all functions
        $bool = function ($b) {
            return $b ? 'true' : 'false';
        };
        $fn = Moment::defineLocale('zz-f', array(
            'relativeTime' => array(
                'future' => function ($s) {
                    return 'IN[' . $s . ']';
                },
                'past' => function ($s) {
                    return 'AGO[' . $s . ']';
                },
                's' => function ($n, $withoutSuffix, $key, $isFuture) use ($bool) {
                    return 'S' . $n . $bool($withoutSuffix) . $key . $bool($isFuture);
                },
                'm' => 'a minute', 'mm' => '%d mins', 'h' => 'an hour', 'hh' => '%d h', 'd' => 'a day', 'dd' => '%d d',
                'M' => 'a month', 'MM' => '%d M', 'y' => 'a year', 'yy' => '%d y',
            ),
            'meridiemParse' => '/AM|PM/',
            'meridiemHour' => function ($h, $meridiem) {
                return $meridiem === 'PM' ? $h + 12 : $h;
            },
            'meridiem' => function ($h) {
                return $h < 12 ? 'AM' : 'PM';
            },
            'months' => function ($m, $format = '') {
                return is_object($m) ? 'M' . $m->month() . $format : 'M';
            },
            'monthsShort' => function ($m, $format = '') {
                return is_object($m) ? 'S' . $m->month() : 'S';
            },
            'weekdays' => function ($m, $format = '') {
                return is_object($m) ? 'W' . $m->day() : 'W';
            },
            'weekdaysShort' => function ($m) {
                return is_object($m) ? 'WS' . $m->day() : 'WS';
            },
            'weekdaysMin' => function ($m) {
                return is_object($m) ? 'WM' . $m->day() : 'WM';
            },
        ));
        $n = Moment::utc($now);
        $this->assertSame(array('S5truestrue', 'IN[x]', 'AGO[x]', 'M5', 'M', 'M5MMMM', 'S5', 'S', 'W6', 'W', 'WS6', 'WS', 'WM6', 'WM', 'W', 'WS', 'WM'), array(
            $fn->relativeTime(5, true, 's', true), $fn->pastFuture(1, 'x'), $fn->pastFuture(-1, 'x'), $fn->months($n), $fn->months(), $fn->months($n, 'MMMM'),
            $fn->monthsShort($n), $fn->monthsShort(), $fn->weekdays($n), $fn->weekdays(), $fn->weekdaysShort($n), $fn->weekdaysShort(), $fn->weekdaysMin($n),
            $fn->weekdaysMin(), $fn->weekdays(true), $fn->weekdaysShort(true), $fn->weekdaysMin(true)));
        $this->assertSame('M5MMMM MMM dddd ddd dd S5 W6 WS6 WM6', Moment::utc($now)->locale('zz-f')->format('MMMM MMM dddd ddd dd'));
        $this->assertSame('IN[S5falsestrue]', Moment::duration(5, 's')->locale('zz-f')->humanize(true));
        $this->assertSame('S5truestrue', Moment::duration(5, 's')->locale('zz-f')->humanize());
        $this->assertSame('22', Moment::createUtc('10 PM', 'h A', 'zz-f')->format('HH'));
        $this->assertTrue($fn->hasMeridiemHour());
        Moment::defineLocale('zz-f', null);

        // every locale builds its month and weekday regexes
        foreach (Moment::locales() as $name) {
            $loc = Registry::get($name);
            $loc->monthsRegex();
            $loc->monthsShortRegex();
            $loc->monthsRegex(true);
            $loc->monthsShortRegex(true);
        }
        Moment::setNow(null);
        Moment::$suppressDeprecationWarnings = false;
    }

    /** more edges: hooks that throw, registry corners, guess without intl */
    public function testRemainingEdges()
    {
        $this->frozen();
        $this->assertSame('en', Registry::find(Moment::duration(1, 'd'))->abbr);

        $property = new \ReflectionProperty('Wilkques\Moment\Locale\Registry', 'global');
        $property->setAccessible(true);
        $saved = $property->getValue();
        $property->setValue(null, null);
        $this->assertSame('en', Registry::get()->abbr);
        $property->setValue(null, $saved);

        Moment::defineLocale('zz9', array('months' => array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l')));
        Moment::updateLocale('zz9', null);
        $this->assertNull(Registry::find('zz9'));

        Moment::$updateOffset = function () {
            throw new \RuntimeException('boom');
        };
        try {
            Moment::utc(5);
            $this->fail('the hook should have thrown');
        } catch (\RuntimeException $e) {
            $this->assertSame('boom', $e->getMessage());
        }
        $calls = 0;
        Moment::$updateOffset = function () use (&$calls) {
            $calls++;
        };
        Moment::utc(5);
        Moment::$updateOffset = null;
        $this->assertSame(1, $calls); // a throwing hook doesn't leave construction muted

        $this->assertSame('0', Duration::jsNumber(0.0));
        if (PHP_VERSION_ID >= 50400) { // PHP 5.3's json_encode() doesn't know JsonSerializable
            $this->assertSame('"PT1H"', json_encode(Moment::duration(1, 'h')));
            $this->assertSame('{"at":"2024-01-31T12:34:56.000Z","none":null}', json_encode(array('at' => Moment::utc('2024-01-31T12:34:56Z'), 'none' => Moment::invalid())));
        }

        Tz::$withoutIntl = true;
        Tz::$guessUseAbbr = true;
        Tz::$guessByName = false;
        date_default_timezone_set('America/New_York');
        try {
            $guess = Tz::guess(true);
        } catch (\Exception $e) {
            $guess = null;
        }
        Tz::$withoutIntl = false;
        Tz::$guessUseAbbr = null;
        Tz::$guessByName = true;
        date_default_timezone_set('UTC');
        $this->assertSame('America/New_York', $guess);
    }
}
