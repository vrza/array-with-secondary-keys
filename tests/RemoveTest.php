<?php

use PHPUnit\Framework\TestCase;
use CardinalCollections\ArrayWithSecondaryKeys\ArrayWithSecondaryKeys;
use CardinalCollections\ArrayWithSecondaryKeys\NoSuchIndexException;

final class RemoveTest extends TestCase
{
    public function testRemove(): void
    {
        $a = new ArrayWithSecondaryKeys();
        $a->put(
            22,
            [
                'name' => 'twenty-two',
                'state' => [
                    'pid' => 12345,
                    'db' => 'UPDATED'
                ]
            ]
        );
        $a->createIndex('state.pid');
        // non-existing key removal is a no-op
        $a->remove('23.state.pid');
        $a->remove('22.state.pid');
        $this->assertFalse(
            $a->has('22.state.pid')
        );
        $this->assertEquals(
            'UPDATED',
            $a->get('22.state.db')
        );
    }

    public function testRemoveByIndex(): void
    {
        $pid = 12345;
        $a = new ArrayWithSecondaryKeys();
        $a->put(
            22,
            [
                'name' => 'twenty-two',
                'state' => [
                    'pid' => $pid,
                    'db' => 'UPDATED'
                ]
            ]
        );
        $a->createIndex('state.pid');
        // non-existing key removal is a no-op
        $a->removeByIndex('state.pid', $pid + 1);
        $a->removeByIndex('state.pid', $pid);
        $this->assertTrue($a->isEmpty());
    }

    public function testRemoveIndexedValue(): void
    {
        $pid = 12345;
        $a = new ArrayWithSecondaryKeys();
        $a->put(
            22,
            [
                'name' => 'twenty-two',
                'state' => [
                    'pid' => $pid
                ]
            ]
        );
        $index = 'state.pid';
        $a->createIndex($index);
        $this->assertEquals(
            $pid,
            $a->get('22.state.pid')
        );
        try {
            $this->assertEquals(
                'twenty-two',
                $a->getByIndex($index, $pid)['name']
            );
        } catch (NoSuchIndexException $e) {
            $this->fail("No such index: $index");
        }
        $a->put('22.state.pid', null);
        $this->assertEquals(
            null,
            $a->get('22.state.pid')
        );
        try {
            $this->assertEquals(
                null,
                $a->getByIndex($index, $pid)
            );
        } catch (NoSuchIndexException $e) {
            $this->fail("No such index: $index");
        }
    }

    public function testRemoveNumericKey(): void
    {
        $a = new ArrayWithSecondaryKeys(
            ['apple', 'pear', 'quince']
        );
        $a->remove(1);
        $this->assertCount(2, $a);
        $this->assertEquals(
            'quince',
            $a->asArray()[2]
        );
    }

    public function testIterationAfterRemoval(): void
    {
        $a = new ArrayWithSecondaryKeys([ 22 => [22], 42 => [42], 33 => [33] ]);
        $a->remove(42);
        foreach ($a as $key => $_value) {
            $this->assertNotEquals($key, 42);
        }
    }

    public function testIterationAfterRemovalByIndex(): void
    {
        $a = new ArrayWithSecondaryKeys([
             22 => [
                 'name' => 'twenty-two',
                 'state' => [
                     'pid' => 12345,
                     'db' => 'UPDATED'
                 ]
            ],
            42 => [
                 'name' => 'forty-two',
                 'state' => [
                     'pid' => 12346,
                     'db' => 'REMOVED'
                 ]
            ],
            33 => [
                'name' => 'thirty-three',
                'state' => [
                    'pid' => 12347,
                    'db' => 'UNCHANGED'
                ]
            ]
        ]);
        $index = 'state.pid';
        $a->createIndex($index);
        $a->removeByIndex($index, 12346);
        foreach ($a as $key => $_value) {
            $this->assertNotEquals($key, 42);
        }
    }
}
