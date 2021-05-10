<?php

use PHPUnit\Framework\TestCase;
use VladimirVrzic\ArrayWithSecondaryKeys\ArrayWithSecondaryKeys;

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
        $a = new ArrayWithSecondaryKeys($initialArray);
        $a->createIndex('email');
        $a->append(
            [
                'name' => 'Mika',
                'email' => 'mika@frg.ex'
            ]
        );
        try {
            $this->assertEquals(
                'Mika',
                $a->getByIndex('email', 'mika@frg.ex')['name']
            );
        } catch (NoSuchIndexException $e) {
            $this->fail();
        }
    }
}
