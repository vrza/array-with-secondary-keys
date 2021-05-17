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
        $primaryKeys = $a->primaryKeys();
        $this->assertEquals(2, count($primaryKeys));
        $this->assertEquals('pera', $primaryKeys[0]);
        $this->assertEquals(0, $primaryKeys[1]);
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
