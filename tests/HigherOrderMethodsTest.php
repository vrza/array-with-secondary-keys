<?php

use PHPUnit\Framework\TestCase;
use CardinalCollections\ArrayWithSecondaryKeys\ArrayWithSecondaryKeys;

class HigherOrderMethodsTest extends TestCase
{
    public function testReduce(): void
    {
        $map = new ArrayWithSecondaryKeys([]);
        $this->assertTrue($map->isEmpty());
        $result = $map->reduce(function ($acc, $value) {
            return $acc + $value;
        });
        $this->assertNull($result);

        $map2 = new ArrayWithSecondaryKeys([5 => 3, 7 => 1, 9 => -2]);
        $this->assertFalse($map2->isEmpty());
        $result = $map2->reduce(function ($acc, $k, $v) {
            return [$acc[0] + $k, $acc[1] + $v];
        }, [0,0]);
        $this->assertEquals(21, $result[0]);
        $this->assertEquals(2, $result[1]);

        $map3 = new ArrayWithSecondaryKeys([5 => 3, 7 => 1, 9 => -2]);
        $sumOfKeys = $map3->reduce(function ($acc, $k) {
            return $acc + $k;
        }, 0);
        $this->assertEquals(21, $sumOfKeys);
        $sumOfValues = $map3->reduce(function ($acc, $_k, $v) {
            return $acc + $v;
        });
        $this->assertEquals(2, $sumOfValues);
    }

    public function testReduceTuples(): void
    {
        $map = new ArrayWithSecondaryKeys([]);
        $this->assertTrue($map->isEmpty());
        $result = $map->reduceTuples(function ($acc, $value) {
            return $acc + $value;
        });
        $this->assertNull($result);
        $map2 = new ArrayWithSecondaryKeys([5 => 3, 7 => 1]);
        $this->assertFalse($map2->isEmpty());
        $result = $map2->reduceTuples(function ($acc, $k, $v) {
            return [$acc[0] + $k, $acc[1] + $v];
        });
        $this->assertEquals(12, $result[0]);
        $this->assertEquals(4, $result[1]);
        $result = $map2->reduceTuples(function ($acc, $k) {
            return [$acc[0] + $k, $acc[1]];
        });
        $this->assertEquals(12, $result[0]);
    }

    public function testMap(): void
    {
        $map = new ArrayWithSecondaryKeys([]);
        $this->assertTrue($map->isEmpty());
        $result = $map->map(function ($key, $value) {
            return [$key * $key, $value * $value];
        });
        $this->assertTrue($result->isEmpty());
        $map->put(3, 4);
        $result = $map->map(function ($key, $value) {
            return [$key * $key, $value * $value];
        });
        $this->assertInstanceOf(ArrayWithSecondaryKeys::class, $result);
        $this->assertCount(1, $result);
        $this->assertEquals(16, $result->get(9));
    }

    public function testEvery(): void
    {
        $map = new ArrayWithSecondaryKeys(['one' => 'one', 'two' => 'two', 'three' => 'three']);
        $result1 = $map->every(function ($k, $v) {
            return $k === $v;
        });
        $this->assertTrue($result1);

        $map['three'] = 'four';
        $result2 = $map->every(function ($k, $v) {
            return $k === $v;
        });
        $this->assertFalse($result2);
    }

    public function testSome(): void
    {
        $map = new ArrayWithSecondaryKeys(['one' => 'two', 'three' => 'four']);
        $result1 = $map->some(function ($k, $v) {
            return $k === $v;
        });
        $this->assertFalse($result1);

        $map['five'] = 'five';
        $result2 = $map->some(function ($k, $v) {
            return $k === $v;
        });
        $this->assertTrue($result2);
    }

    public function testForeach(): void
    {
        $map = new ArrayWithSecondaryKeys([
            1 => 2,
            3 => 4,
            5 => 6
        ]);
        $sum = 0;
        $map->foreach(function($k, $v) use (&$sum) {
            $sum += $k + $v;
        });
        $this->assertEquals(21, $sum);
    }
}
