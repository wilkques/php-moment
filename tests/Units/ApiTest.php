<?php

namespace Wilkques\Moment\Tests\Units;

use PHPUnit\Framework\TestCase;
use Wilkques\Moment\Duration;
use Wilkques\Moment\Locale\Registry;
use Wilkques\Moment\Moment;
use Wilkques\Moment\Timezone\Tz;

/**
 * The parts of moment's API beyond everyday use: aliases, same-name static/instance calls,
 * deprecated spellings, runtime locales, creation data and the moment-timezone data API.
 */
class ApiTest extends TestCase
{
    protected function frozen()
    {
        date_default_timezone_set('UTC');
        Moment::setNow(1718445600000); // 2024-06-15T10:00:00Z
        Moment::$suppressDeprecationWarnings = true;
        Registry::globalLocale('en');
    }

    public function testPluralAliasesGetAndSet()
    {
        $this->frozen();
        $m = Moment::createUtc('2024-01-31T12:34:56.789Z');

        $this->assertSame(2024, $m->years());
        $this->assertSame(31, $m->dates());
        $this->assertSame(3, $m->days());
        $this->assertSame(12, $m->hours());
        $this->assertSame(34, $m->minutes());
        $this->assertSame(56, $m->seconds());
        $this->assertSame(789, $m->milliseconds());
        $this->assertSame(0, $m->months());
        $this->assertSame(1, $m->quarters());
        $this->assertSame(5, $m->weeks());
        $this->assertSame(5, $m->isoWeeks());

        $this->assertSame($m, $m->hours(1)->minutes(2));
        $this->assertSame('01:02', $m->format('HH:mm'));
        $this->assertSame('2024-02-29', $m->months(1)->format('YYYY-MM-DD'));
    }

    public function testSameNameStaticAndInstanceCalls()
    {
        $this->frozen();
        $a = Moment::utc('2024-01-31T12:00:00Z');                    // static factory
        $b = Moment::create('2024-01-31T12:00:00Z')->utc();          // instance
        $this->assertSame($a->valueOf(), $b->valueOf());
        $this->assertTrue($b->isUtc());

        $this->assertSame(1704067200, Moment::unix(1704067200)->unix());
        $this->assertSame('2024-03-10T01:30:00-05:00', Moment::tz('2024-03-10T01:30:00', 'America/New_York')->format());
        $this->assertSame('America/New_York', Moment::tz('2024-03-10T01:30:00', 'America/New_York')->tz());

        $this->assertSame('fr', Moment::locale('fr'));
        $this->assertSame('fr', Moment::locale());
        $this->assertSame('fr', Moment::create()->locale());
        $this->assertSame('fr', Moment::create()->localeData()->abbr);
        $this->assertSame('janvier', Moment::months(0));
        $this->assertSame('dimanche', Moment::weekdays(0));
        $this->assertSame('lundi', Moment::weekdays(true, 0));
        $this->assertSame(array('lu', 'ma', 'me', 'je', 've', 'sa', 'di'), Moment::weekdaysMin(true));
        Moment::locale('en');

        $x = Moment::utc('2024-01-01');
        $y = Moment::utc('2024-06-01');
        $this->assertSame($y, Moment::max($x, $y));
        $this->assertSame($x, Moment::min($x, $y));
        $this->assertSame($y, Moment::max(array($x, $y)));
    }

    public function testDeprecatedSpellings()
    {
        $this->frozen();
        $seen = array();
        Moment::$deprecationHandler = function ($name, $message) use (&$seen) {
            $seen[] = $name;
        };

        $m = Moment::utc('2024-01-31T12:00:00Z');
        $this->assertSame('en', $m->lang()->abbr);
        $this->assertSame('fr', $m->copy()->lang('fr')->locale());
        $this->assertSame(0.0, (float) $m->zone());
        $this->assertSame('2024-01-31T20:00:00+08:00', $m->copy()->zone(-480)->format());
        $this->assertSame('2024-03-31T12:00:00.000Z', $m->copy()->add('months', 2)->toISOString());   // inverted arguments
        $this->assertSame('PT1H', Moment::duration(1, 'hour')->toIsoString()); // PHP method names are case-insensitive: same as toISOString()
        $this->assertSame('en', Moment::lang());
        $this->assertSame('en', Moment::langData()->abbr);

        Moment::$deprecationHandler = null;
        foreach (array('lang', 'zone', 'add', 'langData') as $name) {
            $this->assertContains($name, $seen);
        }
    }

