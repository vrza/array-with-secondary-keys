<?php

use PHPUnit\Framework\TestCase;
use VladimirVrzic\ArrayWithSecondaryKeys\ArrayWithSecondaryKeys;
use VladimirVrzic\ArrayWithSecondaryKeys\NoSuchIndexException;

final class UpdateSecondaryKeyTest extends TestCase
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
}
