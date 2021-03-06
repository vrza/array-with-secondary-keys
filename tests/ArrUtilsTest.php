<?php

use PHPUnit\Framework\TestCase;
use CardinalCollections\ArrayWithSecondaryKeys\ArrUtils;

final class ArrUtilsTest extends TestCase
{
    public function testSetNullKey(): void
    {
        $a = [
            22 => 42
        ];
        ArrUtils::set($a, null, 'foo');
        $this->assertEquals('foo', $a);
    }

    public function testHasNullKey(): void
    {
        $a = [
            22 => 42
        ];
        $result = ArrUtils::has($a, null);
        $this->assertFalse($result);
    }

    public function testHasKey(): void
    {
        $a = [
            22 => 42
        ];
        $result = ArrUtils::has($a, 22);
        $this->assertTrue($result);
    }

    public function testForget(): void
    {
        $a = [
            22 =>
                [
                    'name' => 'twenty-two',
                    'state' => [
                        'pid' => 12345
                    ]
                ]
        ];
        ArrUtils::forget($a, []);
        $this->assertEquals('twenty-two', $a[22]['name']);
        ArrUtils::forget($a, 22);
        $this->assertCount(0, $a);
    }

    public function testForgetDotNotation(): void
    {
        $a = [
            22 =>
                [
                    'name' => 'twenty-two',
                    'state' => [
                        'pid' => 12345
                    ]
                ]
        ];
        ArrUtils::set($a, '22.foo.bar', 'baz');
        ArrUtils::forget($a, "22.foo.bar.bogus");
        $this->assertEquals("baz", ArrUtils::get($a, "22.foo.bar"));
    }

}
