<?php

namespace Wilkques\Moment\Parse;

use Wilkques\Moment\Calendar;

/**
 * V8's date string parser (what `new Date(string)` / Date.parse use): the ES5 ISO 8601 form
 * and V8's lenient "legacy" form ("Jan 5, 2024 10:30 PM", "2024/01/05", "5 January 2024 EST", ...).
 * moment.js falls back to it for strings it can't recognise, so moment's results depend on it.
 *
 * Ported from V8's src/date/dateparser.{h,cc} and dateparser-inl.h.
 */
class V8DateParser
{
    const T_INVALID = -6;
    const T_UNKNOWN = -5;
    const T_WHITESPACE = -4;
    const T_NUMBER = -3;
    const T_SYMBOL = -2;
    const T_END = -1;

    // keyword types (tags >= 0)
    const K_INVALID = 0;
    const K_MONTH = 1;
    const K_TZ = 2;
    const K_TSEP = 3;
    const K_AMPM = 4;

    const NONE = null;
    const MAX_SIGNIFICANT_DIGITS = 9;

    /** prefix (3 letters, '' = end) => array(type, value) */
    protected static $keywords = array(
        'jan' => array(self::K_MONTH, 1), 'feb' => array(self::K_MONTH, 2), 'mar' => array(self::K_MONTH, 3),
        'apr' => array(self::K_MONTH, 4), 'may' => array(self::K_MONTH, 5), 'jun' => array(self::K_MONTH, 6),
        'jul' => array(self::K_MONTH, 7), 'aug' => array(self::K_MONTH, 8), 'sep' => array(self::K_MONTH, 9),
        'oct' => array(self::K_MONTH, 10), 'nov' => array(self::K_MONTH, 11), 'dec' => array(self::K_MONTH, 12),
        'am' => array(self::K_AMPM, 0), 'pm' => array(self::K_AMPM, 12),
        'ut' => array(self::K_TZ, 0), 'utc' => array(self::K_TZ, 0), 'z' => array(self::K_TZ, 0), 'gmt' => array(self::K_TZ, 0),
        'cdt' => array(self::K_TZ, -5), 'cst' => array(self::K_TZ, -6), 'edt' => array(self::K_TZ, -4), 'est' => array(self::K_TZ, -5),
        'mdt' => array(self::K_TZ, -6), 'mst' => array(self::K_TZ, -7), 'pdt' => array(self::K_TZ, -7), 'pst' => array(self::K_TZ, -8),
        't' => array(self::K_TSEP, 0),
    );

    protected $chars = array();
    protected $pos = 0;
    protected $count = 0;
    /** @var array lookahead token */
    protected $current;

    // composers
    protected $day = array();          // up to 3 numbers
    protected $namedMonth = null;
    protected $isoDate = false;
    protected $time = array();         // up to 4 numbers
    protected $hourOffset = null;
    protected $tzSign = null;
    protected $tzHour = null;
    protected $tzMinute = null;

    /**
     * Parse a date string like Date.parse.
     *
     * @return array|null array(year, month0, day, hour, minute, second, ms, utcOffsetSeconds|null) or null (NaN)
     */
    public static function fields($string)
    {
        $p = new self($string);

        return $p->run();
    }

    /**
     * @return array|null array('wall' => ms (the fields read as UTC), 'tzm' => offset minutes|null)
     */
    public static function parse($string)
    {
        $f = self::fields($string);
        if ($f === null) {
            return null;
        }
        $wall = Calendar::utcMs($f[0], $f[1], $f[2], $f[3], $f[4], $f[5], $f[6]);
        $tzm = $f[7] === null ? null : $f[7] / 60;
        $instant = $tzm === null ? $wall : $wall - $tzm * 60000;
        if (abs($instant) > 8.64e15 + ($tzm === null ? 14 * 3600000 : 0)) {
            return null;
        }

        // without a zone of its own the date is in the HOST's local time, whatever mode the moment is in
        return array('wall' => $wall, 'tzm' => $tzm, 'host' => $tzm === null);
    }

    protected function __construct($string)
    {
        $chars = preg_split('//u', $string, -1, PREG_SPLIT_NO_EMPTY);
        $this->chars = $chars === false ? str_split($string) : $chars;
        $this->count = count($this->chars);
        $this->current = $this->scan();
    }