    public function testAddAcceptsEveryDurationShape()
    {
        $m = Moment::utc('2024-01-31T12:00:00Z');

        $this->assertSame('2024-01-31T13:30:00Z', $m->copy()->add('PT1H30M')->format());
        $this->assertSame('2024-03-01T12:00:00Z', $m->copy()->add(array('months' => 1, 'days' => 1))->format());
        $this->assertSame('2024-01-31T12:00:00Z', $m->copy()->add('bogus', 'bogus')->format());
        $this->assertSame('2024-01-31T11:00:00Z', $m->copy()->subtract(Moment::duration(1, 'hour'))->format());
        $this->assertSame('2024-02-01T00:00:00Z', $m->copy()->add('12:00:00')->format());
    }

    public function testNullEmptyAndObjectInput()
    {
        $this->frozen();

        $this->assertFalse(Moment::create(null)->isValid());
        $flags = Moment::create(null)->parsingFlags();
        $this->assertTrue($flags['nullInput']);
        $this->assertFalse(Moment::create('')->isValid());
        $this->assertSame(1718445600000, Moment::create()->valueOf());          // no argument: now
        $this->assertSame(1718445600000, Moment::create(array())->valueOf());   // empty array/object: now too
        $this->assertFalse(Moment::create(NAN)->isValid());
        $this->assertFalse(Moment::create(1e16)->isValid());

        $m = Moment::createUtc(array('year' => 2024, 'month' => 5, 'day' => 5, 'hour' => 10));
        $this->assertSame('2024-06-05T10:00:00Z', $m->format());
        $this->assertSame('2024-02-29', Moment::createUtc(array('y' => 2024, 'M' => 1, 'd' => 29))->format('YYYY-MM-DD'));
        $this->assertFalse(Moment::createUtc(array('year' => 2023, 'month' => 1, 'day' => 29))->isValid());
    }

    public function testParsingFlagsAndCreationData()
    {
        $m = Moment::createUtc('2024-02-30');
        $this->assertFalse($m->isValid());
        $this->assertSame(2, $m->invalidAt());                 // the date overflowed
        $flags = $m->parsingFlags();
        $this->assertTrue($flags['iso']);

        $m = Moment::createUtc('31/01/2024', 'DD/MM/YYYY', true);
        $data = $m->creationData();
        $this->assertSame('31/01/2024', $data['input']);
        $this->assertSame('DD/MM/YYYY', $data['format']);
        $this->assertTrue($data['strict']);
        $this->assertTrue($data['isUTC']);
        $this->assertSame('en', $data['locale']->abbr);

        $this->assertSame(-2, Moment::createUtc(1706704496789)->invalidAt());
    }

    public function testParseZone()
    {
        $m = Moment::parseZone('2024-01-31T12:34:56+08:00');
        $this->assertSame(480.0, (float) $m->utcOffset());
        $this->assertSame('2024-01-31T12:34:56+08:00', $m->format());

        $this->assertSame(-330.0, (float) Moment::parseZone('2024-01-31T12:34:56-0530')->utcOffset());
        $this->assertSame(0.0, (float) Moment::parseZone('2024-01-31')->utcOffset());
        $this->assertSame(540.0, (float) Moment::create('Wed, 31 Jan 2024 12:34:56 +0900')->parseZone()->utcOffset());
    }

    public function testEraAndWeekHelpers()
    {
        $m = Moment::createUtc('2024-01-31');
        $this->assertSame('Anno Domini', $m->eraName());
        $this->assertSame('AD', $m->eraAbbr());
        $this->assertSame('AD', $m->eraNarrow());
        $this->assertSame(2024, $m->eraYear());
        $this->assertSame('Before Christ', Moment::createUtc(array(-10, 0, 1))->eraName());
        $this->assertSame(11, Moment::createUtc(array(-10, 0, 1))->eraYear());

        $this->assertSame(53, Moment::createUtc('2021-01-03')->isoWeeksInISOWeekYear());
        $this->assertSame(52, Moment::createUtc('2024-06-01')->weeksInWeekYear());
    }

    public function testHasAlignedHourOffsetAndDst()
    {
        $this->assertTrue(Moment::tz('2024-06-01', 'Asia/Taipei')->hasAlignedHourOffset());
        $this->assertFalse(Moment::tz('2024-06-01', 'Asia/Kolkata')->hasAlignedHourOffset());
        $this->assertTrue(Moment::tz('2024-06-01', 'Asia/Kolkata')->hasAlignedHourOffset(Moment::parseZone('2024-01-01T00:00:00+05:30')));

        $this->assertTrue(Moment::tz('2024-07-01', 'America/New_York')->isDST());
        $this->assertFalse(Moment::tz('2024-01-01', 'America/New_York')->isDST());
        $this->assertFalse(Moment::tz('2024-07-01', 'Asia/Taipei')->isDST());
    }

