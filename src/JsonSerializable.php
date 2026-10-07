<?php

// JsonSerializable arrived in PHP 5.4; this declares it for PHP 5.3 so Moment and Duration can implement it everywhere.
namespace {
    if (!interface_exists('JsonSerializable', false)) {
        interface JsonSerializable
        {
            public function jsonSerialize();
        }
    }
}