    // ------------------------------------------------------------------ character classes

    protected static function code($ch)
    {
        $o = ord($ch[0]);
        if ($o < 0x80 || strlen($ch) === 1) {
            return $o;
        }
        if ($o < 0xE0) {
            return (($o & 0x1F) << 6) | (ord($ch[1]) & 0x3F);
        }
        if ($o < 0xF0) {
            return (($o & 0x0F) << 12) | ((ord($ch[1]) & 0x3F) << 6) | (ord($ch[2]) & 0x3F);
        }

        return 0x10000 + ((($o & 0x07) << 18) | ((ord($ch[1]) & 0x3F) << 12) | ((ord($ch[2]) & 0x3F) << 6) | (ord($ch[3]) & 0x3F));
    }

    protected static function isWhiteSpace($code)
    {
        return ($code >= 9 && $code <= 13) || $code === 32 || $code === 0xA0 || $code === 0x1680
            || ($code >= 0x2000 && $code <= 0x200A) || $code === 0x2028 || $code === 0x2029
            || $code === 0x202F || $code === 0x205F || $code === 0x3000 || $code === 0xFEFF;
    }

    protected function ch()
    {
        return $this->pos < $this->count ? $this->chars[$this->pos] : null;
    }

    protected function isDigit()
    {
        $c = $this->ch();

        return $c !== null && strlen($c) === 1 && $c >= '0' && $c <= '9';
    }

    // ------------------------------------------------------------------ tokenizer

    protected static function tok($tag, $value = 0, $length = 0)
    {
        return array('tag' => $tag, 'value' => $value, 'length' => $length);
    }

    protected function scan()
    {
        $pre = $this->pos;
        if ($this->pos >= $this->count) {
            return self::tok(self::T_END);
        }
        if ($this->isDigit()) {
            $n = 0;
            $i = 0;
            while ($this->ch() === '0') {
                $this->pos++;
            }
            while ($this->isDigit()) {
                if ($i < self::MAX_SIGNIFICANT_DIGITS) {
                    $n = $n * 10 + (int) $this->ch();
                }
                $i++;
                $this->pos++;
            }

            return self::tok(self::T_NUMBER, $n, $this->pos - $pre);
        }
        $c = $this->ch();
        if ($c === ':' || $c === '-' || $c === '+' || $c === '.' || $c === ')') {
            $this->pos++;

            return self::tok(self::T_SYMBOL, $c);
        }
        $code = self::code($c);
        if ($code >= 65 && !self::isWhiteSpace($code)) {
            $prefix = '';
            $len = 0;
            while ($this->pos < $this->count) {
                $cc = self::code($this->chars[$this->pos]);
                if (!($cc >= 65 && !self::isWhiteSpace($cc))) {
                    break;
                }
                if ($len < 3) {
                    $prefix .= $cc < 128 ? chr($cc | 0x20) : "\x01";
                }
                $this->pos++;
                $len++;
            }
            $type = self::K_INVALID;
            $value = 0;
            if (isset(self::$keywords[$prefix])) {
                // a word longer than the keyword only counts for month names
                $kw = self::$keywords[$prefix];
                if ($len <= 3 || $kw[0] === self::K_MONTH) {
                    list($type, $value) = $kw;
                }
            }

            return self::tok($type, $value, $len);
        }
        if (self::isWhiteSpace($code)) {
            $this->pos++;

            return self::tok(self::T_WHITESPACE, 0, 1);
        }
        if ($c === '(') {
            $balance = 0;
            do {
                $cc = $this->ch();
                if ($cc === ')') {
                    --$balance;
                } elseif ($cc === '(') {
                    ++$balance;
                }
                $this->pos++;
            } while ($balance > 0 && $this->pos < $this->count);

            return self::tok(self::T_UNKNOWN);
        }
        $this->pos++;

        return self::tok(self::T_UNKNOWN);
    }

    protected function next()
    {
        $result = $this->current;
        $this->current = $this->scan();

        return $result;
    }

    protected function peek()
    {
        return $this->current;
    }

