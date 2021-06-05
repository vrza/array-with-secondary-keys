<?php

use PHPUnit\Framework\TestCase;
use CardinalCollections\ArrayWithSecondaryKeys\ArrayWithSecondaryKeys;

class IteratorTest extends TestCase
{
    public function testIteratorStringKeys(): void
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
        $keys = '';
        $names = '';
        foreach ($a as $key => $value) {
            $keys .= $key;
            $names .= $value['name'];
        }
        $this->assertEquals('peramikalazo', $keys);
        $this->assertEquals('PeraMikaLazo', $names);
    }

    public function testIteratorNumericKeys(): void
    {
        $initialArray = [
            22 => [
                'name' => 'Pera',
                'email' => 'pera@ddr.ex'
            ],
            32 => [
                'name' => 'Mika',
                'email' => 'mika@frg.ex'
            ],
            42 => [
                'name' => 'Lazo',
                'email' => 'lazo@sfrj.ex'
            ]
        ];
        $a = new ArrayWithSecondaryKeys($initialArray);
        $keys = 0;
        $names = '';
        foreach ($a as $key => $value) {
            $keys += $key;
            $names .= $value['name'];
        }
        $this->assertEquals(96, $keys);
        $this->assertEquals('PeraMikaLazo', $names);
    }

    public function testIteratorStringKeysPutDot(): void
    {
        $initialArray = [
            'pera' => [
                'name' => 'Pera'
            ]
        ];
        $a = new ArrayWithSecondaryKeys($initialArray);
        $a->put('mika.name', 'Mika');
        $a->put('lazo.name', 'Lazo');
        $keys = '';
        $names = '';
        foreach ($a as $key => $value) {
            $keys .= $key;
            $names .= $value['name'];
        }
        $this->assertEquals('peramikalazo', $keys);
        $this->assertEquals('PeraMikaLazo', $names);
    }
}
