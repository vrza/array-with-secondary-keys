<?php

use PHPUnit\Framework\TestCase;
use CardinalCollections\ArrayWithSecondaryKeys\ArrayWithSecondaryKeys;
use CardinalCollections\Mutable\Set;

/**
 *  Tests to help improve iterator sanity.
 *  Good writeup on PHP foreach:
 *  https://stackoverflow.com/questions/10057671/how-does-php-foreach-actually-work
 */
class ModificationDuringIterationTest extends TestCase
{
    public function testNestedLoops(): void
    {
        $a = new ArrayWithSecondaryKeys([ 1 => 1, 2 => 1, 3 => 1, 4 => 1, 5 => 1 ]);
        $verificationSet = new Set([]);
        foreach ($a as $k1 => $v1) {
            foreach ($a as $k2 => $v2) {
                if ($k1 == 1 && $k2 == 1) {
                    $a->remove(1);
                }
                $verificationSet->add([$k1, $k2]);
            }
        }
        /*
         * PHP 5 output: (1, 1) (1, 3) (1, 4) (1, 5)
         * PHP 7 output: (1, 1) (1, 3) (1, 4) (1, 5)
         *               (3, 1) (3, 3) (3, 4) (3, 5)
         *               (4, 1) (4, 3) (4, 4) (4, 5)
         *               (5, 1) (5, 3) (5, 4) (5, 5)
         * our output:   (1, 1) (1, 2) (1, 3) (1, 4) (1, 5)
         */
        $this->assertCount(4, $a);
        $this->assertCount(5, $verificationSet);
        $this->assertTrue($verificationSet->has([1, 1]));
        $this->assertTrue($verificationSet->has([1, 2]));
        $this->assertTrue($verificationSet->has([1, 3]));
        $this->assertTrue($verificationSet->has([1, 4]));
        $this->assertTrue($verificationSet->has([1, 5]));
    }

    public function testRemoveAddSameKey(): void
    {
        $map = new ArrayWithSecondaryKeys(['EzEz' => 1, 'EzFY' => 2, 'FYEz' => 3]);
        foreach ($map as $value) {
            unset($map['EzFY']);
            $map['FYFY'] = 4;
        }
        /*
         * PHP 5 output: 1, 4
         * PHP 7 output: 1, 3, 4 <- we have this behavior
         *
         * Previously the HashPointer restore mechanism jumped
         * right to the new element because it "looked" like
         * it's the same as the removed element (due to colliding
         * hash and pointer). As we no longer rely on the element
         * hash for anything, this is no longer an issue.
         */
        $this->assertCount(3, $map);
        $verificationSet = new Set();
        foreach ($map as $value) {
            $verificationSet->add($value);
        }
        $this->assertTrue($verificationSet->equals(new Set([1, 3, 4])));
    }

    public function testRemove(): void
    {
        $map = new ArrayWithSecondaryKeys([
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
        ]);
        $reachedLastElement = false;
        foreach ($map as $k => $v) {
            if ($k == 'mika') {
                $map->remove('pera');
            }
            if ($k == 'lazo') {
                $reachedLastElement = true;
                $map->remove($k);
            }
        }
        $this->assertTrue($reachedLastElement);
        $this->assertCount(1, $map);
    }

    public function testRemovePrevious(): void
    {
        $a = new ArrayWithSecondaryKeys([ 1 => 1, 2 => 1, 3 => 1, 4 => 1, 5 => 1 ]);
        $previous = false;
        foreach ($a as $key => $value) {
            if ($previous) $a->remove($previous);
            $previous = $key;
        }
        $this->assertCount(1, $a);
        $this->assertTrue($a->has(5));
    }

    /**
     *  Test that would break iteration in SplObjectStorage
     *  https://www.php.net/manual/en/splobjectstorage.detach.php
     */
    public function testRemovingCurrent(): void
    {
        $a = new ArrayWithSecondaryKeys([ 1 => 1, 2 => 1, 3 => 1, 4 => 1, 5 => 1 ]);
        $a->rewind();
        $iterated = 0;
        $expected = $a->count();
        while ($a->valid()) {
            $iterated++;
            $a->remove($a->key());
            $a->next();
        }
        $this->assertEquals($expected, $iterated);
    }
}
