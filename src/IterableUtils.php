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
    public static function lastKeyThroughIterator(iterable $c)
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
    public static function keysThroughIterator(iterable $c): array
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

    public static function lastKey(iterable $iterable)
    {
        return (is_array($iterable) && function_exists('array_key_last'))
            ? array_key_last($iterable)
            : self::iterable_keys_reduce($iterable, function ($acc, $key) {
                 return $key;
              });
    }

    public static function keys(iterable $iterable)
    {
        return self::iterable_keys_reduce($iterable, function ($acc, $key) {
            $acc[] = $key;
            return $acc;
        }, []);
    }

    public static function iterable_keys_reduce(iterable $iterable, $callback, $initial = null)
    {
        reset($iterable);
        $acc = $initial ?? key($iterable);
        while (($key = key($iterable)) !== null) {
            $acc = $callback($acc, $key);
            next($iterable);
        }
        return $acc;
    }

    public static function iterable_values_reduce(iterable $iterable, $callback, $initial = null)
    {
        reset($iterable);
        $acc = $initial ?? current($iterable);
        while ((key($iterable)) !== null) {
            $value = current($iterable);
            $acc = $callback($acc, $value);
            next($iterable);
        }
        return $acc;
    }

    public static function iterable_reduce(iterable $iterable, $callback, $initial = null)
    {
        reset($iterable);
        $acc = $initial ?? [key($iterable) => current($iterable)];
        while (($key = key($iterable)) !== null) {
            $value = current($iterable);
            $acc = $callback($acc, $key, $value);
            next($iterable);
        }
        return $acc;
    }
}
