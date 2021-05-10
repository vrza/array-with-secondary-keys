<?php

use PHPUnit\Framework\TestCase;
use VladimirVrzic\ArrayWithSecondaryKeys\ArrayWithSecondaryKeys;
use VladimirVrzic\ArrayWithSecondaryKeys\NoSuchIndexException;

final class RemoveTest extends TestCase
{
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
