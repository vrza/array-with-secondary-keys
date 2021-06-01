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
}
