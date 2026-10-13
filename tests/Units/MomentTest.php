<?php

namespace Wilkques\Moment\Tests\Units;

use PHPUnit\Framework\TestCase;
use Wilkques\Moment\Duration;
use Wilkques\Moment\Locale\Registry;
use Wilkques\Moment\Moment;

class MomentTest extends TestCase
{
    /** 2024-06-15T10:00:00Z */
    const NOW = 1718445600000;

    protected function frozen()
    {
        date_default_timezone_set('UTC');
        Moment::setNow(self::NOW);
        Registry::globalLocale('en');
    }

    protected function ymd($m)
    {
        return $m->format('YYYY-MM-DD HH:mm:ss.SSS');
    }

    public function testAddMonthClampsToEndOfMonth()
    {
        $this->assertSame('2023-02-28', Moment::createUtc('2023-01-31')->add(1, 'month')->format('YYYY-MM-DD'));
        $this->assertSame('2024-02-29', Moment::createUtc('2024-01-31')->add(1, 'month')->format('YYYY-MM-DD'));
        $this->assertSame('2025-02-28', Moment::createUtc('2024-02-29')->add(1, 'year')->format('YYYY-MM-DD'));
        $this->assertSame('2023-11-30', Moment::createUtc('2024-01-31')->subtract(2, 'months')->format('YYYY-MM-DD'));
    }

    public function testMutatorsMutateAndCopyDoesNot()
    {
        $a = Moment::createUtc('2024-01-10');
        $b = $a->copy()->add(1, 'day');

        $this->assertSame('2024-01-10', $a->format('YYYY-MM-DD'));
        $this->assertSame('2024-01-11', $b->format('YYYY-MM-DD'));
        $this->assertSame($a, $a->add(1, 'day'));
        $this->assertSame('2024-01-11', $a->format('YYYY-MM-DD'));
    }

    public function testAddAcceptsObjectsAndDurations()
    {
        $m = Moment::createUtc('2024-01-01T00:00:00Z')->add(array('days' => 1, 'hours' => 2))->add(Moment::duration(30, 'minutes'));

        $this->assertSame('2024-01-02T02:30:00Z', $m->format());
    }

    public function testGettersAndSetters()
    {
        $m = Moment::createUtc(array(2024, 0, 31, 12, 34, 56, 789));

        $this->assertSame(array(2024, 0, 31, 12, 34, 56, 789), $m->toArray());
        $this->assertSame(31, $m->date());
        $this->assertSame(3, $m->day()); // Wednesday
        $this->assertSame(31, $m->dayOfYear());
        $this->assertSame(1, $m->quarter());
        $this->assertSame(5, $m->week());
        $this->assertSame(5, $m->isoWeek());

        $m->month(1); // January 31 -> February: clamped to the 29th
        $this->assertSame('2024-02-29', $m->format('YYYY-MM-DD'));

        $m->set(array('year' => 2023, 'hour' => 1));
        $this->assertSame('2023-02-28 01:34:56.789', $this->ymd($m));
        $this->assertSame('2023-03-02', $m->set('date', 30)->format('YYYY-MM-DD'));
    }

    public function testStartAndEndOf()
    {
        $m = Moment::createUtc('2024-05-15T13:45:10.123Z');

        $this->assertSame('2024-05-01 00:00:00.000', $this->ymd($m->copy()->startOf('month')));
        $this->assertSame('2024-05-31 23:59:59.999', $this->ymd($m->copy()->endOf('month')));
        $this->assertSame('2024-05-12', $m->copy()->startOf('week')->format('YYYY-MM-DD'));
        $this->assertSame('2024-05-13', $m->copy()->startOf('isoWeek')->format('YYYY-MM-DD'));
        $this->assertSame('2024-04-01', $m->copy()->startOf('quarter')->format('YYYY-MM-DD'));
    }

    public function testComparisons()
    {
        $a = Moment::createUtc('2024-05-15T13:45:00Z');
        $b = Moment::createUtc('2024-05-15T18:00:00Z');

        $this->assertTrue($a->isBefore($b));
        $this->assertFalse($a->isBefore($b, 'day'));
        $this->assertTrue($a->isSame($b, 'day'));
        $this->assertTrue($a->isSameOrBefore($b, 'day'));
        $this->assertTrue($a->isBetween('2024-05-15T00:00:00Z', '2024-05-16T00:00:00Z'));
        $this->assertFalse($a->isBetween($a, $b));
        $this->assertTrue($a->isBetween($a, $b, null, '[]'));
        $this->assertSame($b, Moment::max($a, $b));
        $this->assertSame($a, Moment::min($a, $b));
    }

