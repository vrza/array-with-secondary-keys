<?php

use PHPUnit\Framework\TestCase;
use CardinalCollections\ArrayWithSecondaryKeys\ArrayWithSecondaryKeys;

class CountableTest extends TestCase
{
    public function testCountable(): void
    {
        $initialArray = [
            'pera' => [
                'name' => 'Pera',
                'email' => 'pera@ddr.ex'
            ],
            'mika' => [
                'name' => 'Mika',
                'email' => 'mika@frg.ex'
            ],
            'lazo' => [
                'name' => 'Lazo',
                'email' => 'lazo@sfrj.ex'
            ]
        ];
        $a = new ArrayWithSecondaryKeys($initialArray);
        $this->assertEquals(count($a), 3);
    }
}