    protected function skipSymbol($c)
    {
        if ($this->current['tag'] === self::T_SYMBOL && $this->current['value'] === $c) {
            $this->current = $this->scan();

            return true;
        }

        return false;
    }

    protected static function isSymbol($t, $c)
    {
        return $t['tag'] === self::T_SYMBOL && $t['value'] === $c;
    }

    protected static function isSign($t)
    {
        return $t['tag'] === self::T_SYMBOL && ($t['value'] === '+' || $t['value'] === '-');
    }

    protected static function signOf($t)
    {
        return $t['value'] === '+' ? 1 : -1;
    }

    protected static function isNumber($t)
    {
        return $t['tag'] === self::T_NUMBER;
    }

    protected static function isFixed($t, $length)
    {
        return $t['tag'] === self::T_NUMBER && $t['length'] === $length;
    }

    protected static function isKeyword($t)
    {
        return $t['tag'] >= 0;
    }

    protected static function isZ($t)
    {
        return $t['tag'] === self::K_TZ && $t['length'] === 1;
    }

    // ------------------------------------------------------------------ composers

    protected static function between($x, $lo, $hi)
    {
        return $x >= $lo && $x <= $hi;
    }

    protected function dayAdd($n)
    {
        if (count($this->day) < 3) {
            $this->day[] = $n;

            return true;
        }

        return false;
    }

    protected function timeAdd($n)
    {
        if (count($this->time) < 4) {
            $this->time[] = $n;

            return true;
        }

        return false;
    }

    protected function timeAddFinal($n)
    {
        if (!$this->timeAdd($n)) {
            return false;
        }
        while (count($this->time) < 4) {
            $this->time[] = 0;
        }

        return true;
    }

    protected function timeExpecting($n)
    {
        $i = count($this->time);

        return ($i === 1 && self::between($n, 0, 59)) || ($i === 2 && self::between($n, 0, 59)) || ($i === 3 && self::between($n, 0, 999));
    }

    protected function tzSet($hours)
    {
        $this->tzSign = $hours < 0 ? -1 : 1;
        $this->tzHour = $hours * $this->tzSign;
        $this->tzMinute = 0;
    }

    protected function tzIsEmpty()
    {
        return $this->tzHour === null;
    }

    protected function tzIsUtc()
    {
        return $this->tzHour === 0 && $this->tzMinute === 0;
    }

    protected function tzExpecting($n)
    {
        return $this->tzHour !== null && $this->tzMinute === null && self::between($n, 0, 59);
    }

    protected static function readMilliseconds($t)
    {
        $number = $t['value'];
        $length = $t['length'];
        if ($length < 3) {
            $number *= $length === 1 ? 100 : 10;
        } elseif ($length > 3) {
            if ($length > self::MAX_SIGNIFICANT_DIGITS) {
                $length = self::MAX_SIGNIFICANT_DIGITS;
            }
            $factor = 1;
            do {
                $factor *= 10;
                $length--;
            } while ($length > 3);
            $number = (int) ($number / $factor);
        }

        return $number;
    }

    // ------------------------------------------------------------------ ES5 ISO form

