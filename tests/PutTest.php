<?php

use PHPUnit\Framework\TestCase;
use CardinalCollections\ArrayWithSecondaryKeys\ArrayWithSecondaryKeys;

class PutTest extends TestCase
{
    public function testPutIfAbsent(): void
    {
        $initialArray = [
            'pera' => [
                'name' => 'Pera',
                'email' => 'pera@ddr.ex'
            ]
        ];
        $a = new ArrayWithSecondaryKeys($initialArray);
        $a->createIndex('email');
        $new = $a->putIfAbsent(
            'mika',
            [
                'name' => 'Mika',
                'email' => 'mika@frg.ex'
            ]
        );
        $this->assertNull($new);
        $existing = $a->putIfAbsent(
            'pera',
            [
                'name' => 'Petar',
                'email' => 'petar@ddr.ex'
            ]
        );
        $this->assertEquals(
            'Pera',
            $existing['name']
        );
    }

}