    public function testDiff()
    {
        $a = Moment::createUtc('2024-03-31');
        $b = Moment::createUtc('2024-01-31');

        $this->assertSame(2, $a->diff($b, 'months'));
        $this->assertSame(-2, $b->diff($a, 'months'));
        $this->assertSame(60, $a->diff($b, 'days'));
        $this->assertTrue(abs(8.571428571428571 - $a->diff($b, 'weeks', true)) < 1e-9);
        $this->assertSame(0, Moment::createUtc('2024-03-31')->diff('2024-03-31T00:00:00.999Z', 'seconds'));
    }

    public function testInvalidMoments()
    {
        $m = Moment::createUtc('not a date');

        $this->assertFalse($m->isValid());
        $this->assertSame('Invalid date', $m->format());
        $this->assertNull($m->toISOString());
        $this->assertTrue(is_nan($m->valueOf()));
        $this->assertTrue(is_nan($m->diff(Moment::create())));
        $this->assertFalse($m->isBefore(Moment::create()));
        $this->assertFalse(Moment::createUtc('2024-02-30')->isValid());
    }

    public function testStrictParsing()
    {
        $this->assertTrue(Moment::createUtc('2024-01-05', 'YYYY-MM-DD', true)->isValid());
        $this->assertFalse(Moment::createUtc('2024-1-5', 'YYYY-MM-DD', true)->isValid());
        $this->assertTrue(Moment::createUtc('2024-1-5', 'YYYY-MM-DD')->isValid());
        $this->assertFalse(Moment::createUtc('2024-01-05 trailing', 'YYYY-MM-DD', true)->isValid());
        $this->assertSame('2024-01-05', Moment::createUtc('05/01/2024', array('YYYY-MM-DD', 'DD/MM/YYYY'))->format('YYYY-MM-DD'));
    }

    public function testFallsBackToJsDateParsing()
    {
        // like moment, unrecognised strings go to what `new Date(string)` would make of them (V8's parser)
        $this->assertSame('2024-01-05T00:00:00.000Z', Moment::createUtc('January 5, 2024')->toISOString());
        $this->assertSame('2024-01-05T22:30:00.000Z', Moment::createUtc('Jan 5 2024 10:30 PM')->toISOString());
        // a local moment honours the offset; a utc() one appends " UTC", which wins (as in moment)
        $this->assertSame('2024-01-05T02:30:00.000Z', Moment::create('Jan 5, 2024 10:30 +0800')->toISOString());
        $this->assertSame('2024-01-05T10:30:00.000Z', Moment::createUtc('Jan 5, 2024 10:30 +0800')->toISOString());
        $this->assertFalse(Moment::createUtc('next thursday')->isValid());
        // ... but not in strict mode, and an ISO-looking string that is invalid stays invalid
        $this->assertFalse(Moment::createUtc('January 5, 2024', true)->isValid());
        $this->assertFalse(Moment::createUtc('2024-02-30')->isValid());
    }

    public function testFormatEscapesAndLongFormats()
    {
        $m = Moment::createUtc('2024-01-05T15:04:05Z');

        $this->assertSame('Q YYYY 2024', $m->format('[Q YYYY] YYYY'));
        $this->assertSame('01/05/2024', $m->format('L'));
        $this->assertSame('Friday, January 5, 2024 3:04 PM', $m->format('LLLL'));
        $this->assertSame('5th', $m->format('Do'));
        $this->assertSame('2024-01-05T15:04:05Z', $m->format());
    }

    public function testLocales()
    {
        $this->frozen();
        $m = Moment::createUtc('2024-01-05T15:04:05Z');

        $this->assertSame('2024年1月5日星期五 15:04', $m->copy()->locale('zh-tw')->format('LLLL'));
        $this->assertSame('下午3点04分', $m->copy()->locale('zh-cn')->format('Ah点mm分'));
        $this->assertSame('zh-tw', $m->copy()->locale('zh_TW')->locale());
        $this->assertSame('en', $m->locale());

        Registry::globalLocale('zh-tw');
        $this->assertSame('zh-tw', Moment::createUtc()->locale());
        $this->assertSame('1 個月', Moment::createUtc('2024-07-16')->from('2024-06-15', true));
        Registry::globalLocale('en');
    }

    public function testRelativeTime()
    {
        $this->frozen();
        $m = Moment::create(self::NOW - 90 * 60000);

        $this->assertSame('2 hours ago', $m->fromNow());
        $this->assertSame('2 hours', $m->fromNow(true));
        $this->assertSame('in 2 hours', Moment::create(self::NOW + 90 * 60000)->fromNow());
        $this->assertSame('Today at 8:30 AM', $m->calendar());
        $this->assertSame('Yesterday at 10:00 AM', Moment::create(self::NOW - 86400000)->calendar());
        $this->assertSame('01/06/2024', Moment::create(self::NOW - 14 * 86400000)->calendar(null, array('sameElse' => 'DD/MM/YYYY')));
    }

