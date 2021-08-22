<?php

use PHPUnit\Framework\TestCase;
use CardinalCollections\ArrayWithSecondaryKeys\ArrayWithSecondaryKeys;
use CardinalCollections\ArrayWithSecondaryKeys\NoSuchIndexException;

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
        $index = 'state.pid';
        $a->createIndex($index);
        try {
            $secondaryKeys = $a->secondaryKeys($index);
            $this->assertCount(
                1,
                $secondaryKeys
            );
            $this->assertEquals(
                12345,
                $secondaryKeys[0]
            );
        } catch (NoSuchIndexException $e) {
            $this->fail("No such index: $index");
        }
        $a->updateSecondaryKey($index, null, 13579);
        $a->updateSecondaryKey($index, 12345, 13579);
        $this->assertEquals(
            13579,
            $a->get('22.state.pid')
        );
        $a->updateSecondaryKey($index, 13579, null);
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
        $index = 'state.pid';
        $a->createIndex($index);
        $a->updateByIndex(
            $index,
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
            $index,
            1,
            [
                'state' => [
                    'pid' => 42
                ]
            ]
        );
        try {
            $this->assertFalse($a->containsSecondaryKey($index, 42));
        } catch (NoSuchIndexException $e) {
            $this->fail("No such index: $index");
        }
        $this->assertEquals(
            'twenty-three',
            $a->get('22.name')
        );
    }

    public function testUpdateByIndexUpdatesAllSecondaryKeys(): void
    {
        $a = new ArrayWithSecondaryKeys();
        $a->put(
            22,
            [
                'name' => 'twenty-two',
                'state' => [
                    'pid' => 12345
                ],
                'fk' => 2022
            ]
        );
        $index = 'state.pid';
        $a->createIndex($index);
        $index2 = 'fk';
        $a->createIndex($index2);
        $a->updateByIndex(
            $index,
            12345,
            [
                'name' => 'twenty-three',
                'state' => [
                    'pid' => 12345
                ],
                'fk' => 2023
            ]
        );
        $this->assertEquals('twenty-three', $a->getByIndex('fk', 2023)['name']);
    }
}
