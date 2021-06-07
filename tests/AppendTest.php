<?php

use PHPUnit\Framework\TestCase;
use CardinalCollections\ArrayWithSecondaryKeys\ArrayWithSecondaryKeys;
use CardinalCollections\ArrayWithSecondaryKeys\NoSuchIndexException;

class AppendTest extends TestCase
{
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
        $a->append(
            [
                'name' => 'Mika',
                'email' => 'mika@frg.ex'
            ]
        );
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
}