    public function testInspect()
    {
        $this->assertSame('moment.utc("2024-01-31T12:00:00.000+00:00")', Moment::utc('2024-01-31T12:00:00Z')->inspect());
        $this->assertSame('moment.parseZone("2024-01-31T20:00:00.000+08:00")', Moment::parseZone('2024-01-31T20:00:00+08:00')->inspect());
        $this->assertSame('moment.invalid(/* nope */)', Moment::utc('nope', 'YYYY')->inspect());
    }

    public function testDurationAsAndClone()
    {
        $d = Moment::duration(90, 'minutes');
        $this->assertTrue(abs($d->as('hours') - 1.5) < 1e-9);
        $copy = $d->clone();
        $copy->add(30, 'minutes');
        $this->assertSame('PT1H30M', $d->toISOString());
        $this->assertSame('PT2H', $copy->toISOString());
        $this->assertSame('en', $d->localeData()->abbr);
    }

    public function testRuntimeLocales()
    {
        $this->frozen();
        $made = Moment::defineLocale('xx', array(
            'months' => array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l'),
            'ordinal' => function ($number, $token) {
                return $number . '!';
            },
            'relativeTime' => array('future' => 'soon: %s', 'past' => '%s back'),
            'week' => array('dow' => 1, 'doy' => 4),
        ));
        $this->assertSame('xx', $made->abbr);
        $m = Moment::createUtc('2024-03-05')->locale('xx');
        $this->assertSame('c 5! 2024', $m->format('MMMM Do YYYY'));
        $this->assertSame('a day back', Moment::createUtc('2024-03-04')->locale('xx')->from('2024-03-05'));
        $this->assertSame(1, $m->localeData()->firstDayOfWeek());
        $this->assertContains('xx', Moment::locales());

        // updateLocale changes an existing locale, and null undoes it
        Moment::updateLocale('en', array('months' => array('1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12')));
        $this->assertSame('3', Moment::createUtc('2024-03-05')->locale('en')->format('MMMM'));
        $this->assertSame('Mar', Moment::createUtc('2024-03-05')->locale('en')->format('MMM'));
        Moment::updateLocale('en', null);
        $this->assertSame('March', Moment::createUtc('2024-03-05')->locale('en')->format('MMMM'));

        Moment::defineLocale('xx', null);
        $this->assertNull(Registry::find('xx'));
        Moment::locale('en');
    }

    public function testLocaleLookup()
    {
        $this->assertSame('zh-tw', Registry::get('zh_TW')->abbr);
        $this->assertSame('en', Registry::get('en-US')->abbr);              // falls back to the language
        $this->assertSame('fr-ca', Registry::get(array('xx-yy', 'fr-ca-zz'))->abbr);
        $this->assertNull(Registry::find('nonexistent'));
        $this->assertSame('en', Registry::get('nonexistent')->abbr);        // like moment: unknown names give the global locale
        $this->assertSame('ru', Registry::get('ru')->abbr);
    }

    public function testRelativeTimeInOtherLocales()
    {
        $this->frozen();
        $this->assertSame('через 5 минут', Moment::create(1718445600000 + 300000)->locale('ru')->fromNow());
        $this->assertSame('5 minutes ago', Moment::create(1718445600000 - 300000)->fromNow());
        $this->assertSame('vor 2 Stunden', Moment::create(1718445600000 - 7200000)->locale('de')->fromNow());
    }

    public function testTimezoneDataApi()
    {
        $names = Tz::names();
        $this->assertTrue(count($names) > 500);
        $this->assertContains('Asia/Taipei', $names);
        $this->assertContains('Asia/Calcutta', $names);        // links are zones too
        $this->assertTrue(strlen(Tz::dataVersion()) > 0);

        $zone = Tz::zone('asia/taipei');
        $this->assertSame('Asia/Taipei', $zone->name);
        $this->assertEquals(-480, $zone->utcOffset(1704067200000));     // minutes, positive WEST
        $this->assertSame('CST', $zone->abbr(1704067200000));
        $this->assertSame('Asia/Calcutta', Tz::zone('Asia/Calcutta')->name);
        $this->assertNull(Tz::zone('No/Such_Zone'));
        $this->assertFalse(Tz::zoneExists('No/Such_Zone'));
        $this->assertSame(array('Asia/Taipei'), Tz::zonesForCountry('tw'));
        $this->assertNull(Tz::zonesForCountry('xx'));

        Tz::add('Test/Zone|AAA BBB|-60 -120|0101|-1z4C0 8Q40 8Q40|10');
        $this->assertContains('Test/Zone', Tz::names());
        $this->assertSame('Test/Zone', Moment::tz(Moment::utc('2020-01-01')->valueOf(), 'Test/Zone')->tz());
        Tz::reset();
        $this->assertNotContains('Test/Zone', Tz::names());
    }

