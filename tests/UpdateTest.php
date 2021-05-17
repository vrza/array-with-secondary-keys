<?php

use PHPUnit\Framework\TestCase;
use VladimirVrzic\ArrayWithSecondaryKeys\ArrayWithSecondaryKeys;
use VladimirVrzic\ArrayWithSecondaryKeys\NoSuchIndexException;

final class UpdateTest extends TestCase
{
    public function testUpdateSecondaryKey(): void
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
        $a->createIndex('state.pid');
        $secondaryKeys = $a->secondaryKeys('state.pid');
        $this->assertEquals(
            1,
            count($secondaryKeys)
        );
        $this->assertEquals(
            12345,
            $secondaryKeys[0]
        );
        $a->updateSecondaryKey('state.pid', 12345, 13579);
        $this->assertEquals(
            13579,
            $a->get('22.state.pid')
        );
        $a->updateSecondaryKey('state.pid', 13579, null);
        $this->assertNull(
            $a->get('22.state.pid')
        );
    }

    public function testUpdateByIndex(): void
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
        $a->createIndex('state.pid');
        $a->updateByIndex(
            'state.pid',
            12345,
            [
                'name' => 'twenty-three',
                'state' => [
                    'pid' => 12345
                ]
            ]

        );
        // update by non existing secondary key is a no-op
        $a->updateByIndex(
            'state.pid',
            1,
            [
                'state' => [
                    'pid' => 42
                ]
            ]
        );
        $this->assertFalse($a->containsSecondaryKey('state.pid', 42));
        $this->assertEquals(
            'twenty-three',
            $a->get('22.name')
        );
    }
}
