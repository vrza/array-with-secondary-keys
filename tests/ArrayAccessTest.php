<?php

use PHPUnit\Framework\TestCase;
use CardinalCollections\ArrayWithSecondaryKeys\ArrayWithSecondaryKeys;
use CardinalCollections\ArrayWithSecondaryKeys\NoSuchIndexException;

class ArrayAccessTest extends TestCase
{
    public function testBasicArrayAccess(): void
    {
        $index = 'email';
        $initialArray = [
            'pera' => [
                'name' => 'Pera',
                'email' => 'pera@ddr.ex'
            ]
        ];
        $a = new ArrayWithSecondaryKeys($initialArray);
        $a->createIndex($index);
        $this->assertTrue(isset($a['pera']));
        $this->assertFalse(isset($a['mika']));
        $this->assertCount(1, $a);
        $a['mika'] = [
            'name' => 'Mika',
            'email' => 'mika@frg.ex'
        ];
        $this->assertCount(2, $a);
        $this->assertTrue(isset($a['mika']));
        $a['lazo'] = [
            'name' => 'Lazo',
            'email' => 'lazo@sfrj.ex'
        ];
        $this->assertCount(3, $a);
        unset($a['mika']);
        $this->assertFalse(isset($a['mika']));
        $this->assertCount(2, $a);
        $l = $a['lazo'];
        $this->assertEquals('Lazo', $l['name']);
        try {
            $this->assertEquals(
                'Lazo',
                $a->getByIndex($index, 'lazo@sfrj.ex')['name']
            );
        } catch (NoSuchIndexException $e) {
            $this->fail("No such index: $index");
        }
    }

    public function testOffsetSetInvalidArgumentException(): void
    {
        $a = new ArrayWithSecondaryKeys();
        $this->expectException(InvalidArgumentException::class);
        $a[['k' => 'v']] = 'document';
    }

    public function testOffsetGetInvalidArgumentException(): void
    {
        $a = new ArrayWithSecondaryKeys(['foo']);
        $this->expectException(InvalidArgumentException::class);
        $a[['k' => 'v']];
    }

    public function testOffsetUnsetInvalidArgumentException(): void
    {
        $a = new ArrayWithSecondaryKeys(['foo']);
        $this->expectException(InvalidArgumentException::class);
        unset($a[['k' => 'v']]);
    }

    public function testAppend(): void
    {
        $initialArray = [
            'pera' => [
                'name' => 'Pera',
                'email' => 'pera@ddr.ex'
            ]
        ];
        $index = 'email';
        $a = new ArrayWithSecondaryKeys($initialArray);
        $a->createIndex($index);
        $a[] = [
                'name' => 'Mika',
                'email' => 'mika@frg.ex'
            ];
        $primaryKeys = $a->primaryKeys();
        $this->assertCount(2, $primaryKeys);
        $this->assertEquals('pera', $primaryKeys[0]);
        $this->assertEquals(0, $primaryKeys[1]);
        try {
            $this->assertEquals(
                'Mika',
                $a->getByIndex($index, 'mika@frg.ex')['name']
            );
        } catch (NoSuchIndexException $e) {
            $this->fail("No such index: $index");
        }
    }

    public function testNullOffset(): void {
        $a = new ArrayWithSecondaryKeys(['one', 'two']);
        unset($a[null]);
        $this->assertCount(2, $a);
        $this->assertNull($a[null]);
    }
}
