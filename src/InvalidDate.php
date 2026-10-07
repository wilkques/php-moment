<?php

namespace Wilkques\Moment;

use DateTime;

/**
 * What toDate() gives for an invalid moment, like JavaScript's `Invalid Date`: still a DateTime
 * (so type checks pass), but isValid() is false, getTimestamp() is false and it formats as "Invalid Date".
 */
class InvalidDate extends DateTime
{
    public function __construct()
    {
        parent::__construct('@0');
    }

    /** @return bool always false */
    public function isValid()
    {
        return false;
    }

    /** @return bool false, like getTime() being NaN */
    #[\ReturnTypeWillChange]
    public function getTimestamp()
    {
        return false;
    }

    /** @return string "Invalid Date" whatever the format */
    #[\ReturnTypeWillChange]
    public function format($format)
    {
        return 'Invalid Date';
    }

    public function __toString()
    {
        return 'Invalid Date';
    }
}