    /** @return array the first token the legacy parser must handle (T_END when all consumed, T_INVALID on error) */
    protected function parseEs5()
    {
        $invalid = self::tok(self::T_INVALID);
        $p = $this->peek();

        if (self::isSign($p)) {
            $signToken = $this->next();
            $p = $this->peek();
            if (!self::isFixed($p, 6)) {
                return $signToken;
            }
            $sign = self::signOf($signToken);
            $year = $this->next();
            if ($sign < 0 && $year['value'] === 0) {
                return $signToken;
            }
            $this->dayAdd($sign * $year['value']);
        } elseif (self::isFixed($p, 4)) {
            $t = $this->next();
            $this->dayAdd($t['value']);
        } else {
            return $this->next();
        }

        if ($this->skipSymbol('-')) {
            $p = $this->peek();
            if (!self::isFixed($p, 2) || !self::between($p['value'], 1, 12)) {
                return $this->next();
            }
            $t = $this->next();
            $this->dayAdd($t['value']);
            if ($this->skipSymbol('-')) {
                $p = $this->peek();
                if (!self::isFixed($p, 2) || !self::between($p['value'], 1, 31)) {
                    return $this->next();
                }
                $t = $this->next();
                $this->dayAdd($t['value']);
            }
        }

        $p = $this->peek();
        if ($p['tag'] !== self::K_TSEP) {
            if ($p['tag'] !== self::T_END) {
                return $this->next();
            }
        } else {
            $this->next();
            $p = $this->peek();
            if (!self::isFixed($p, 2) || !self::between($p['value'], 0, 24)) {
                return $invalid;
            }
            $hourIs24 = $p['value'] === 24;
            $t = $this->next();
            $this->timeAdd($t['value']);
            if (!$this->skipSymbol(':')) {
                return $invalid;
            }
            $p = $this->peek();
            if (!self::isFixed($p, 2) || !self::between($p['value'], 0, 59) || ($hourIs24 && $p['value'] > 0)) {
                return $invalid;
            }
            $t = $this->next();
            $this->timeAdd($t['value']);
            if ($this->skipSymbol(':')) {
                $p = $this->peek();
                if (!self::isFixed($p, 2) || !self::between($p['value'], 0, 59) || ($hourIs24 && $p['value'] > 0)) {
                    return $invalid;
                }
                $t = $this->next();
                $this->timeAdd($t['value']);
                if ($this->skipSymbol('.')) {
                    $p = $this->peek();
                    if (!self::isNumber($p) || ($hourIs24 && $p['value'] > 0)) {
                        return $invalid;
                    }
                    $this->timeAdd(self::readMilliseconds($this->next()));
                }
            }

            $p = $this->peek();
            if (self::isZ($p)) {
                $this->next();
                $this->tzSet(0);
            } elseif (self::isSign($p)) {
                $this->tzSign = self::signOf($this->next());
                $p = $this->peek();
                if (self::isFixed($p, 4)) {
                    $t = $this->next();
                    $hour = (int) ($t['value'] / 100);
                    $min = $t['value'] % 100;
                    if (!self::between($hour, 0, 23) || !self::between($min, 0, 59)) {
                        return $invalid;
                    }
                    $this->tzHour = $hour;
                    $this->tzMinute = $min;
                } else {
                    if (!self::isFixed($p, 2) || !self::between($p['value'], 0, 23)) {
                        return $invalid;
                    }
                    $t = $this->next();
                    $this->tzHour = $t['value'];
                    if (!$this->skipSymbol(':')) {
                        return $invalid;
                    }
                    $p = $this->peek();
                    if (!self::isFixed($p, 2) || !self::between($p['value'], 0, 59)) {
                        return $invalid;
                    }
                    $t = $this->next();
                    $this->tzMinute = $t['value'];
                }
            }
            $p = $this->peek();
            if ($p['tag'] !== self::T_END) {
                return $invalid;
            }
        }

        // date-only forms are UTC, date-time forms without an offset are local time
        if ($this->tzIsEmpty() && !$this->time) {
            $this->tzSet(0);
        }
        $this->isoDate = true;

        return self::tok(self::T_END);
    }

    // ------------------------------------------------------------------ legacy form + composition

