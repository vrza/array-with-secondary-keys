<?php

use PHPUnit\Framework\TestCase;
use VladimirVrzic\ArrayWithSecondaryKeys\ArrayWithSecondaryKeys;

class NumericIndexTest extends TestCase
{
    public function testArrayWithNumericIndexes(): void
    {
        $initialArray = [
            [
                'name' => 'Pera',
                'email' => 'pera@ddr.ex'
            ],
            [
                'name' => 'Mika',
                'email' => 'mika@frg.ex'
            ]
        ];
        $a = new ArrayWithSecondaryKeys($initialArray);
        $a->createIndex('email');
        try {
            $this->assertEquals(
                'mika@frg.ex',
                $a->get('1.email')
            );
            $this->assertEquals(
                'Mika',
                $a->getByIndex('email', 'mika@frg.ex')['name']
            );
        } catch (NoSuchIndexException $e) {
            $this->fail();
        }
    }
}
