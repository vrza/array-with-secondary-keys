<?php

use PHPUnit\Framework\TestCase;
use VladimirVrzic\ArrayWithSecondaryKeys\ArrayWithSecondaryKeys;
use VladimirVrzic\ArrayWithSecondaryKeys\NoSuchIndexException;

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
        $a->createIndex('state.pid');
        $this->assertEquals(
            $pid,
            $a->get('22.state.pid')
        );
        try {
            $this->assertEquals(
                'twenty-two',
                $a->getByIndex('state.pid', $pid)['name']
            );
        } catch (NoSuchIndexException $e) {
            $this->fail("No such index: state.pid");
        }
        $a->put('22.state.pid', null);
        $this->assertEquals(
            null,
            $a->get('22.state.pid')
        );
        try {
            $this->assertEquals(
                null,
                $a->getByIndex('state.pid', $pid)
            );
        } catch (NoSuchIndexException $e) {
            $this->fail("No such index: state.pid");
        }
    }
}
