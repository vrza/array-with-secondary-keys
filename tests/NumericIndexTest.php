<?php

use PHPUnit\Framework\TestCase;
use VladimirVrzic\ArrayWithSecondaryKeys\ArrayWithSecondaryKeys;
use VladimirVrzic\ArrayWithSecondaryKeys\NoSuchIndexException;

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
        $index = 'email';
        $a = new ArrayWithSecondaryKeys($initialArray);
        $a->createIndex($index);
        $this->assertEquals(
            'mika@frg.ex',
            $a->get('1.email')
        );
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