    public function testDefaultZone()
    {
        $this->frozen();
        Tz::setDefault('America/New_York');
        $this->assertSame('2024-06-15T06:00:00-04:00', Moment::create()->format());
        $this->assertSame('2024-06-15T10:00:00-04:00', Moment::create('2024-06-15 10:00')->format());
        $this->assertSame('2024-06-15T10:00:00Z', Moment::utc()->format());
        Tz::setDefault(null);
        $this->assertSame('2024-06-15T10:00:00+00:00', Moment::create()->format());
    }

    public function testHtml5FormatsAndVersion()
    {
        $m = Moment::utc('2024-01-31T12:34:56.789Z');
        $this->assertSame('2024-01-31T12:34', $m->format(Moment::$HTML5_FMT['DATETIME_LOCAL']));
        $this->assertSame('2024-W05', $m->format(Moment::$HTML5_FMT['WEEK']));
        $this->assertSame('12:34:56.789', $m->format(Moment::$HTML5_FMT['TIME_MS']));
        $this->assertSame('2.30.1', Moment::VERSION);
    }

    public function testHooks()
    {
        $this->frozen();
        Moment::$parseTwoDigitYear = function ($input) {
            return 1900 + (int) $input;
        };
        $this->assertSame(1924, Moment::createUtc('24-01-05', 'YY-MM-DD')->year());
        Moment::$parseTwoDigitYear = null;
        $this->assertSame(2024, Moment::createUtc('24-01-05', 'YY-MM-DD')->year());

        Moment::$calendarFormat = function ($m, $now) {
            return 'sameDay';
        };
        $this->assertSame('Today at 12:00 AM', Moment::createUtc('2000-01-01')->calendar());
        Moment::$calendarFormat = null;

        Moment::$createFromInputFallback = function ($input, $useUtc) {
            return array('wall' => 0, 'tzm' => 0);
        };
        $this->assertSame(0, Moment::createUtc('whatever you like')->valueOf());
        Moment::$createFromInputFallback = null;
        $this->assertFalse(Moment::createUtc('whatever you like')->isValid());
    }

    public function testMacrosAndUpdateOffsetHook()
    {
        $this->frozen();
        Moment::macro('endOfNoon', function ($m, $minutes) {
            return $m->hour(12)->minute($minutes);
        });
        $this->assertSame('12:45', Moment::utc('2024-01-31T01:00:00Z')->endOfNoon(45)->format('HH:mm'));

        $calls = array();
        Moment::$updateOffset = function ($m, $keepTime) use (&$calls) {
            $calls[] = $keepTime;
        };
        Moment::utc('2024-01-31T01:00:00Z')->add(1, 'day')->add(1, 'hour');
        Moment::$updateOffset = null;
        $this->assertSame(array(null, true, false), $calls);
    }

    public function testSmallAccessors()
    {
        $this->assertSame('PT1H', Moment::duration(1, 'hour')->toString());
        Tz::setDefault('Asia/Taipei');
        $this->assertSame('Asia/Taipei', Moment::defaultZone()->name);
        Tz::setDefault(null);
        $this->assertNull(Moment::defaultZone());
        $this->assertTrue(Tz::needsOffset(Moment::utc('2024-01-31')));
        $this->assertFalse(Tz::needsOffset(Moment::utc(1706704496789)));
        $this->assertFalse(Tz::needsOffset(Moment::utc('2024-01-31T00:00:00+08:00')));
    }

    public function testInvalidWithFlags()
    {
        $m = Moment::invalid();
        $flags = $m->parsingFlags();
        $this->assertTrue($flags['userInvalidated']);
        $this->assertFalse($m->isValid());

        $m = Moment::invalid(array('invalidMonth' => 'foo'));
        $flags = $m->parsingFlags();
        $this->assertSame('foo', $flags['invalidMonth']);
        $this->assertFalse($flags['userInvalidated']);
    }
}
