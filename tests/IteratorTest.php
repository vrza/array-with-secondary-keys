<?php

use PHPUnit\Framework\TestCase;
use CardinalCollections\ArrayWithSecondaryKeys\ArrayWithSecondaryKeys;

class IteratorTest extends TestCase
{
    public function testIterator(): void
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
        $this->assertEquals($keys, 'peramikalazo');
        $this->assertEquals($names, 'PeraMikaLazo');
    }
}