    public function testDuration()
    {
        $d = Moment::duration(array('hours' => 25, 'minutes' => 61));

        $this->assertSame(2, $d->hours());
        $this->assertSame(1, $d->days());
        $this->assertSame(1, $d->minutes());
        $this->assertSame('PT26H1M', $d->toISOString());
        $this->assertSame('a day', $d->humanize());
        $this->assertSame('in a day', $d->humanize(true));
        $this->assertTrue(abs(26.016666666666666 - $d->asHours()) < 1e-9);
        $this->assertSame('PT36H', Moment::duration('PT36H')->toISOString());
        $this->assertTrue(Duration::isDuration($d));
        $this->assertEquals(array(60000, 0, 0), Moment::duration(1, 'minute')->parts());
    }

    public function testRelativeTimeThresholds()
    {
        $this->assertSame('a few seconds', Moment::duration(44, 'seconds')->humanize());
        $this->assertSame('a minute', Moment::duration(45, 'seconds')->humanize());
        $this->assertTrue(Duration::relativeTimeThreshold('s', 50));
        $this->assertSame('a few seconds', Moment::duration(49, 'seconds')->humanize());
        $this->assertSame('a minute', Moment::duration(50, 'seconds')->humanize());
        Duration::relativeTimeThreshold('s', 45);
        $this->assertSame(44, Duration::relativeTimeThreshold('ss'));
    }

    public function testTimeZones()
    {
        $m = Moment::createInZone('2024-03-10T01:30:00', 'America/New_York');

        $this->assertSame('America/New_York', $m->tz());
        $this->assertSame(-300.0, (float) $m->utcOffset());
        $this->assertSame('2024-03-10T03:30:00-04:00', $m->copy()->add(1, 'hour')->format());
        $this->assertSame('2024-03-10T03:30:00-04:00', Moment::createInZone('2024-03-10T02:30:00', 'America/New_York')->format()); // gap moves forward
        $this->assertSame('2024-11-03T01:30:00-04:00', Moment::createInZone('2024-11-03T01:30:00', 'America/New_York')->format()); // overlap: earlier
        $this->assertSame('2024-03-10T14:30:00+08:00', $m->copy()->tz('Asia/Taipei')->format());
        $this->assertSame('2024-03-10T01:30:00+08:00', $m->copy()->tz('Asia/Taipei', true)->format());
        $this->assertSame('EST', $m->zoneAbbr());
        $this->assertSame('2024-03-10T00:00:00-05:00', $m->copy()->startOf('day')->format());
        $this->assertSame('2024-03-10T23:59:59-04:00', $m->copy()->endOf('day')->format());
    }

    public function testFixedOffsets()
    {
        $m = Moment::createUtc('2024-01-01T00:00:00Z');

        $this->assertSame('2024-01-01T08:00:00+08:00', $m->copy()->utcOffset(480)->format());
        $this->assertSame('2024-01-01T08:00:00+08:00', $m->copy()->utcOffset('+08:00')->format());
        $this->assertSame('2024-01-01T08:00:00+08:00', $m->copy()->utcOffset(8)->format());
        $this->assertSame('2024-01-01T00:00:00+08:00', $m->copy()->utcOffset(8, true)->format());
        $this->assertTrue($m->isUtc());
        $this->assertSame('2023-12-31T16:00:00Z', Moment::createUtc('2024-01-01T00:00:00+08:00')->format());
    }

    public function testLocalUsesDefaultTimezone()
    {
        date_default_timezone_set('Asia/Taipei');
        $m = Moment::create('2024-01-01T00:00:00');

        $this->assertTrue($m->isLocal());
        $this->assertSame('2023-12-31T16:00:00.000Z', $m->toISOString());
        $this->assertSame(480.0, (float) $m->utcOffset());
        date_default_timezone_set('UTC');
    }

    public function testUnix()
    {
        $this->assertSame(1704067200, Moment::createUtc('2024-01-01')->unix());
        $this->assertSame('2024-01-01', Moment::fromUnix(1704067200)->utc()->format('YYYY-MM-DD'));
        $this->assertSame('2024-01-01T00:00:00.000Z', Moment::createUtc('1704067200', 'X')->toISOString());
    }

    public function testWeekYears()
    {
        $m = Moment::createUtc('2021-01-03');

        $this->assertSame(2020, $m->isoWeekYear());
        $this->assertSame(53, $m->isoWeek());
        $this->assertSame(53, Moment::createUtc('2020-06-01')->isoWeeksInYear());
        $this->assertSame('2019-12-30', $m->copy()->isoWeek(1)->isoWeekday(1)->format('YYYY-MM-DD'));
    }
}
