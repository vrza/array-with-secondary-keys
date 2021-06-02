<?php

namespace VladimirVrzic\ArrayWithSecondaryKeys;

class IterableUtils
{
    /**
     *  Get the last key of an iterable.
     *
     *  For an array, this can be done with
     *  built in array functions.
     *  PHP < 7.3:
     *    array_keys($array)[count($array) - 1];
     *  PHP >= 7.3:
     *    array_key_last($array);
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

    /**
     *  Return an array with all keys of an iterable.
     *
     * @param iterable $c
     * @return int|string|null
     */
    public static function keys(iterable $c): array
    {
        for (
            reset($c), $keys = [];
            key($c) !== null;
            next($c)
        ) {
            $keys[] = key($c);
        }
        return $keys;
    }
}
