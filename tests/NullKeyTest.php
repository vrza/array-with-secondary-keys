<?php

use PHPUnit\Framework\TestCase;
use CardinalCollections\ArrayWithSecondaryKeys\ArrayWithSecondaryKeys;

final class NullKeyTest extends TestCase
{
    public function testNullKey(): void
    {
        $a = new ArrayWithSecondaryKeys();
        $a->put(
            22,
            [
                'name' => 'twenty-two',
                'state' => [
                    'pid' => 12345
                ]
            ]
        );
        $this->assertFalse(
            $a->has(null)
        );
        $this->assertEquals(
            $a->get(null),
            $a->asArray()
        );
    }
}