    protected function run()
    {
        $token = $this->parseEs5();
        if ($token['tag'] === self::T_INVALID) {
            return null;
        }
        $hasReadNumber = (bool) $this->day;

        for (; $token['tag'] !== self::T_END; $token = $this->next()) {
            if ($token['tag'] === self::T_NUMBER) {
                $hasReadNumber = true;
                $n = $token['value'];
                if ($this->skipSymbol(':')) {
                    if ($this->skipSymbol(':')) {
                        if ($this->time) {
                            return null;
                        }
                        $this->timeAdd($n);
                        $this->timeAdd(0);
                    } else {
                        if (!$this->timeAdd($n)) {
                            return null;
                        }
                        if (self::isSymbol($this->peek(), '.')) {
                            $this->next();
                        }
                    }
                } elseif ($this->skipSymbol('.') && $this->timeExpecting($n)) {
                    $this->timeAdd($n);
                    if (!self::isNumber($this->peek())) {
                        return null;
                    }
                    $ms = self::readMilliseconds($this->next());
                    $this->timeAddFinal($ms);
                } elseif ($this->tzExpecting($n)) {
                    $this->tzMinute = $n;
                } elseif ($this->timeExpecting($n)) {
                    $this->timeAddFinal($n);
                    $peek = $this->peek();
                    if ($peek['tag'] !== self::T_END && $peek['tag'] !== self::T_WHITESPACE && !self::isZ($peek) && !self::isSign($peek)) {
                        return null;
                    }
                } else {
                    if (!$this->dayAdd($n)) {
                        return null;
                    }
                    $this->skipSymbol('-');
                }
            } elseif (self::isKeyword($token)) {
                $type = $token['tag'];
                $value = $token['value'];
                if ($type === self::K_AMPM && $this->time) {
                    $this->hourOffset = $value;
                } elseif ($type === self::K_MONTH) {
                    $this->namedMonth = $value;
                    $this->skipSymbol('-');
                } elseif ($type === self::K_TZ && $hasReadNumber) {
                    $this->tzSet($value);
                } else {
                    if ($hasReadNumber) {
                        return null;
                    }
                    if (self::isNumber($this->peek())) {
                        return null;
                    }
                }
            } elseif (self::isSign($token) && ($this->tzIsUtc() || $this->time)) {
                $this->tzSign = self::signOf($token);
                $n = 0;
                $length = 0;
                if (self::isNumber($this->peek())) {
                    $nt = $this->next();
                    $length = $nt['length'];
                    $n = $nt['value'];
                }
                $hasReadNumber = true;

                if (self::isSymbol($this->peek(), ':')) {
                    $this->tzHour = $n;
                    $this->tzMinute = null;
                } elseif ($length === 2 || $length === 1) {
                    $this->tzHour = $n;
                    $this->tzMinute = 0;
                } elseif ($length === 4 || $length === 3) {
                    $this->tzHour = (int) ($n / 100);
                    $this->tzMinute = $n % 100;
                } else {
                    return null;
                }
            } elseif ((self::isSign($token) || self::isSymbol($token, ')')) && $hasReadNumber) {
                return null;
            }
            // anything else (whitespace, unknown characters) is ignored
        }

        return $this->compose();
    }

    protected function compose()
    {
        // day
        $comp = $this->day;
        if (count($comp) < 1) {
            return null;
        }
        while (count($comp) < 3) {
            $comp[] = 1;
        }
        $year = 0;
        if ($this->namedMonth === null) {
            if ($this->isoDate || !self::between($comp[0], 1, 31)) {
                $year = $comp[0];
                $month = $comp[1];
                $day = $comp[2];
            } else {
                $month = $comp[0];
                $day = $comp[1];
                $year = $comp[2];
            }
        } else {
            $month = $this->namedMonth;
            if (!self::between($comp[0], 1, 31)) {
                $year = $comp[0];
                $day = $comp[1];
            } else {
                $day = $comp[0];
                $year = $comp[1];
            }
        }
        if (!$this->isoDate) {
            if (self::between($year, 0, 49)) {
                $year += 2000;
            } elseif (self::between($year, 50, 99)) {
                $year += 1900;
            }
        }
        if (abs($year) > 1073741823 || !self::between($month, 1, 12) || !self::between($day, 1, 31)) {
            return null;
        }

        // time
        $t = $this->time;
        while (count($t) < 4) {
            $t[] = 0;
        }
        list($hour, $minute, $second, $ms) = $t;
        if ($this->hourOffset !== null) {
            if (!self::between($hour, 0, 12)) {
                return null;
            }
            $hour %= 12;
            $hour += $this->hourOffset;
        }
        if (!self::between($hour, 0, 23) || !self::between($minute, 0, 59) || !self::between($second, 0, 59) || !self::between($ms, 0, 999)) {
            if ($hour !== 24 || $minute !== 0 || $second !== 0 || $ms !== 0) {
                return null;
            }
        }

        // zone
        $offset = null;
        if ($this->tzSign !== null) {
            $h = $this->tzHour === null ? 0 : $this->tzHour;
            $m = $this->tzMinute === null ? 0 : $this->tzMinute;
            $total = $h * 3600 + $m * 60;
            if ($total > 1073741823) {
                return null;
            }
            $offset = $this->tzSign < 0 ? -$total : $total;
        }

        return array($year, $month - 1, $day, $hour, $minute, $second, $ms, $offset);
    }
}
