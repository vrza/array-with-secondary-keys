<?php

use PHPUnit\Framework\TestCase;
use VladimirVrzic\ArrayWithSecondaryKeys\IterableUtils;

final class IterableUtilsTest extends TestCase
{
    public function testLastKey(): void
    {
        $a = [];
        $a[] = 'foo';
        $a[] = 'bar';
        $a[] = 'baz';
        $last = IterableUtils::lastKey($a);
        $this->assertEquals(2, $last);
    }

    public function testKeys(): void
    {
        $a = ['foo', 'bar', 'baz'];
        $keys = IterableUtils::keys($a);
        $this->assertEquals([0, 1, 2], $keys);
    }

    public function testLastKeyThroughIterator(): void
    {
        $a = [];
        $a[] = 'foo';
        $a[] = 'bar';
        $a[] = 'baz';
        $last = IterableUtils::lastKeyThroughIterator($a);
        $this->assertEquals(2, $last);
    }

    public function testKeysThroughIterator(): void
    {
        $a = ['foo', 'bar', 'baz'];
        $keys = IterableUtils::keysThroughIterator($a);
        $this->assertEquals([0, 1, 2], $keys);
    }

    public function testIterableValuesReduce(): void
    {
        $a = [1, 2, 3];
        echo "in test\n";
        $sum = IterableUtils::iterable_values_reduce($a, function($acc, $x) {
           return $acc + $x;
        }, 0);
        $this->assertEquals(6, $sum);
    }

    public function testIterableReduce(): void
    {
        $a = [1, 2, 3];
        $sum = IterableUtils::iterable_reduce($a, function($acc, $k, $v) {
            return $acc + $k + $v;
        }, 0);
        $this->assertEquals(9, $sum);
    }
}
