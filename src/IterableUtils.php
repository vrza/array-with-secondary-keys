<?php

namespace VladimirVrzic\ArrayWithSecondaryKeys;

use Iterator;

class IterableUtils
{
    /**
     *  Get the last key of an iterable.
     *
     *  For an array, this can be done with
     *  built in array functions.
     *  PHP < 7.3:
     *    array_keys($this->p)[count($this->p) - 1];
     *  PHP >= 7.3:
     *    array_key_last($this->p);
     *
     * @param iterable $c
     * @return int|string|null
     */
    public static function lastKey(iterable $c)
    {
        for (
            reset($c), $key = key($c);
            key($c) !== null;
            next($c)
        ) {
            $key = key($c);
        }
        return $key;
    }
}
